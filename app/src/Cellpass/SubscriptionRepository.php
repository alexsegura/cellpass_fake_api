<?php

namespace Cellpass;

use Doctrine\DBAL\Connection;

class SubscriptionRepository
{
    private $conn;

    public function __construct(Connection $conn)
    {
        $this->conn = $conn;
    }

    public function create(array $data = array())
    {
        $sql =
        'INSERT INTO cellpass_subscription (id, customer_id, offer_id, date_sub)'
        . ' VALUES '
        . '(:id, :customer_id, :offer_id, DATETIME("now"))';

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id', $data['id']);
        $stmt->bindValue(':customer_id', $data['customer_id']);
        $stmt->bindValue(':offer_id', $data['offer_id']);
        $stmt->execute();

        return $id;
    }

    public function findAll()
    {
        $stmt = $this->conn->prepare('SELECT * FROM cellpass_subscription');
        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
