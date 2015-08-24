<?php

class FormProcessor {
    private $request;
    private $message;

    public function __construct($request, $message) {
        $this->request = $request;
        $this->message = $message;
    }

    public function process($message_name, $obj, $fields, $checks = array(), $success_message = false) {
        try{
            if(is_null($this->checks($message_name, $checks))) return null;

            foreach ($fields as $field) {
                if (is_array($field)) {
                    $f = $field[0];
                    $v = $field[1];

                    if (strpos($f, "()") !== false) { // если f - функция
                        $f = str_replace("()", '', $f);
                        $obj->$f($v);
                    } else $obj->$f = $v;

                } else $obj->$field = $this->request->$field;
            }


            if($obj->save()){
                if($success_message)
                    $this->setSessionMessage($message_name, $success_message);
                return $obj;
            }
            return true;
        } catch(Exception $e){
            $this->setSessionMessage($message_name, $this->getError($e));
            return null;
        }
    }
    public function checks($message_name, $checks){
        try{
            foreach ($checks as $check) {
                $equal = isset($check[3]) ? $check[3] : true;

                if($equal && $check[0] != $check[1]) throw new Exception($check[2]);
                else if(!$equal && $check[0] == $check[1]) throw new Exception($check[2]);
            }
        } catch (Exception $e){
            $this->setSessionMessage($message_name, $this->getError($e));
            return null;
        }
    }

    private function getError($e){
        if($e instanceof ValidatorException){
            return $e->getErrors();
        } elseif ($message = $e->getMessage())
            return $message;
        return "UNKNOWN_ERROR";
    }
    public function setSessionMessage($message_name, $text){
        if(!session_id()) session_start();
        $_SESSION["message"] = array($message_name => $text);
    }
    public function getSessionMessage($message_name){
        if(!session_id()) session_start();
        if(!empty($_SESSION["message"][$message_name])) {
            unset($_SESSION["message"]);
            return $_SESSION["message"][$message_name];
        }
        return false;
    }
    public function uploadIMG($message_name, $file, $max_size, $dir, $source_name = false){
        try{
            $name = File::uploadIMG($file, $max_size, $dir, false, $source_name);
            return $name;
        } catch (Exception $e){
            $this->setSessionMessage($message_name, $this->getError($e));
            return null;
        }
    }
}