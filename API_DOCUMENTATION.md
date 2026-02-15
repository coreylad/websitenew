# Modern Torrent Tracker API Documentation

**Version:** 10.0  
**Base URL:** `https://yourdomain.com/api.php`  
**Response Format:** JSON  
**Compatible with:** Standard torrent tracker API specifications

---

## Authentication

All API requests require authentication via one of the following methods:

### Method 1: API Key Header (Recommended)
```http
GET /api.php?endpoint=torrents
X-API-Key: your_api_key_here
```

### Method 2: API Key Query Parameter
```http
GET /api.php?endpoint=torrents&api_key=your_api_key_here
```

### Method 3: Session Authentication
Use existing session cookies from logged-in user.

---

## Endpoints

### 1. List/Search Torrents

**Endpoint:** `GET /api.php?endpoint=torrents`

**Query Parameters:**
- `search` (string, optional) - Search query for torrent name/description
- `category_id` (integer, optional) - Filter by category ID
- `type` (string, optional) - Filter by type (movie, tv, game, music, etc.)
- `free` (boolean, optional) - Filter free torrents (true/false)
- `page` (integer, optional) - Page number (default: 1)
- `per_page` (integer, optional) - Results per page (default: 50, max: 100)

**Example Request:**
```http
GET /api.php?endpoint=torrents&search=action&category_id=1&free=true&page=1&per_page=25
X-API-Key: your_api_key_here
```

**Example Response:**
```json
{
  "success": true,
  "data": {
    "data": [
      {
        "id": 123,
        "name": "Example Torrent",
        "category_id": 1,
        "type": "movie",
        "size": 1073741824,
        "size_formatted": "1.00 GB",
        "info_hash": "abc123def456...",
        "seeders": 10,
        "leechers": 5,
        "times_completed": 50,
        "created_at": "2024-01-01 12:00:00",
        "is_free": true,
        "is_double_upload": false,
        "download_link": "https://yourdomain.com/download.php?id=123"
      }
    ],
    "meta": {
      "current_page": 1,
      "per_page": 25,
      "total": 100,
      "last_page": 4
    }
  }
}
```

---

### 2. Get Single Torrent Details

**Endpoint:** `GET /api.php?endpoint=torrent&id={id}`

**Query Parameters:**
- `id` (integer, required) - Torrent ID

**Example Request:**
```http
GET /api.php?endpoint=torrent&id=123
X-API-Key: your_api_key_here
```

**Example Response:**
```json
{
  "success": true,
  "data": {
    "id": 123,
    "name": "Example Torrent",
    "category_id": 1,
    "category_name": "Movies",
    "type": "movie",
    "size": 1073741824,
    "size_formatted": "1.00 GB",
    "info_hash": "abc123def456...",
    "seeders": 10,
    "leechers": 5,
    "times_completed": 50,
    "created_at": "2024-01-01 12:00:00",
    "is_free": true,
    "is_double_upload": false,
    "description": "Torrent description here...",
    "uploader": {
      "id": 1,
      "username": "uploader_name"
    },
    "download_link": "https://yourdomain.com/download.php?id=123",
    "files": [
      {
        "filename": "movie.mkv",
        "size": 1073741824
      }
    ]
  }
}
```

---

### 3. List Categories

**Endpoint:** `GET /api.php?endpoint=categories`

**Example Request:**
```http
GET /api.php?endpoint=categories
X-API-Key: your_api_key_here
```

**Example Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Movies",
      "image": "movies.png",
      "sort_index": 1
    },
    {
      "id": 2,
      "name": "TV Shows",
      "image": "tv.png",
      "sort_index": 2
    }
  ]
}
```

---

### 4. Get Tracker Statistics

**Endpoint:** `GET /api.php?endpoint=stats`

**Example Request:**
```http
GET /api.php?endpoint=stats
X-API-Key: your_api_key_here
```

**Example Response:**
```json
{
  "success": true,
  "data": {
    "torrents": {
      "total": 10000,
      "seeders": 5000,
      "leechers": 2000,
      "total_size": 10995116277760,
      "total_downloads": 50000
    },
    "users": {
      "total": 1000,
      "active": 800
    }
  }
}
```

---

### 5. Get Current User Info

**Endpoint:** `GET /api.php?endpoint=user`

**Example Request:**
```http
GET /api.php?endpoint=user
X-API-Key: your_api_key_here
```

**Example Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "username": "john_doe",
    "email": "john@example.com",
    "uploaded": 107374182400,
    "downloaded": 53687091200,
    "ratio": 2.0,
    "seedbonus": 100.50,
    "invites": 5,
    "uploads": 50,
    "active_uploads": 45,
    "class": "Power User",
    "joined": "2023-01-01 00:00:00"
  }
}
```

---

### 6. RSS Feed

**Endpoint:** `GET /api.php?endpoint=rss`

**Query Parameters:**
- `limit` (integer, optional) - Number of torrents to include (default: 50, max: 100)

**Example Request:**
```http
GET /api.php?endpoint=rss&limit=25
X-API-Key: your_api_key_here
```

**Response:** RSS 2.0 XML feed

---

## Error Responses

All errors return a JSON response with appropriate HTTP status code:

```json
{
  "success": false,
  "error": {
    "message": "Error description",
    "code": 400
  }
}
```

### Common Error Codes:
- `400` - Bad Request (invalid parameters)
- `401` - Unauthorized (invalid or missing API key)
- `404` - Not Found (resource doesn't exist)
- `405` - Method Not Allowed (wrong HTTP method)
- `500` - Internal Server Error

---

## Rate Limiting

API requests are rate-limited per user:
- **Authenticated users:** 300 requests per minute
- **Unauthenticated:** 60 requests per minute

Rate limit headers are included in responses:
```
X-RateLimit-Limit: 300
X-RateLimit-Remaining: 299
X-RateLimit-Reset: 1234567890
```

---

## Data Types

### Size Units
File sizes support from Bytes to Exabytes:
- B (Bytes)
- KB (Kilobytes) - 1,024 bytes
- MB (Megabytes) - 1,048,576 bytes
- GB (Gigabytes) - 1,073,741,824 bytes
- TB (Terabytes) - 1,099,511,627,776 bytes
- PB (Petabytes) - 1,125,899,906,842,624 bytes
- EB (Exabytes) - 1,152,921,504,606,846,976 bytes

### Date Format
All dates are in `YYYY-MM-DD HH:MM:SS` format (MySQL DATETIME).

---

## Best Practices

1. **Use HTTPS** - Always use HTTPS for API requests to protect API keys
2. **Cache responses** - Cache category and stats data to reduce API calls
3. **Implement pagination** - Use pagination for large result sets
4. **Handle errors** - Always check for `success: false` in responses
5. **API key security** - Never expose API keys in client-side code

---

## Example Implementations

### PHP Example
```php
<?php

function getTorrents(string $apiKey, array $params = []): array
{
    $baseUrl = 'https://yourdomain.com/api.php';
    $params['endpoint'] = 'torrents';
    
    $url = $baseUrl . '?' . http_build_query($params);
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'X-API-Key: ' . $apiKey
    ]);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    return json_decode($response, true);
}

// Usage
$result = getTorrents('your_api_key', [
    'search' => 'action',
    'category_id' => 1,
    'page' => 1,
    'per_page' => 50
]);

if ($result['success']) {
    foreach ($result['data']['data'] as $torrent) {
        echo $torrent['name'] . ' - ' . $torrent['size_formatted'] . "\n";
    }
}
```

### JavaScript Example
```javascript
async function getTorrents(apiKey, params = {}) {
    params.endpoint = 'torrents';
    
    const url = `https://yourdomain.com/api.php?${new URLSearchParams(params)}`;
    
    const response = await fetch(url, {
        headers: {
            'X-API-Key': apiKey
        }
    });
    
    return await response.json();
}

// Usage
getTorrents('your_api_key', {
    search: 'action',
    category_id: 1,
    page: 1,
    per_page: 50
}).then(result => {
    if (result.success) {
        result.data.data.forEach(torrent => {
            console.log(`${torrent.name} - ${torrent.size_formatted}`);
        });
    }
});
```

### Python Example
```python
import requests

def get_torrents(api_key, **params):
    params['endpoint'] = 'torrents'
    
    response = requests.get(
        'https://yourdomain.com/api.php',
        params=params,
        headers={'X-API-Key': api_key}
    )
    
    return response.json()

# Usage
result = get_torrents(
    'your_api_key',
    search='action',
    category_id=1,
    page=1,
    per_page=50
)

if result['success']:
    for torrent in result['data']['data']:
        print(f"{torrent['name']} - {torrent['size_formatted']}")
```

---

## Changelog

### Version 10.0 (2024)
- Initial modern API implementation
- PHP 8.5+ compatibility with strict types
- PDO prepared statements for security
- Petabyte+ file size support
- RESTful JSON API design
- RSS feed support
- Comprehensive error handling
- Rate limiting ready
- CORS support

---

## Support

For API support or bug reports, please contact the tracker administrators.
