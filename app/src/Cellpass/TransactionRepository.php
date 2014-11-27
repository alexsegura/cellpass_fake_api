<?php

namespace Cellpass;

use Doctrine\DBAL\Connection;

class TransactionRepository
{
    private $conn;

    public function __construct(Connection $conn)
    {
        $this->conn = $conn;
    }

    public function findAll()
    {
        $stmt = $this->conn->prepare('SELECT * FROM cellpass_transaction ORDER BY ctime DESC');
        $stmt->execute();

        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        array_walk($rows, function(&$row) {
            $row['success'] = $row['success'] === null ? null : (bool) $row['success'];
        });

        return $rows;
    }

    public function find($id)
    {
        $stmt = $this->conn->prepare('SELECT * FROM cellpass_transaction WHERE id = :id');
        $stmt->bindValue(':id', $id);
        $stmt->execute();

        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function create(array $data = array())
    {
        $sql =
        'INSERT INTO cellpass_transaction (id, type, offer_id, customer_editor_id, subscription_id, state, url_ok, url_ko, ctime, mtime)'
        . ' VALUES '
        . '(:id, :type, :offer_id, :customer_editor_id, :subscription_id, "init", :url_ok, :url_ko, :ctime, :mtime)';

        $id = md5(time());

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id', $id);
        $stmt->bindValue(':type', $data['type']);
        $stmt->bindValue(':offer_id', isset($data['offer_id']) ? $data['offer_id'] : null);
        $stmt->bindValue(':customer_editor_id', isset($data['customer_editor_id']) ? $data['customer_editor_id'] : null);
        $stmt->bindValue(':subscription_id', isset($data['subscription_id']) ? $data['subscription_id'] : null);
        $stmt->bindValue(':url_ok', $data['url_ok']);
        $stmt->bindValue(':url_ko', isset($data['url_ko']) ? $data['url_ko'] : null);
        $stmt->bindValue(':ctime', date('Y-m-d H:i:s'));
        $stmt->bindValue(':mtime', date('Y-m-d H:i:s'));
        $stmt->execute();

        return $id;
    }

    public function update($id, array $data = array())
    {
        $keys = ['success', 'state', 'state_value', 'error', 'error_code'];
        $fields = [];
        foreach ($keys as $key) {
            if (isset($data[$key])) {
                $fields[] = "$key = :$key";
            }
        }

        if (!empty($fields)) {
            $sql = 'UPDATE cellpass_transaction SET ' . implode(', ', $fields) . ' WHERE id = :id';

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

    public function updateState($id, $state)
    {
        $stmt = $this->conn->prepare('UPDATE cellpass_transaction SET state = :state, mtime = :mtime WHERE id = :id');
        $stmt->bindValue(':state', $state);
        $stmt->bindValue(':id', $id);
        $stmt->bindValue(':mtime', date('Y-m-d H:i:s'));
        $stmt->execute();
    }
}
