<?php

declare(strict_types=1);

/**
 * Modern Torrent API Endpoint - PHP 8.5+ Compatible
 * RESTful JSON API for torrent tracker operations
 * Compatible with standard torrent tracker API specifications
 * 
 * Endpoints:
 * - GET /api.php?endpoint=torrents - List/search torrents
 * - GET /api.php?endpoint=torrent&id=123 - Get single torrent details
 * - GET /api.php?endpoint=categories - List categories
 * - GET /api.php?endpoint=stats - Get tracker statistics
 * - GET /api.php?endpoint=user - Get current user info
 * - GET /api.php?endpoint=rss - RSS feed
 * 
 * Authentication:
 * - API Key in header: X-API-Key: your_key_here
 * - API Key in query: ?api_key=your_key_here
 * - Session authentication
 * 
 * Example requests:
 * - GET /api.php?endpoint=torrents&search=movie&category_id=1&page=1&per_page=50
 * - GET /api.php?endpoint=torrents&free=true&type=movie
 * - GET /api.php?endpoint=torrent&id=123
 * - GET /api.php?endpoint=categories
 * - GET /api.php?endpoint=stats
 * - GET /api.php?endpoint=user
 * - GET /api.php?endpoint=rss&limit=50
 */

define('THIS_SCRIPT', 'api');
define('IN_TRACKER', true);

require_once __DIR__ . '/include/class_torrent_api_handler.php';

// Initialize and handle API request
$api = new TorrentAPIHandler();
$api->handleRequest();
