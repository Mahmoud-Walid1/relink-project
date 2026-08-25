<?php

require_once __DIR__ . '/../Models/Folder.php';
require_once __DIR__ . '/../Services/SlugService.php';
require_once __DIR__ . '/../Services/AuthService.php';
require_once __DIR__ . '/../Helpers/ResponseHelper.php';
require_once __DIR__ . '/../Helpers/RequestHelper.php';

class FolderApiController {
    private Folder $folderModel;

    public function __construct() {
        AuthService::requireAuth();
        $this->folderModel = new Folder();
    }

    public function list(): void {
        $folders = $this->folderModel->getAll();
        ResponseHelper::success(['folders' => $folders]);
    }

    public function create(): void {
        $input = RequestHelper::getJsonInput();
        $name = trim($input['name'] ?? '');
        $customSlug = trim($input['slug'] ?? '');
        $parentId = !empty($input['parent_id']) ? (int)$input['parent_id'] : null;

        if (empty($name)) {
            ResponseHelper::error('اسم المجلد مطلوب');
            return;
        }

        $slug = SlugService::createSlug($name, $customSlug);
        $folderId = $this->folderModel->create($name, $slug, $parentId);

        ResponseHelper::success([
            'folder' => $this->folderModel->findById($folderId)
        ], 'تم إنشاء المجلد بنجاح');
    }

    public function update(int $id): void {
        $input = RequestHelper::getJsonInput();
        $name = trim($input['name'] ?? '');
        $customSlug = trim($input['slug'] ?? '');
        $parentId = !empty($input['parent_id']) ? (int)$input['parent_id'] : null;

        if (empty($name)) {
            ResponseHelper::error('اسم المجلد مطلوب');
            return;
        }

        if ($parentId === $id) {
            ResponseHelper::error('لا يمكن تعيين المجلد كأب لنفسه');
            return;
        }

        $slug = SlugService::createSlug($name, $customSlug);
        $this->folderModel->update($id, $name, $slug, $parentId);

        ResponseHelper::success([
            'folder' => $this->folderModel->findById($id)
        ], 'تم تحديث المجلد بنجاح');
    }

    public function delete(int $id): void {
        $this->folderModel->delete($id);
        ResponseHelper::success([], 'تم حذف المجلد وكافة محتوياته');
    }
}
