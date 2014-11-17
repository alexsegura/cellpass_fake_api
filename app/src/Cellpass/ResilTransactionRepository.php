<?php

namespace Cellpass;

use Doctrine\DBAL\Connection;

class ResilTransactionRepository
{
    private $conn;

    public function __construct(Connection $conn)
    {
        $this->conn = $conn;
    }

    public function find($id)
    {
        $stmt = $this->conn->prepare('SELECT * FROM cellpass_resil_transaction WHERE id = :id');
        $stmt->bindValue(':id', $id);
        $stmt->execute();

        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function create(array $data = array())
    {
        $sql =
        'INSERT INTO cellpass_resil_transaction (id, subscription_id, state, url_ok, url_ko, ctime, mtime)'
        . ' VALUES '
        . '(:id, :subscription_id, "init", :url_ok, :url_ko, DATETIME("now"), DATETIME("now"))';

        $id = md5(time());

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id', $id);
        $stmt->bindValue(':subscription_id', $data['subscription_id']);
        $stmt->bindValue(':url_ok', $data['url_ok']);
        $stmt->bindValue(':url_ko', isset($data['url_ko']) ? $data['url_ko'] : null);
        $stmt->execute();

        return $id;
    }
}
