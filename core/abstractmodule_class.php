<?php
abstract class AbstractModule{
    private $properties;
    private $view;
    
    public function __construct($view){
        $this->view = $view;
    }
    final protected function add($name, $default = null, $is_array = false){
        $this->properties[$name]["is_array"] = $is_array;
        if($is_array && $default == "null") $this->properties[$name]["value"] = array();
        else $this->properties[$name]["value"] = $default;
    }
    final public function __get($name){
        if(array_key_exists($name, $this->properties)) return $this->properties[$name]["value"];
        else return null;
    }
    final public function __set($name, $value){
        if(array_key_exists($name, $this->properties)) {
            $this->properties[$name]["value"] = $value;
            return true;
        }
        else return false;
    }
    final protected function addArray(){
        // TODO возможно придется реализовать
    }
    final protected function getParameters(){
        $result = array();
        foreach ($this->properties as $key => $value)
            $result[$key] = $value["value"];

        return $result;
    }
    final protected function getComplexValue($obj, $field){
        if(strpos($field, "->") !== false) $field = explode("->", $field);
        if(is_array($field)){
            $value = $obj;
            foreach ($field as $f)
                $value = $value->$f;
        } else $value = $obj->$field;

        return $value;
    }
    final protected function numberOf($num, $suffix){ // array(стул, стула, стульев)
        $keys = array(2, 0, 1, 1, 1, 2);

        $mod = $num % 100;

        $key = $mod < 20 && $mod > 4 ? 2 : $keys[min(($mod % 10), 5)];

        return $suffix[$key];
    }
    abstract public function getTmplFile();
}