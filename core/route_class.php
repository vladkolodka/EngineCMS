<?php
class Route{
    public static function start(){
        $names = URL::getControllerAndAction();

        $controller = $names[0] . "Controller";
        $action = "action" .  $names[1];

        try{
            if(class_exists($controller)) $controller = new $controller;
            if(method_exists($controller, $action)) $controller->$action();
            else throw new Exception();
        } catch(Exception $e){
            if($e->getMessage() != "ACCESS_DENIED") $controller->action404();
        }
    }
}