<?php
class MysqliDb {
    private $mysqli;
    private $prefix = '';

    public function __construct($config) {
        $this->mysqli = new mysqli(
            $config['host'],
            $config['username'],
            $config['password'],
            $config['db'],
            $config['port']
        );

        if ($this->mysqli->connect_error) {
            throw new Exception('Connect Error (' . $this->mysqli->connect_errno . ') ' . $this->mysqli->connect_error);
        }

        if (isset($config['charset'])) {
            $this->mysqli->set_charset($config['charset']);
        }
        if (isset($config['prefix'])) {
            $this->prefix = $config['prefix'];
        }
    }

    public function rawQuery($query, $params = null) {
        $stmt = $this->mysqli->prepare($query);
        if (!$stmt) {
            throw new Exception("Error in prepare statement: " . $this->mysqli->error);
        }

        if ($params) {
            $types = '';
            $bindParams = array();
            foreach ($params as $param) {
                if (is_int($param)) {
                    $types .= 'i';
                } elseif (is_float($param)) {
                    $types .= 'd';
                } elseif (is_string($param)) {
                    $types .= 's';
                } else {
                    $types .= 'b';
                }
                $bindParams[] = $param;
            }
            array_unshift($bindParams, $types);
            call_user_func_array(array($stmt, 'bind_param'), $this->refValues($bindParams));
        }

        if (!$stmt->execute()) {
            throw new Exception("Error in execute statement: " . $stmt->error);
        }

        $result = $stmt->get_result();
        if ($result === false && $stmt->errno) {
            throw new Exception("Error in get result: " . $stmt->error);
        }

        if ($result) {
            $data = array();
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
            $result->free();
            return $data;
        }

        return $stmt->affected_rows;
    }

    private function refValues($arr) {
        if (strnatcmp(phpversion(), '5.3') >= 0) {
            $refs = array();
            foreach ($arr as $key => $value) {
                $refs[$key] = &$arr[$key];
            }
            return $refs;
        }
        return $arr;
    }

    public function insert($tableName, $data) {
        $columns = array_keys($data);
        $values = array_values($data);
        $placeholders = array_fill(0, count($values), '?');

        $query = "INSERT INTO " . $this->prefix . $tableName;
        $query .= " (" . implode(',', $columns) . ")";
        $query .= " VALUES (" . implode(',', $placeholders) . ")";

        return $this->rawQuery($query, $values);
    }

    public function get($tableName, $columns = '*', $where = null, $params = null) {
        $query = "SELECT " . (is_array($columns) ? implode(',', $columns) : $columns);
        $query .= " FROM " . $this->prefix . $tableName;
        if ($where) {
            $query .= " WHERE " . $where;
        }

        return $this->rawQuery($query, $params);
    }

    public function update($tableName, $data, $where = null, $params = null) {
        $sets = array();
        $values = array();
        
        foreach ($data as $column => $value) {
            $sets[] = $column . "=?";
            $values[] = $value;
        }

        $query = "UPDATE " . $this->prefix . $tableName;
        $query .= " SET " . implode(',', $sets);
        if ($where) {
            $query .= " WHERE " . $where;
            if ($params) {
                $values = array_merge($values, $params);
            }
        }

        return $this->rawQuery($query, $values);
    }

    public function delete($tableName, $where = null, $params = null) {
        $query = "DELETE FROM " . $this->prefix . $tableName;
        if ($where) {
            $query .= " WHERE " . $where;
        }

        return $this->rawQuery($query, $params);
    }

    public function getLastError() {
        return $this->mysqli->error;
    }

    public function getLastInsertId() {
        return $this->mysqli->insert_id;
    }

    public function escape($str) {
        return $this->mysqli->real_escape_string($str);
    }

    public function close() {
        if ($this->mysqli) {
            $this->mysqli->close();
        }
    }
} 