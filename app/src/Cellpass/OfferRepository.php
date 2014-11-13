<?php

namespace Cellpass;

use Doctrine\DBAL\Connection;

class OfferRepository
{
    private $conn;

    public function __construct(Connection $conn)
    {
        $this->conn = $conn;
    }

    public function findAll()
    {
        $stmt = $this->conn->prepare('SELECT * FROM cellpass_offer');
        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function find($id)
    {
        $stmt = $this->conn->prepare('SELECT * FROM cellpass_offer WHERE id = :id');
        $stmt->bindValue(':id', $id);
        $stmt->execute();

        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function create(array $data = array())
    {
        $sql = 'INSERT INTO cellpass_offer (service_id, name, type, price, operator) VALUES (:service_id, :name, :type, :price, :operator)';

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':service_id', $data['service_id']);
        $stmt->bindValue(':name', $data['name']);
        $stmt->bindValue(':type', $data['type']);
        $stmt->bindValue(':price', $data['price']);
        $stmt->bindValue(':operator', $data['operator']);
        $stmt->execute();
    }

    public function update($id, array $data = array())
    {
        $sql = 'UPDATE cellpass_offer SET service_id = :service_id, name = :name, type = :type, price = :price, operator = :operator WHERE id = :id';

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':service_id', $data['service_id']);
        $stmt->bindValue(':name', $data['name']);
        $stmt->bindValue(':type', $data['type']);
        $stmt->bindValue(':price', $data['price']);
        $stmt->bindValue(':operator', $data['operator']);
        $stmt->bindValue(':id', $id);
        $stmt->execute();
    }

    public function findByServiceId($service_id)
    {
        $stmt = $this->conn->prepare('SELECT * FROM cellpass_offer WHERE service_id = :service_id');
        $stmt->bindValue(':service_id', $service_id);
        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
