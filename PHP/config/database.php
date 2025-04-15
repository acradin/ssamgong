<?php
require_once __DIR__ . '/MysqliDb.php';

class Database {
    private static $instance = null;
    private $db = null;

    private function __construct() {
        $this->db = new MysqliDb(array(
            'host' => 'localhost',
            'username' => 'chatbot_test',
            'password' => '1234',
            'db' => 'chatbot_test',
            'port' => 3306,
            'prefix' => '',
            'charset' => 'utf8mb4'
        ));
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->db;
    }
} 