<?php
abstract class ObjectDB extends AbstractObjectDB{
    private static $months = array("янв", "фев", "март", "апр", "май", "июнь", "июль", "авг", "сен", "окт", "ноя", "дек");

    public function __construct($table){
        parent::__construct($table, Config::FORMAT_DATE);
    }

    protected static function getMonth($date = false){
        if(!$date) $date = time();
        else $date = strtotime($date);

        return self::$months[date('n', $date) - 1];
    }
    public function preEdit($field, $value){
        return true;
    }
    public function postEdit($field, $value){
        return true;
    }
}