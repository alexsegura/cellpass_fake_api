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
        . '(:id, :customer_id, :offer_id, :date_sub)';

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id', $data['id']);
        $stmt->bindValue(':customer_id', $data['customer_id']);
        $stmt->bindValue(':offer_id', $data['offer_id']);
        $stmt->bindValue(':date_sub', date('Y-m-d H:i:s'));
        $stmt->execute();

        return $id;
    }

    public function find($id)
    {
        $stmt = $this->conn->prepare('SELECT * FROM cellpass_subscription WHERE id = :id');
        $stmt->bindValue(':id', $id);
        $stmt->execute();

        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function findAll()
    {
        $stmt = $this->conn->prepare('SELECT * FROM cellpass_subscription');
        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function update($id, array $data = array())
    {
        $keys = ['date_unsub', 'date_eff_unsub'];
        $fields = [];
        foreach ($keys as $key) {
            if (isset($data[$key])) {
                $fields[] = "$key = :$key";
            }
        }

        if (!empty($fields)) {
            $sql = 'UPDATE cellpass_subscription SET ' . implode(', ', $fields) . ' WHERE id = :id';

            $stmt = $this->conn->prepare($sql);

            foreach ($keys as $key) {
                if (isset($data[$key])) {
                    $stmt->bindValue(":$key", $data[$key]);
                }
            }

            $stmt->bindValue(':id', $id);
            $stmt->execute();
        }
    }
}
