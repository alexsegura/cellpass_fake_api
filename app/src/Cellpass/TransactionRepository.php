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
        $stmt = $this->conn->prepare('SELECT * FROM cellpass_transaction');
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
        'INSERT INTO cellpass_transaction (id, offer_id, customer_editor_id, state, url_ok, url_ko, ctime, mtime)'
        . ' VALUES '
        . '(:id, :offer_id, :customer_editor_id, "init", :url_ok, :url_ko, DATETIME("now"), DATETIME("now"))';

        $id = md5(time());

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id', $id);
        $stmt->bindValue(':offer_id', $data['offer_id']);
        $stmt->bindValue(':customer_editor_id', $data['customer_editor_id']);
        $stmt->bindValue(':url_ok', $data['url_ok']);
        $stmt->bindValue(':url_ko', isset($data['url_ko']) ? $data['url_ko'] : null);
        $stmt->execute();

        return $id;
    }

    public function updateSuccess($id, $success)
    {
        $stmt = $this->conn->prepare('UPDATE cellpass_transaction SET success = :success, mtime = DATETIME("NOW") WHERE id = :id');
        $stmt->bindValue(':success', $success ? 1 : 0);
        $stmt->bindValue(':id', $id);
        $stmt->execute();
    }

    public function updateState($id, $state)
    {
        $stmt = $this->conn->prepare('UPDATE cellpass_transaction SET state = :state, mtime = DATETIME("NOW") WHERE id = :id');
        $stmt->bindValue(':state', $state);
        $stmt->bindValue(':id', $id);
        $stmt->execute();
    }
}
