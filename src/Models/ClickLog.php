<?php

require_once __DIR__ . '/../../config/database.php';

class ClickLog {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function log(int $linkId, string $ipAddress, string $userAgent): void {
        $stmt = $this->db->prepare("INSERT INTO click_logs (link_id, ip_address, user_agent) VALUES (:link_id, :ip_address, :user_agent)");
        $stmt->execute([
            ':link_id' => $linkId,
            ':ip_address' => $ipAddress,
            ':user_agent' => $userAgent
        ]);
    }

    public function getLogsForLink(int $linkId, int $limit = 50): array {
        $stmt = $this->db->prepare("SELECT * FROM click_logs WHERE link_id = :link_id ORDER BY clicked_at DESC LIMIT :limit");
        $stmt->bindValue(':link_id', $linkId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
