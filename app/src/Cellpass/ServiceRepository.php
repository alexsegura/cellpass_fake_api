<?php

namespace Cellpass;

use Doctrine\DBAL\Connection;

class ServiceRepository
{
    private $conn;

    public function __construct(Connection $conn)
    {
        $this->conn = $conn;
    }

    public function findAll()
    {
        $stmt = $this->conn->prepare('SELECT * FROM cellpass_service');
        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function find($id)
    {
        $stmt = $this->conn->prepare('SELECT * FROM cellpass_service WHERE id = :id');
        $stmt->bindValue(':id', $id);
        $stmt->execute();

        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function create(array $data = array())
    {
        $sql = 'INSERT INTO cellpass_service (name) VALUES (:name)';

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':name', $data['name']);
        $stmt->execute();
    }

    public function update($id, array $data = array())
    {
        $sql = 'UPDATE cellpass_service SET name = :name WHERE id = :id';

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':name', $data['name']);
        $stmt->bindValue(':id', $id);
        $stmt->execute();
    }
}
