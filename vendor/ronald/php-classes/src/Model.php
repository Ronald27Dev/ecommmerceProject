<?php

namespace Ronald;

class Model {

    protected $values = [];

    public function __call($name, $arguments) {
        $method = substr($name, 0, 3);
        $fieldName = substr($name, 3);

        // Handle the specific cases
        if ($method === 'get') {

            if ($fieldName === 'idcart') {
                
                return isset($this->values[$fieldName]) ? $this->values[$fieldName] : 0;

            } elseif ($fieldName === 'deszipcode' || $fieldName === 'vlfreight' || $fieldName === 'nrdays') {
                
                return isset($this->values[$fieldName]) ? $this->values[$fieldName] : null;
            }

            return $this->values[$fieldName];
        } elseif ($method === 'set') {
            
            $this->values[$fieldName] = $arguments[0];
        }
    }

    public function setData($data = array()) {
        foreach ($data as $key => $value) {
            $this->{"set" . $key}($value);
        }
    }

    public function getValues() {
        return $this->values;
    }
}