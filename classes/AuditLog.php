<?php
/**
 * AuditLog — ecommerce_db.audit_logs
 * Columns: id, user_id, action, table_name, record_id,
 *          old_values, new_values, ip_address, created_at
 */
class AuditLog
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Write an audit entry.
     *
     * @param string     $action     e.g. 'user.login', 'order.status_changed'
     * @param string     $tableName  DB table affected
     * @param int|null   $recordId   PK of the affected row
     * @param array|null $oldValues  snapshot before change
     * @param array|null $newValues  snapshot after change
     * @param int|null   $userId     acting user (null for system/unauthenticated)
     */
    public function write(
        string $action,
        string $tableName = '',
        ?int   $recordId  = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?int   $userId    = null
    ): void {
        // Attempt to get user ID from session if not supplied
        if ($userId === null && isset($_SESSION['user_id'])) {
            $userId = (int) $_SESSION['user_id'];
        }

        $ip = $this->getClientIp();

        $stmt = $this->db->prepare(
            'INSERT INTO audit_logs
               (user_id, action, table_name, record_id, old_values, new_values, ip_address)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $userId,
            $action,
            $tableName ?: null,
            $recordId,
            $oldValues ? json_encode($oldValues, JSON_UNESCAPED_UNICODE) : null,
            $newValues ? json_encode($newValues, JSON_UNESCAPED_UNICODE) : null,
            $ip,
        ]);
    }

    /** Get recent audit entries — admin view */
    public function getRecent(int $limit = 50): array
    {
        $limit = max(1, min($limit, 200));
        $stmt = $this->db->query(
            'SELECT a.*, CONCAT(u.first_name," ",u.last_name) AS user_name, u.email
             FROM audit_logs a
             LEFT JOIN users u ON u.id = a.user_id
             ORDER BY a.created_at DESC
             LIMIT ' . $limit
        );
        return $stmt->fetchAll();
    }

    /** Get audit entries for a specific user */
    public function getForUser(int $userId, int $limit = 30): array
    {
        $limit = max(1, min($limit, 200));
        $stmt = $this->db->prepare(
            'SELECT * FROM audit_logs WHERE user_id = ?
             ORDER BY created_at DESC LIMIT ' . $limit
        );
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    /** Get audit entries for a specific record */
    public function getForRecord(string $tableName, int $recordId): array
    {
        $stmt = $this->db->prepare(
            'SELECT a.*, CONCAT(u.first_name," ",u.last_name) AS user_name
             FROM audit_logs a
             LEFT JOIN users u ON u.id = a.user_id
             WHERE a.table_name = ? AND a.record_id = ?
             ORDER BY a.created_at DESC'
        );
        $stmt->execute([$tableName, $recordId]);
        return $stmt->fetchAll();
    }

    private function getClientIp(): string
    {
        foreach (['HTTP_CLIENT_IP','HTTP_X_FORWARDED_FOR','REMOTE_ADDR'] as $key) {
            if (!empty($_SERVER[$key])) {
                $ip = trim(explode(',', $_SERVER[$key])[0]);
                if (filter_var($ip, FILTER_VALIDATE_IP)) return $ip;
            }
        }
        return '0.0.0.0';
    }
}
