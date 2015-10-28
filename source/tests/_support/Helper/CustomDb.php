<?php namespace Helper;


use Codeception\Module\Db;

class CustomDb extends Db
{
    public function haveInDatabase($table, array $data, $shouldRemove = true)
    {
        $result = parent::haveInDatabase($table, $data);

        if (!$shouldRemove) {
            array_pop($this->insertedRows);
        }
    }
}