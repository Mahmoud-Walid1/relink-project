<?php

require_once __DIR__ . '/../../config/database.php';

class Link {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function create(?int $folderId, string $title, string $slug, string $targetUrl, int $isActive = 1, int $trackAnalytics = 0): int {
        $stmt = $this->db->prepare("INSERT INTO links (folder_id, title, slug, target_url, is_active, track_analytics) VALUES (:folder_id, :title, :slug, :target_url, :is_active, :track_analytics)");
        $stmt->execute([
            ':folder_id' => $folderId,
            ':title' => $title,
            ':slug' => $slug,
            ':target_url' => $targetUrl,
            ':is_active' => $isActive,
            ':track_analytics' => $trackAnalytics
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, ?int $folderId, string $title, string $slug, string $targetUrl, int $isActive, int $trackAnalytics): bool {
        $stmt = $this->db->prepare("UPDATE links SET folder_id = :folder_id, title = :title, slug = :slug, target_url = :target_url, is_active = :is_active, track_analytics = :track_analytics WHERE id = :id");
        return $stmt->execute([
            ':id' => $id,
            ':folder_id' => $folderId,
            ':title' => $title,
            ':slug' => $slug,
            ':target_url' => $targetUrl,
            ':is_active' => $isActive,
            ':track_analytics' => $trackAnalytics
        ]);
    }

    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM links WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    public function findById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM links WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function findByFolderAndSlug(?int $folderId, string $slug): ?array {
        $sql = "SELECT * FROM links WHERE slug = :slug AND " . ($folderId === null ? "folder_id IS NULL" : "folder_id = :folder_id");
        $stmt = $this->db->prepare($sql);
        $params = [':slug' => $slug];
        if ($folderId !== null) {
            $params[':folder_id'] = $folderId;
        }
        $stmt->execute($params);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function incrementClick(int $id): void {
        $stmt = $this->db->prepare("UPDATE links SET click_count = click_count + 1, last_accessed_at = CURRENT_TIMESTAMP WHERE id = :id");
        $stmt->execute([':id' => $id]);
    }

    public function getAll(): array {
        $stmt = $this->db->query("SELECT * FROM links ORDER BY created_at DESC");
        return $stmt->fetchAll();
    }

    public function search(string $query): array {
        $stmt = $this->db->prepare("SELECT * FROM links WHERE title LIKE :q OR slug LIKE :q OR target_url LIKE :q ORDER BY title ASC");
        $stmt->execute([':q' => '%' . $query . '%']);
        return $stmt->fetchAll();
    }
}
