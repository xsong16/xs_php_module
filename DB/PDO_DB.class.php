<?php
class PDO_DB {
    static private $db_holder = array();
    private $_ins = null;
    private $_stmt = null;

    public  function  __construct($conn) {
        $this->_ins = self::_getDB($conn);
    }

    private function _getDB($conn) {
        $con_str = $config[$conn] ? $config[$conn] : null;
        if (!$con_str) {
            return null;
        }
        if (!isset(self::$ins[$conn])) {
            self::$ins[$conn] = new PDO($conn, array(PDO::ATTR_PERSISTENT => true));
        }
        return isset(self::$ins[$conn]) ? self::$ins[$conn] : null;
    }

    public function prepare($sql) {
        if ($this->_ins) {
            $this->_stmt = $this->_ins->prepare($sql);
            return true;
        }
        return false;
    }

    public function execute() {
        if ($this->_ins && $this->_stmt) {
            return $this->_ins->execute();
        }
        return false;
    }

    public function bindValue($param) {
        if ($this->_stmt && is_array($param) && !empty($param)) {
            foreach ($param as $name => $value) {
                $this->_stmt->bindValue(':'.$name, $value);
            }
        }
    }

    public function fetch() {
        if ($this->_stmt) {
            return $this->_stmt->fetch(PDO::FETCH_ASSOC);
        }
        return false;
    }

    public function fetchAll() {
        if ($this->_stmt) {
            return $this->_stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        return false;
    }

    public function rowCount() {
        if ($this->_stmt) {
            return $this->_stmt->rowCount();
        }
        return false;
    }

    public function lastInsertId() {
        if ($this->_ins) {
            return $this->_ins->lastInsertId();
        }
        return false;
    }
    
    public function beginTransaction() {
        if ($this->_ins) {
            return $this->_ins->beginTransaction();
        }
        return false;
    }

    public function commit() {
        if ($this->_ins) {
            return $this->_ins->commit();
        }
        return false;
    }

    public function rollBack() {
        if ($this->_ins) {
            return $this->_ins->rollBack();
        }
        return false;
    }
}
