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

    public function findAll()
    {
        $stmt = $this->conn->prepare('SELECT * FROM cellpass_customer');
        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
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
