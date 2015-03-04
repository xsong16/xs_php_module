<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class SqlFactory extends DBFactory{
    private $_where = '';
    private $_limit = array();
    private $_conClass = '';
    
    private $_error = '';
    private $_is_error = false;
    public function __construct($conClass, $connector = null) {
        parent::__construct($connector);
        $this->_conClass = $conClass;
    }
    
//    public function table($name) {
//        $this->clearCon();
//        $this->_table = $this->escape($name);
//        return $this;
//    }
    
    public function andWhere($arr) {
        return $this->where($arr, 'and');
    }
    
    public function orWhere($arr) {
        return $this->where($arr, 'or');
    }
    
    public function where($arr, $combine = "and") {
        $class = $this->_conClass;
        if (is_array($arr) && !empty($arr)) {
            $where = array();
            foreach ($arr as $key => $value) {
                if ($class::isKeyBelong($key)) {
                    $where[] = $key . $this->analyseWhere($value);
                } else {
                    $this->_error = 'key does not exists';
                    $this->_is_error = true;
                    return $this;
                }
            }
            $this->_where .= $this->_where ? " {$combine} " . '('.implode(" {$combine} ", $where).')' 
                    : '('.implode(" {$combine} ", $where) . ')';
        }
        return $this;
    }
    
    private function analyseWhere($sql) {
        if (empty($sql)) {
            return ' ';
        }
        if (!is_array($sql)) {
            return ' = ' . "'{$this->escape($sql)}' ";
        }
        if (2 <= count($sql)) {
            switch ($sql[1]) {
                case '>' :
                    return ' >' . "'{$this->escape($sql[0])}'";
                case '>=' :
                    return '>=' . "'{$this->escape($sql[0])}'";
                case '<=' :
                    return '<=' . "'{$this->escape($sql[0])}'";
                case '<' :
                    return '<' . "'{$this->escape($sql[0])}'";
                case 'in' : 
                    if (is_array($sql[0])) {
                        foreach ($sql[0] as &$v) {
                            $v = "'" . $this->escape($v) . "'";
                        }
                        return ' in (' . implode(',', $sql[0]) . ')';
                    }
                    break;
                default :
                    return '=' . "'{$this->escape($sql[1])}' ";
            }
        }
    }
    private function clearCon() {
        $this->_limit = array();
        $this->_where = '';
    }
    
    public  function limit($start, $limit = 0) {
        if ($limit) {
            $this->_limit = array(
                (int)$start,
                (int)$limit
            );
        } else {
            $this->_limit = array(
                (int)$start
            );
        }
        return $this;
    }
    
    public function getError() {
        return $this->_error;
    }
    
    public function delete() {
        if ($this->_is_error) {
            $this->_is_error = false;
            $this->clearCon();
            return false;
        }
        $className = $this->_conClass;
        $table = $className::tableName();
        if (!$table) {
            return false;
        }
        $sql = "delete from ".$table." ";
        if (empty($this->_where) && empty($this->_limit)) {
            return false;
        }
        if ($this->_where) {
            $sql .= " where ".$this->_where.' ';
        }
        if (!empty($this->_limit)) {

            if (2 == count($this->_limit)) {
                $sql .= " limit {$this->_limit[0]}, {$this->_limit[1]}";
            } else if (1 == count($this->_limit)) {
                $sql .= " limit {$this->_limit[0]}";
            }
        }
        $this->clearCon();
        return $this->execute($sql);
    }
    
    public  function select() {
        if ($this->_is_error) {
            $this->_is_error = false;
            $this->clearCon();
            return null;
        }
        $className = $this->_conClass;
        $table = $className::tableName();
        if (!$table) {
            return null;
        }
        $sql = "select * from " . $table . " ";
        if ($this->_where) {
            $sql .= ' where ' . $this->_where . ' ';
        }
        if (!empty($this->_limit)) {
            if (2 == count($this->_limit)) {
                $sql .= " limit {$this->_limit[0]}, {$this->_limit[1]}";
            } else if (1 == count($this->_limit)) {
                $sql .= " limit {$this->_limit[0]}";
            }
        }
        $this->clearCon();
        $res = $this->getAllRows($sql);
        if (!empty($res)) {
            $all_ins = array();
            foreach ($res as $value) {
                $ins = new $this->_conClass();
                $ins->load($value);
                $all_ins[$ins->id] = $ins;
            }
            return $all_ins;
        }
        return null;
    }
}