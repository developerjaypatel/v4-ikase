<?php

abstract class DbAccess {

    public string $last_error = '';
    private PDO $db;

    function __construct(PDO $datalink) {
        $this->db = $datalink;
    }

    private function tryCatch(callable $queryRunner) {
        try {
            return $queryRunner();
        }
        catch (PDOException $e) {
            //old code used "mysql_query() or die()", but also would store the error...??
            die($e->__toString());
            $this->last_error = $e->__toString();
            return false;
        }
    }

    /**
     * @param $sql
     * @return false|PDOStatement
     */
    protected function query($sql) {
        return $this->tryCatch(fn() => $this->db->query($sql));
    }

    protected function getResults($sql, $fetchStyle = PDO::FETCH_ASSOC, $all = false) {
        $fetch = $all? 'fetchAll' : 'fetch';
        return $this->tryCatch(fn() => $this->query($sql)->$fetch($fetchStyle));
    }

    protected function getLastInsertedId() {
        return $this->db->lastInsertId();
    }

}
