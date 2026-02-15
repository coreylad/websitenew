<?php

declare(strict_types=1);

/**
 * Modern Torrent API Handler - PHP 8.5+ Compatible
 * Compatible with standard torrent tracker API specifications
 * Provides JSON API endpoints for torrent management
 */

require_once __DIR__ . '/../../global.php';

class TorrentAPIHandler
{
    private PDO $db;
    private ?array $user = null;
    private string $apiKey = '';
    
    public function __construct()
    {
        global $TSDatabase;
        $this->db = $TSDatabase->getConnection();
        
        // Set JSON response header
        header('Content-Type: application/json; charset=utf-8');
        
        // Enable CORS if needed
        if (isset($_SERVER['HTTP_ORIGIN'])) {
            header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
            header('Access-Control-Allow-Credentials: true');
            header('Access-Control-Max-Age: 86400');
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'])) {
                header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
            }
            if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])) {
                header('Access-Control-Allow-Headers: ' . $_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']);
            }
            exit(0);
        }
    }
    
    /**
     * Handle API request and route to appropriate method
     */
    public function handleRequest(): void
    {
        try {
            // Authenticate user
            if (!$this->authenticate()) {
                $this->sendError('Unauthorized', 401);
                return;
            }
            
            $endpoint = $_GET['endpoint'] ?? 'torrents';
            $method = $_SERVER['REQUEST_METHOD'];
            
            switch ($endpoint) {
                case 'torrents':
                    $this->handleTorrentsEndpoint($method);
                    break;
                    
                case 'torrent':
                    $this->handleSingleTorrentEndpoint($method);
                    break;
                    
                case 'categories':
                    $this->handleCategoriesEndpoint();
                    break;
                    
                case 'stats':
                    $this->handleStatsEndpoint();
                    break;
                    
                case 'user':
                    $this->handleUserEndpoint();
                    break;
                    
                case 'rss':
                    $this->handleRSSEndpoint();
                    break;
                    
                default:
                    $this->sendError('Invalid endpoint', 404);
            }
            
        } catch (Exception $e) {
            $this->sendError($e->getMessage(), 500);
        }
    }
    
    /**
     * Authenticate API request via API key or session
     */
    private function authenticate(): bool
    {
        // Check for API key in header
        if (isset($_SERVER['HTTP_X_API_KEY'])) {
            $this->apiKey = $_SERVER['HTTP_X_API_KEY'];
            return $this->validateAPIKey($this->apiKey);
        }
        
        // Check for API key in query parameter
        if (isset($_GET['api_key'])) {
            $this->apiKey = $_GET['api_key'];
            return $this->validateAPIKey($this->apiKey);
        }
        
        // Check for session authentication
        if (isset($GLOBALS['CURUSER']) && !empty($GLOBALS['CURUSER'])) {
            $this->user = $GLOBALS['CURUSER'];
            return true;
        }
        
        return false;
    }
    
    /**
     * Validate API key and load user
     */
    private function validateAPIKey(string $apiKey): bool
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM users WHERE api_token = ? AND enabled = "yes" LIMIT 1'
        );
        $stmt->execute([$apiKey]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            $this->user = $user;
            return true;
        }
        
        return false;
    }
    
    /**
     * Handle torrents list/search endpoint
     */
    private function handleTorrentsEndpoint(string $method): void
    {
        if ($method !== 'GET') {
            $this->sendError('Method not allowed', 405);
            return;
        }
        
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = min(100, max(1, (int)($_GET['per_page'] ?? 50)));
        $offset = ($page - 1) * $perPage;
        
        // Build query conditions
        $conditions = ['visible = "yes"'];
        $params = [];
        
        // Search query
        if (!empty($_GET['search'])) {
            $conditions[] = '(name LIKE ? OR description LIKE ?)';
            $searchTerm = '%' . $_GET['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        // Category filter
        if (!empty($_GET['category_id'])) {
            $conditions[] = 'category = ?';
            $params[] = (int)$_GET['category_id'];
        }
        
        // Type filter (movie, tv, game, etc.)
        if (!empty($_GET['type'])) {
            $conditions[] = 'type = ?';
            $params[] = $_GET['type'];
        }
        
        // Free/Double upload filter
        if (isset($_GET['free'])) {
            $conditions[] = 'free = ?';
            $params[] = $_GET['free'] === 'true' ? 'yes' : 'no';
        }
        
        // Build WHERE clause
        $whereClause = implode(' AND ', $conditions);
        
        // Get total count
        $countStmt = $this->db->prepare(
            "SELECT COUNT(*) FROM torrents WHERE $whereClause"
        );
        $countStmt->execute($params);
        $totalCount = (int)$countStmt->fetchColumn();
        
        // Get torrents
        $params[] = $perPage;
        $params[] = $offset;
        
        $stmt = $this->db->prepare("
            SELECT 
                id,
                name,
                category,
                type,
                size,
                added,
                seeders,
                leechers,
                times_completed,
                info_hash,
                free,
                double_upload,
                description,
                owner
            FROM torrents 
            WHERE $whereClause
            ORDER BY added DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->execute($params);
        $torrents = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Format torrent data
        $formattedTorrents = array_map([$this, 'formatTorrentData'], $torrents);
        
        $this->sendSuccess([
            'data' => $formattedTorrents,
            'meta' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $totalCount,
                'last_page' => (int)ceil($totalCount / $perPage)
            ]
        ]);
    }
    
    /**
     * Handle single torrent endpoint
     */
    private function handleSingleTorrentEndpoint(string $method): void
    {
        if ($method !== 'GET') {
            $this->sendError('Method not allowed', 405);
            return;
        }
        
        $torrentId = (int)($_GET['id'] ?? 0);
        
        if ($torrentId <= 0) {
            $this->sendError('Invalid torrent ID', 400);
            return;
        }
        
        $stmt = $this->db->prepare('
            SELECT 
                t.*,
                u.username as uploader_username,
                c.name as category_name
            FROM torrents t
            LEFT JOIN users u ON t.owner = u.id
            LEFT JOIN categories c ON t.category = c.id
            WHERE t.id = ? AND t.visible = "yes"
            LIMIT 1
        ');
        $stmt->execute([$torrentId]);
        $torrent = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$torrent) {
            $this->sendError('Torrent not found', 404);
            return;
        }
        
        // Get file list
        $filesStmt = $this->db->prepare('
            SELECT filename, size 
            FROM files 
            WHERE torrent = ?
            ORDER BY filename
        ');
        $filesStmt->execute([$torrentId]);
        $files = $filesStmt->fetchAll(PDO::FETCH_ASSOC);
        
        $torrent['files'] = $files;
        
        $this->sendSuccess($this->formatTorrentData($torrent, true));
    }
    
    /**
     * Handle categories endpoint
     */
    private function handleCategoriesEndpoint(): void
    {
        $stmt = $this->db->query('
            SELECT id, name, image, sort_index
            FROM categories
            ORDER BY sort_index, name
        ');
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $this->sendSuccess($categories);
    }
    
    /**
     * Handle stats endpoint
     */
    private function handleStatsEndpoint(): void
    {
        // Get torrent stats
        $torrentStats = $this->db->query('
            SELECT 
                COUNT(*) as total_torrents,
                SUM(seeders) as total_seeders,
                SUM(leechers) as total_leechers,
                SUM(size) as total_size,
                SUM(times_completed) as total_downloads
            FROM torrents 
            WHERE visible = "yes"
        ')->fetch(PDO::FETCH_ASSOC);
        
        // Get user stats
        $userStats = $this->db->query('
            SELECT 
                COUNT(*) as total_users,
                COUNT(CASE WHEN enabled = "yes" THEN 1 END) as active_users
            FROM users
        ')->fetch(PDO::FETCH_ASSOC);
        
        $this->sendSuccess([
            'torrents' => [
                'total' => (int)$torrentStats['total_torrents'],
                'seeders' => (int)$torrentStats['total_seeders'],
                'leechers' => (int)$torrentStats['total_leechers'],
                'total_size' => (int)$torrentStats['total_size'],
                'total_downloads' => (int)$torrentStats['total_downloads']
            ],
            'users' => [
                'total' => (int)$userStats['total_users'],
                'active' => (int)$userStats['active_users']
            ]
        ]);
    }
    
    /**
     * Handle user endpoint (current user info)
     */
    private function handleUserEndpoint(): void
    {
        if (!$this->user) {
            $this->sendError('User not authenticated', 401);
            return;
        }
        
        // Get user stats
        $stmt = $this->db->prepare('
            SELECT 
                COUNT(*) as uploads,
                SUM(CASE WHEN visible = "yes" THEN 1 ELSE 0 END) as active_uploads
            FROM torrents 
            WHERE owner = ?
        ');
        $stmt->execute([$this->user['id']]);
        $uploadStats = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $userData = [
            'id' => (int)$this->user['id'],
            'username' => $this->user['username'],
            'email' => $this->user['email'],
            'uploaded' => (int)($this->user['uploaded'] ?? 0),
            'downloaded' => (int)($this->user['downloaded'] ?? 0),
            'ratio' => $this->calculateRatio(
                (int)($this->user['uploaded'] ?? 0),
                (int)($this->user['downloaded'] ?? 0)
            ),
            'seedbonus' => (float)($this->user['seedbonus'] ?? 0),
            'invites' => (int)($this->user['invites'] ?? 0),
            'uploads' => (int)$uploadStats['uploads'],
            'active_uploads' => (int)$uploadStats['active_uploads'],
            'class' => $this->user['class'] ?? 'User',
            'joined' => $this->user['added'] ?? null
        ];
        
        $this->sendSuccess($userData);
    }
    
    /**
     * Handle RSS feed endpoint
     */
    private function handleRSSEndpoint(): void
    {
        header('Content-Type: application/rss+xml; charset=utf-8');
        
        $limit = min(100, max(1, (int)($_GET['limit'] ?? 50)));
        
        $stmt = $this->db->prepare('
            SELECT * FROM torrents 
            WHERE visible = "yes"
            ORDER BY added DESC
            LIMIT ?
        ');
        $stmt->execute([$limit]);
        $torrents = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $rss = $this->generateRSS($torrents);
        echo $rss;
        exit;
    }
    
    /**
     * Format torrent data for API response
     */
    private function formatTorrentData(array $torrent, bool $detailed = false): array
    {
        $formatted = [
            'id' => (int)$torrent['id'],
            'name' => $torrent['name'],
            'category_id' => (int)$torrent['category'],
            'type' => $torrent['type'] ?? null,
            'size' => (int)$torrent['size'],
            'size_formatted' => mksize($torrent['size']),
            'info_hash' => $torrent['info_hash'],
            'seeders' => (int)$torrent['seeders'],
            'leechers' => (int)$torrent['leechers'],
            'times_completed' => (int)$torrent['times_completed'],
            'created_at' => $torrent['added'],
            'is_free' => ($torrent['free'] ?? 'no') === 'yes',
            'is_double_upload' => ($torrent['double_upload'] ?? 'no') === 'yes',
            'download_link' => $GLOBALS['BASEURL'] . '/download.php?id=' . $torrent['id']
        ];
        
        if ($detailed) {
            $formatted['description'] = $torrent['description'] ?? '';
            $formatted['uploader'] = [
                'id' => (int)$torrent['owner'],
                'username' => $torrent['uploader_username'] ?? null
            ];
            $formatted['category_name'] = $torrent['category_name'] ?? null;
            $formatted['files'] = $torrent['files'] ?? [];
        }
        
        return $formatted;
    }
    
    /**
     * Calculate ratio
     */
    private function calculateRatio(int $uploaded, int $downloaded): float
    {
        if ($downloaded == 0) {
            return $uploaded > 0 ? INF : 0.0;
        }
        return round($uploaded / $downloaded, 2);
    }
    
    /**
     * Generate RSS feed XML
     */
    private function generateRSS(array $torrents): string
    {
        global $SITENAME, $BASEURL;
        
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">' . "\n";
        $xml .= '<channel>' . "\n";
        $xml .= '  <title>' . htmlspecialchars($SITENAME, ENT_XML1) . ' - Latest Torrents</title>' . "\n";
        $xml .= '  <link>' . htmlspecialchars($BASEURL, ENT_XML1) . '</link>' . "\n";
        $xml .= '  <description>Latest torrent releases</description>' . "\n";
        $xml .= '  <language>en-us</language>' . "\n";
        
        foreach ($torrents as $torrent) {
            $xml .= '  <item>' . "\n";
            $xml .= '    <title>' . htmlspecialchars($torrent['name'], ENT_XML1) . '</title>' . "\n";
            $xml .= '    <link>' . htmlspecialchars($BASEURL . '/details.php?id=' . $torrent['id'], ENT_XML1) . '</link>' . "\n";
            $xml .= '    <guid>' . htmlspecialchars($BASEURL . '/details.php?id=' . $torrent['id'], ENT_XML1) . '</guid>' . "\n";
            $xml .= '    <pubDate>' . date(DATE_RSS, strtotime($torrent['added'])) . '</pubDate>' . "\n";
            $xml .= '    <description><![CDATA[' . substr(strip_tags($torrent['description'] ?? ''), 0, 500) . ']]></description>' . "\n";
            $xml .= '    <enclosure url="' . htmlspecialchars($BASEURL . '/download.php?id=' . $torrent['id'], ENT_XML1) . '" length="' . $torrent['size'] . '" type="application/x-bittorrent"/>' . "\n";
            $xml .= '  </item>' . "\n";
        }
        
        $xml .= '</channel>' . "\n";
        $xml .= '</rss>';
        
        return $xml;
    }
    
    /**
     * Send successful JSON response
     */
    private function sendSuccess(mixed $data): void
    {
        echo json_encode([
            'success' => true,
            'data' => $data
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        exit;
    }
    
    /**
     * Send error JSON response
     */
    private function sendError(string $message, int $code = 400): void
    {
        http_response_code($code);
        echo json_encode([
            'success' => false,
            'error' => [
                'message' => $message,
                'code' => $code
            ]
        ], JSON_PRETTY_PRINT);
        exit;
    }
}
