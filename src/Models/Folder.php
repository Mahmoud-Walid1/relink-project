<?php

require_once __DIR__ . '/../../config/database.php';

class Folder {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function create(string $name, string $slug, ?int $parentId = null): int {
        $stmt = $this->db->prepare("INSERT INTO folders (name, slug, parent_id) VALUES (:name, :slug, :parent_id)");
        $stmt->execute([
            ':name' => $name,
            ':slug' => $slug,
            ':parent_id' => $parentId
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, string $name, string $slug, ?int $parentId = null): bool {
        $stmt = $this->db->prepare("UPDATE folders SET name = :name, slug = :slug, parent_id = :parent_id WHERE id = :id");
        return $stmt->execute([
            ':id' => $id,
            ':name' => $name,
            ':slug' => $slug,
            ':parent_id' => $parentId
        ]);
    }

    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM folders WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    public function findById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM folders WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function getAll(): array {
        $stmt = $this->db->query("SELECT * FROM folders ORDER BY name ASC");
        return $stmt->fetchAll();
    }

    public function getPathArray(int $folderId): array {
        $path = [];
        $currentId = $folderId;
        
        while ($currentId !== null) {
            $folder = $this->findById($currentId);
            if (!$folder) break;
            array_unshift($path, $folder);
            $currentId = $folder['parent_id'] ? (int)$folder['parent_id'] : null;
        }
        return $path;
    }
}
