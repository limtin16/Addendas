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

    public function consumeOne($userId, $description = null) {

        // ✅ obtener lote más cercano a expirar
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
            return false;
        }

        $batchId = $batch['id'];

        // ✅ descontar crédito
        $stmt = $this->conn->prepare("
            UPDATE user_credit_batches
            SET remaining_credits = remaining_credits - 1
            WHERE id = ?
        ");

        $stmt->bind_param("i", $batchId);
        $stmt->execute();

        // ✅ registrar uso
        $stmt = $this->conn->prepare("
            INSERT INTO credit_usage_logs
            (user_id, batch_id, credits_used, description)
            VALUES (?, ?, 1, ?)
        ");

        $stmt->bind_param("iis", $userId, $batchId, $description);
        $stmt->execute();

        return true;
    }

}