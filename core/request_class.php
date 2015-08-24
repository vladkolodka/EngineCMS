<?php

class Request{
    private $data;

    public function __construct(){
        $this->data = $this->xss($_REQUEST);
    }
    public function __get($elem){
        if(isset($this->data[$elem])) return $this->data[$elem];
        return false;
    }
    private function xss($data){
        if(is_array($data)){
            $cleared = array();

            foreach ($data as $key => $value)
                $cleared[$key] = $this->xss($value);

            return $cleared;
        }
        else return trim(htmlspecialchars($data));
    }
}