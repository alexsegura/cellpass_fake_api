<?php

namespace Cellpass;

use Doctrine\DBAL\Connection;

class CustomerRepository
{
    private $conn;

    public function __construct(Connection $conn)
    {
        $this->conn = $conn;
    }

    public function create(array $data = array())
    {
        $sql = 'INSERT INTO cellpass_customer (editor_id) VALUES (:editor_id)';

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':editor_id', $data['editor_id']);
        $stmt->execute();

        return [
            'id' => $this->conn->lastInsertId(),
            'editor_id' => $data['editor_id']
        ];
    }
}
