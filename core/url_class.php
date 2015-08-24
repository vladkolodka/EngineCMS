<?php

class URL{
    public static function get($action, $controller = "", $data = array(), $amp=true, $address){
        if($controller) $url = "/$controller/$action";
        else $url = "/$action";

        if($amp) $amp = "&amp;";
        else $amp = "&";

        if(count($data)){
            $url .= '?';

            foreach ($data as $param => $value)
                $url .= "$param=$value$amp";
            $url = substr($url, 0, -strlen($amp));
        }
        return self::getAbsolute($address, $url);
    }
    public static function getAbsolute($address, $url){
        return $address.$url;
    }
    public static function current($address, $amp = false){
        $url = self::getAbsolute($address, $_SERVER["REQUEST_URI"]);
        if($amp) $url = str_replace('&', "&amp;", $url);

        return $url;
    }
    public static function getControllerAndAction(){
        $uri = $_SERVER["REQUEST_URI"];

        $controller_name = "Main";
        $action_name = "index";

        if(strpos($uri, '?') !== false)
            $uri = substr($uri, 0, strpos($uri, '?'));

        $routes = explode('/', $uri);

        if(!empty($routes[2])){
            if(!empty($routes[1]))
                $controller_name = $routes[1];
            $action_name = $routes[2];
        } elseif (!empty($routes[1]))
            $action_name = $routes[1];

        return array($controller_name, $action_name);
    }
    public static function deleteGet($url, $name, $amp = true){
        $url = str_replace("&amp;", '&', $url);

        list($url_path, $url_params) = array_pad(explode('?', $url), 2, '');

        parse_str($url_params, $params);

        unset($params[$name]);

        if(count($params)){
            $url = $url_path . '?' . http_build_query($params);
            if($amp) $url = str_replace('&', "&amp;", $url);
        } else $url = $url_path;
        return $url;
    }
    public static function addGet($url, $name, $value, $amp = true){
        if(strpos($url, '?') === false) $url = $url . '?' . $name . '=' . $value;
        else{
            if($amp) $amp = "&amp;";
            else $amp = '&';

            $url = $url . $amp . $name . '=' . $value;
        }
        return $url;
    }
    public static function addTemplatePage($url, $amp = true){
        return self::addGet($url, "page", '', $amp);
    }
    public static function deletePage($url, $amp = true){
        return self::deleteGet($url, "page", $amp);
    }
    public static function addID($url, $id){
        return $url . '#' . $id;
    }
}