<?php

$baseDir = is_dir(__DIR__ . '/src') ? __DIR__ : __DIR__ . '/..';

require_once $baseDir . '/src/Services/AuthService.php';
require_once $baseDir . '/src/Controllers/AdminController.php';
require_once $baseDir . '/src/Controllers/AuthController.php';
require_once $baseDir . '/src/Controllers/FolderApiController.php';
require_once $baseDir . '/src/Controllers/LinkApiController.php';
require_once $baseDir . '/src/Controllers/RedirectController.php';

AuthService::initSession();

$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// Handle API Routing
if (strpos($requestUri, '/api/') === 0) {
    header('Content-Type: application/json; charset=utf-8');

    if ($requestUri === '/api/auth/login' && $method === 'POST') {
        (new AuthController())->login();
    } elseif ($requestUri === '/api/auth/logout' && $method === 'POST') {
        (new AuthController())->logout();
    } elseif ($requestUri === '/api/auth/me' && $method === 'GET') {
        (new AuthController())->me();
    }
    // Folder API
    elseif ($requestUri === '/api/folders' && $method === 'GET') {
        (new FolderApiController())->list();
    } elseif ($requestUri === '/api/folders' && $method === 'POST') {
        (new FolderApiController())->create();
    } elseif (preg_match('#^/api/folders/(\d+)$#', $requestUri, $matches)) {
        $folderId = (int)$matches[1];
        if ($method === 'PUT') (new FolderApiController())->update($folderId);
        elseif ($method === 'DELETE') (new FolderApiController())->delete($folderId);
    }
    // Link API
    elseif ($requestUri === '/api/links' && $method === 'GET') {
        (new LinkApiController())->list();
    } elseif ($requestUri === '/api/links/search' && $method === 'GET') {
        (new LinkApiController())->search();
    } elseif ($requestUri === '/api/links' && $method === 'POST') {
        (new LinkApiController())->create();
    } elseif (preg_match('#^/api/links/(\d+)$#', $requestUri, $matches)) {
        $linkId = (int)$matches[1];
        if ($method === 'PUT') (new LinkApiController())->update($linkId);
        elseif ($method === 'DELETE') (new LinkApiController())->delete($linkId);
    } elseif (preg_match('#^/api/links/analytics/(\d+)$#', $requestUri, $matches)) {
        $linkId = (int)$matches[1];
        if ($method === 'GET') (new LinkApiController())->getAnalytics($linkId);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'API endpoint not found']);
    }
    exit;
}

// Admin UI Route
if ($requestUri === '/admin' || $requestUri === '/admin/') {
    AdminController::render();
    exit;
}

// Short Link Dynamic Redirection Router (Strict Anti-Cache)
RedirectController::handle($_SERVER['REQUEST_URI']);
