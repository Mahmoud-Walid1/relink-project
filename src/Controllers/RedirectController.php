<?php

require_once __DIR__ . '/../Models/Folder.php';
require_once __DIR__ . '/../Models/Link.php';
require_once __DIR__ . '/../Models/ClickLog.php';

class RedirectController {
    public static function handle(string $requestUri): void {
        // Strict Anti-Caching Headers (Prevent browser from caching redirects)
        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");
        header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");

        $uri = trim(parse_url($requestUri, PHP_URL_PATH), '/');
        if (empty($uri)) {
            self::renderHomePage();
            return;
        }

        $parts = array_values(array_filter(explode('/', $uri)));
        $folderModel = new Folder();
        $linkModel = new Link();

        $currentFolderId = null;
        $allFolders = $folderModel->getAll();

        // Resolve folders along path
        for ($i = 0; $i < count($parts) - 1; $i++) {
            $slug = $parts[$i];
            $found = false;
            foreach ($allFolders as $folder) {
                if ($folder['slug'] === $slug && (int)$folder['parent_id'] === (int)$currentFolderId) {
                    $currentFolderId = (int)$folder['id'];
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                self::render404("المجلد التابع للرابط غير موجود");
                return;
            }
        }

        $targetSlug = end($parts);
        $link = $linkModel->findByFolderAndSlug($currentFolderId, $targetSlug);

        if (!$link) {
            // Check if full path resolves to a folder instead
            self::render404("الرابط غير موجود");
            return;
        }

        // Check if link is paused / disabled
        if ((int)$link['is_active'] === 0) {
            self::renderDisabledPage($link['title']);
            return;
        }

        // Track analytics if enabled for this link
        if ((int)$link['track_analytics'] === 1) {
            $linkModel->incrementClick((int)$link['id']);
            $clickLog = new ClickLog();
            $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
            $ua = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
            $clickLog->log((int)$link['id'], $ip, $ua);
        }

        // Execute HTTP 302 Temporary Redirect (Forces browser revalidation every time)
        header("Location: " . $link['target_url'], true, 302);
        exit;
    }

    private static function renderHomePage(): void {
        require_once __DIR__ . '/../Services/AuthService.php';
        if (AuthService::isLoggedIn()) {
            header("Location: /admin", true, 302);
            exit;
        }
        ?>
        <!DOCTYPE html>
        <html lang="ar" dir="rtl">
        <head>
            <meta charset="UTF-8">
            <title>Relink - نظام إعادة التوجيه للإنتاج المعرفي</title>
            <style>
                body { font-family: 'Segoe UI', Tahoma, sans-serif; background: #0f172a; color: #f8fafc; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; text-align: center; }
                .card { background: #1e293b; padding: 40px; border-radius: 16px; box-shadow: 0 10px 25px rgba(0,0,0,0.5); max-width: 400px; width: 90%; }
                h1 { color: #38bdf8; font-size: 24px; margin-bottom: 12px; }
                p { color: #94a3b8; font-size: 14px; margin-bottom: 24px; }
                a { display: inline-block; background: #0284c7; color: white; padding: 12px 24px; border-radius: 8px; text-decoration: none; font-weight: bold; transition: background 0.2s; }
                a:hover { background: #0369a1; }
            </style>
        </head>
        <body>
            <div class="card">
                <h1>Relink Engine</h1>
                <p>نظام إدارة وتوجيه روابط الإنتاج المعرفي الديناميكي.</p>
                <a href="/admin">دخول لوحة التحكم</a>
            </div>
        </body>
        </html>
        <?php
        exit;
    }

    private static function renderDisabledPage(string $title): void {
        ?>
        <!DOCTYPE html>
        <html lang="ar" dir="rtl">
        <head>
            <meta charset="UTF-8">
            <title>الرابط معطل مؤقتاً</title>
            <style>
                body { font-family: 'Segoe UI', Tahoma, sans-serif; background: #0f172a; color: #f8fafc; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; text-align: center; }
                .card { background: #1e293b; padding: 40px; border-radius: 16px; border-top: 4px solid #ef4444; max-width: 450px; width: 90%; }
                h2 { color: #f87171; font-size: 22px; }
                p { color: #94a3b8; line-height: 1.6; }
            </style>
        </head>
        <body>
            <div class="card">
                <h2>الرابط غير متاح حالياً</h2>
                <p>تم إيقاف رابط الدرس (<?= htmlspecialchars($title) ?>) مؤقتاً من قبل الإدارة. يرجى المحاولة لاحقاً.</p>
            </div>
        </body>
        </html>
        <?php
        exit;
    }

    private static function render404(string $message): void {
        http_response_code(404);
        ?>
        <!DOCTYPE html>
        <html lang="ar" dir="rtl">
        <head>
            <meta charset="UTF-8">
            <title>الرابط غير موجود (404)</title>
            <style>
                body { font-family: 'Segoe UI', Tahoma, sans-serif; background: #0f172a; color: #f8fafc; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; text-align: center; }
                .card { background: #1e293b; padding: 40px; border-radius: 16px; border-top: 4px solid #eab308; max-width: 450px; width: 90%; }
                h2 { color: #facc15; font-size: 22px; }
                p { color: #94a3b8; }
            </style>
        </head>
        <body>
            <div class="card">
                <h2>خطأ 404 - الرابط غير موجود</h2>
                <p><?= htmlspecialchars($message) ?></p>
            </div>
        </body>
        </html>
        <?php
        exit;
    }
}
