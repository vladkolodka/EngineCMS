<?php
abstract class AbstractController{
    protected $view;
    protected $request;
    protected $fp;
    protected $auth_user;
    protected $jsv;
    
    public function __construct($view, $message){
        if(!session_id()) session_start();
        $this->view = $view;
        $this->request = new Request();
        $this->fp = new FormProcessor($this->request, $message);
        $this->auth_user = $this->authUser();

        if(!$this->access()){
            $this->accessDenied();
            throw new Exception("ACCESS_DENIED");
        }
    }
    abstract protected function render($str);
    abstract protected function accessDenied();
    abstract protected function action404();

    protected function authUser(){
        return true;
    }
    protected function notFound(){
        return $this->action404();
    }
    final protected function redirect($url){
        header("Location: $url");
        exit;
    }
    protected function access(){
        return true;
    }
    protected function renderData($modules, $template,  $data){
        if(!is_array($modules)) return false;

        foreach ($modules as $key => $value)
            $data[$key] = $value;

        return $this->view->render($template, $data, true);
    }
}