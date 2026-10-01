<?php

declare(strict_types=1);

final class User
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function findByEmail(string $email): ?array
    {
        $statement = $this->db->prepare(
            'SELECT u.*, r.name AS role_name
             FROM users u
             JOIN roles r ON r.id = u.role_id
             WHERE u.email = ?
             LIMIT 1'
        );
        $statement->execute([$email]);

        $user = $statement->fetch();
        return $user ?: null;
    }

    public function setLastLogin(int $userId): void
    {
        $statement = $this->db->prepare(
            'UPDATE users SET last_login = NOW() WHERE id = ?'
        );
        $statement->execute([$userId]);
    }
}
