<?php

namespace Cellpass;

use Doctrine\DBAL\Connection;

class Db
{
    private $conn;

    public function __construct(Connection $conn)
    {
        $this->conn = $conn;
    }

    public function endTransaction($transaction_id, $editor_id)
    {
        $stmt = $this->conn->prepare('UPDATE cellpass_transaction SET state = "end" WHERE transaction_id = :transaction_id AND editor_id = :editor_id');
        $stmt->bindValue(':transaction_id', $transaction_id);
        $stmt->bindValue(':editor_id', $editor_id);
        $stmt->execute();

        // TODO Check affected rows
    }

    public function getTransaction($transaction_id)
    {
        $stmt = $this->conn->prepare('SELECT * FROM cellpass_transaction WHERE transaction_id = :transaction_id');
        $stmt->bindValue(':transaction_id', $transaction_id);
        $stmt->execute();

        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
}
