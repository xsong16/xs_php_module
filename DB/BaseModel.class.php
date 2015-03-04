<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

abstract class BaseModel{
    
    private $_init_set = array();
    private static $_model = array();
    
    //put your code here
    public function __construct() {
        $this->setDefaultParam();
    }
    
    protected function setDefaultParam() {
        $default_param = $this->defaultSet();
        if (is_array($default_param) && !empty($default_param)) {
            foreach ($default_param as $key => $value) {
                $this->$key = $value;
            }
        }
    }

    abstract public static function tableLine();
    abstract public static function tableName();
    
    public function getKeys() {
        return array_keys($this->_init_set);
    }
    
    public static  function isKeyBelong($key) {
        $keys = static::tableLine();
        return in_array($key, $keys);
    }
    
    public function __set($name, $value) {
        $keys = $this->tableLine();
        if (in_array($name, $keys)) {
            $this->_init_set[$name] = $value;
        }
    }
    
    public function __get($name) {
        if (isset($this->_init_set[$name])) {
            return $this->_init_set[$name];
        }
        return null;
    }

    public static function delete($id) {
        return self::model()->where(array('id' => $id))->delete();
    }
    
    public static function findOne($id) {
        $res = self::model()->where(array('id' => $id))->limit(1)->select();
        if (is_array($res) && !empty($res)) {
            reset($res);
            return current($res);
        }
        return null;
    }
    
    public static function model() {
        if (!self::$_model[get_called_class()]) {
            self::$_model[get_called_class()] = new SqlFactory(get_called_class());
        }
        return self::$_model[get_called_class()];
    }
    
    public function save() {
        $class_name = get_called_class();
        $table_name = $class_name::tableName();
        $id = $this->escape($this->id);
        if ($id) {
            $sets = array();
            foreach ($this->getKeys() as $key) {
                if ('id' != $key) {
                    $sets[] = '`' . $key . '`' . "='" . $this->escape($this->$key) . "'";
                }
            }
            $sql = "update ".$table_name." set ".implode(',', $sets)." where id = '".$id."'";
            return $this->execute($sql);
        } else {
            $sets = '`'.implode('`,`', $this->getKeys()).'`';
            $values = array();
            foreach ($this->getKeys() as $key) {
                if ('id' != $key) {
                    $values[] = "'" . $this->escape($this->$key) . "'";
                }
            }
            $value = implode(",", $values);
            $sql = "insert into " . $table_name . " (".$sets.") values (".$value.")";
            if (!$this->execute($sql)) {
                return false;
            }
            return $this->getInsertId();
        }
    }
//    public function getArray() {
//        if (!empty($this->_init_set)) {
//            return $this->_init_set;
//        }
//        return null;
//    }
}
