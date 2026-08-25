<?php

require_once __DIR__ . '/../Models/Link.php';
require_once __DIR__ . '/../Models/Folder.php';
require_once __DIR__ . '/../Models/ClickLog.php';
require_once __DIR__ . '/../Services/SlugService.php';
require_once __DIR__ . '/../Services/AuthService.php';
require_once __DIR__ . '/../Helpers/ResponseHelper.php';
require_once __DIR__ . '/../Helpers/RequestHelper.php';

class LinkApiController {
    private Link $linkModel;
    private Folder $folderModel;

    public function __construct() {
        AuthService::requireAuth();
        $this->linkModel = new Link();
        $this->folderModel = new Folder();
    }

    public function list(): void {
        $links = $this->linkModel->getAll();
        ResponseHelper::success(['links' => $links]);
    }

    public function search(): void {
        $q = trim($_GET['q'] ?? '');
        $links = empty($q) ? $this->linkModel->getAll() : $this->linkModel->search($q);
        ResponseHelper::success(['links' => $links]);
    }

    public function create(): void {
        $input = RequestHelper::getJsonInput();
        $title = trim($input['title'] ?? '');
        $targetUrl = trim($input['target_url'] ?? '');
        $customSlug = trim($input['slug'] ?? '');
        $folderId = !empty($input['folder_id']) ? (int)$input['folder_id'] : null;
        $isActive = isset($input['is_active']) ? (int)$input['is_active'] : 1;
        $trackAnalytics = isset($input['track_analytics']) ? (int)$input['track_analytics'] : 0;

        if (empty($title) || empty($targetUrl)) {
            ResponseHelper::error('عنوان الدرس ورابط الوجهة مطلوبان');
            return;
        }

        if (!filter_var($targetUrl, FILTER_VALIDATE_URL)) {
            ResponseHelper::error('رابط الوجهة غير صحيح');
            return;
        }

        $slug = SlugService::createSlug($title, $customSlug);
        $linkId = $this->linkModel->create($folderId, $title, $slug, $targetUrl, $isActive, $trackAnalytics);

        ResponseHelper::success([
            'link' => $this->linkModel->findById($linkId)
        ], 'تم إنشاء الرابط بنجاح');
    }

    public function update(int $id): void {
        $input = RequestHelper::getJsonInput();
        $title = trim($input['title'] ?? '');
        $targetUrl = trim($input['target_url'] ?? '');
        $customSlug = trim($input['slug'] ?? '');
        $folderId = !empty($input['folder_id']) ? (int)$input['folder_id'] : null;
        $isActive = isset($input['is_active']) ? (int)$input['is_active'] : 1;
        $trackAnalytics = isset($input['track_analytics']) ? (int)$input['track_analytics'] : 0;

        if (empty($title) || empty($targetUrl)) {
            ResponseHelper::error('عنوان الدرس ورابط الوجهة مطلوبان');
            return;
        }

        $slug = SlugService::createSlug($title, $customSlug);
        $this->linkModel->update($id, $folderId, $title, $slug, $targetUrl, $isActive, $trackAnalytics);

        ResponseHelper::success([
            'link' => $this->linkModel->findById($id)
        ], 'تم تحديث الرابط بنجاح');
    }

    public function delete(int $id): void {
        $this->linkModel->delete($id);
        ResponseHelper::success([], 'تم حذف الرابط بنجاح');
    }

    public function getAnalytics(int $id): void {
        $link = $this->linkModel->findById($id);
        if (!$link) {
            ResponseHelper::error('الرابط غير موجود');
            return;
        }

        $clickLog = new ClickLog();
        $logs = $clickLog->getLogsForLink($id);

        ResponseHelper::success([
            'link' => $link,
            'logs' => $logs
        ]);
    }
}
