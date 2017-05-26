<?php
namespace App;

use PDO;
use PDOException;

class PDOWrapper extends PDO
{
    /**
     * Execute a function contining SQL statements inside a transaction
     */
    public function transaction(callable $closure)
    {
        $this->beginTransaction();
        try {
            $result = call_user_func($closure, $this);
        } catch (PDOException $exception) {
            $this->rollback();
            throw $exception;
        }
        $this->commit();
        return $result;
    }

    /**
     * Insert using prepared statement.
     * Forwards to insertMulti if Array contains Arrays.
     */
    public function insert($table, $data)
    {
        if (is_array(reset($data))) {
            return $this->insertMulti($table, $data);
        }

        $data = (array)$data;
        $columns = array_keys($data);
        $columns = implode(',', $columns);
        $placeholders = str_repeat('?, ', count($data) - 1).'?';

        $insert = $this->prepare(
            "INSERT INTO {$table}
            ({$columns})
            VALUES ({$placeholders});"
        );
        $values = array_values($data);
        $insert->execute($values);
        return $this::lastInsertId();
    }

    /**
     * Insert multiple rows using prepared statement.
     */
    public function insertMulti($table, $data)
    {
        $first_row = reset($data);
        $columns = array_keys($first_row);
        $columns = implode(',', $columns);
        $row_places = '(' . str_repeat('?, ', count($first_row) - 1) . '?)';

        $values = array();
        $placeholders = array();
        foreach ($data as $row) {
            $values = array_merge($values, array_values($row));
            $placeholders[] = $row_places;
        }

        $placeholders = implode(', ', $placeholders);
        $insert = $this->prepare(
            "INSERT INTO {$table}
            ({$columns})
            VALUES {$placeholders};"
        );
        $insert->execute($values);
    }
}
