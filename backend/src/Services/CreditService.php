<?php

class CreditService {

    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function getAvailableCredits($userId) {

        $stmt = $this->conn->prepare("
            SELECT SUM(remaining_credits) as total
            FROM user_credit_batches
            WHERE user_id = ?
              AND remaining_credits > 0
              AND expires_at > NOW()
        ");

        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();

        return (int)($result['total'] ?? 0);
    }

    public function consumeOne($userId) {

        // ✅ obtener el lote que expira primero
        $stmt = $this->conn->prepare("
            SELECT id, remaining_credits
            FROM user_credit_batches
            WHERE user_id = ?
            AND remaining_credits > 0
            AND expires_at > NOW()
            ORDER BY expires_at ASC
            LIMIT 1
        ");

        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $batch = $stmt->get_result()->fetch_assoc();

        if (!$batch) {
            return false; // no hay créditos
        }

        // ✅ descontar 1
        $stmt = $this->conn->prepare("
            UPDATE user_credit_batches
            SET remaining_credits = remaining_credits - 1
            WHERE id = ?
        ");

        $stmt->bind_param("i", $batch['id']);
        $stmt->execute();

        return true;
    }

}