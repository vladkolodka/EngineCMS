<?php
abstract class Validator{
    const CODE_UNKNOWN = "UNKNOWN_ERROR";

    protected $date;
    protected $errors;

    public function __construct($data){
        $this->date = $data;
        $this->validate();
    }
    abstract protected function validate();

    public function getErrors(){
        return $this->errors;
    }
    public function isValid(){
        return count($this->errors) == 0;
    }
    public function setError($err_text){
        $this->errors[] = $err_text;
    }
    protected function isContainQuotes($str){
        $symbols = array('/', "'", '`', "%qout;", "&apos;");

        foreach ($symbols as $symbol)
            if(strpos($str, $symbol) !== false) return true;

        return false;
    }
}