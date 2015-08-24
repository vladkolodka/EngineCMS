<?php
class AbstractSelect{
    private $db;
    private $from = "";
    private $where = "";
    private $order = "";
    private $limit = "";

    public function __construct($db){
        $this->db = $db;
    }
    public function __toString(){
        if($this->from)
            return "SELECT " . $this->from . ' ' . $this->where . ' ' . $this->order . ' ' . $this->limit;
        return "";
    }

    public function from($table_name, $fields){
        if($fields == '*')
            $this->from .= '*';
        else{
            foreach ($fields as $field)
                $this->from .= $field . ',';
            $this->from = substr($this->from, 0, -1);
        }
        $this->from .= " FROM " . $this->db->getTableName($table_name);
        //$this->from .= " FROM " . 'eng_' . $table_name;
        return $this;
    }
    public function limit($count, $offset){
        $count = (int) $count;
        $offset = (int) $offset;
        if ($count < 0 || $offset < 0) return false;

        $this->limit = "LIMIT $offset, $count";

        return $this;
    }
    public function order($field, $ask = true){
        $this->order = "ORDER BY ";
        if(is_array($field)){
            if(!is_array($ask))
                $ask = array_fill(0, count($field), $ask);
            $count = count($field);

            for($i = 0; $i < $count; $i++){
                $this->order .= $field[$i];
                if(!$ask[$i]) $this->order .= " DESC";
                $this->order .= ',';
            }
            $this->order = substr($this->order, 0, -1);
        } else{
            $this->order = "ORDER BY $field";
            if(!$ask) $this->order .= " DESC";
        }
        return $this;
    }
    public function rand(){
        $this->order = "ORDER BY RAND()";
    }

    public function where($where, $values = array(), $and = true){
        if($where){
            $where = $this->db->getQuery($where, $values);
            $this->addWhere($where, $and);
        }
        return $this;
    }
    public function whereIn($field, $values, $and = true){
        $where = "$field IN (";

        foreach ($field as $value)
            $where .= $this->db->getSQ() . ',';
        $where = substr($where, 0, -1) . ')';

        $this->where($where, $values, $and);
    }
    private function addWhere($where, $and){
        if($this->where){
            if($and)
                $this->where .= " AND " . $where;
            else
                $this->where .= " OR " . $where;
        } else
            $this->where .= "WHERE $where";
    }
}