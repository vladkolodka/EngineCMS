<?php
class View{
    private $dir_name; // tpl files dir

    public function __construct($dir){
        $this->dir_name = $dir;
    }

    public function render($template_name, $params, $return = false){
        $template = $this->dir_name . $template_name;
        extract($params);
        ob_start();
        include $template;
        if($return) return ob_get_clean();
        echo ob_get_clean();
        return true;
    }
}