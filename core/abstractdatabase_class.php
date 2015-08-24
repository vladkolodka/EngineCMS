<?php
abstract class AbstractDataBase{
    private $prefix;
    private $sq;
    private $mysqli;

    protected function __construct($db_host, $db_user, $db_password, $db_name, $sq, $prefix){
        $this->sq = $sq;
        $this->prefix = $prefix;

        $this->mysqli = new mysqli($db_host, $db_user, $db_password, $db_name);
        if($this->mysqli->connect_errno) exit("Ошибка соединения с базой данных");
    }
    public function __destruct(){
        if($this->mysqli && !$this->mysqli->connect_errno) $this->mysqli->close();
    }

    public function getQuery(/*AbstractSelect*/ $query, $params){ // заменяет спец. символы в запросе на передаваемые в массиве  @params значения
        if($params){
            $offset = 0; // смещение
            $sq_len = strlen($this->sq);
            foreach ($params as $value) {
                $pos = strpos($query, $this->sq, $offset);

                if(is_null($value)) $arg = "NULL";
                else $arg = $this->mysqli->real_escape_string($value);

                $query = substr_replace($query, $arg, $pos, $sq_len);
                $offset = $pos + strlen($value); // позиция ? + длина нового значения
            }
        }
        return $query;
    }
    public function getTableName($table_name){
        return $this->prefix . $table_name;
    }
    public function getSQ(){
        return $this->sq;
    }

    public function select(AbstractSelect $select){ // получить выборку (ключ = значение)
        $result = $this->getResultSet($select, true, true);
        if(!$result) return false;

        $return_arr = array();

        while($row = $result->fetch_assoc() != false)
            $return_arr[] = $row;

        return $return_arr;
    }
    public function selectRow(AbstractSelect $select){ // выбрать 1 строку
        $result = $this->getResultSet($select, false, true); // 0 нельзя
        if(!$result) return false;

        return $result->fetch_assoc();
    }
    public function selectCol(AbstractSelect $select){ // получить колонку
        $result = $this->getResultSet($select, false, true);
        if(!$result) return false;
        $result_arr = array();

        while($row = $result->fetch_array() != false)
            $result_arr[] = $row[0];

        return $result_arr;
    }
    public function selectCeil(AbstractSelect $select){ // выбор ячейки (поля)
        $result = $this->getResultSet($select, false, true);
        if(!$result) return false;

        return $result->fetch_array()[0];
    }

    public function insert($table_name, $row){
        if(!$row) return false; // если значений нет

        $fields = "(";
        $values = "VALUES(";
        $params = array();

        foreach ($row as $key => $value) {
            $fields .= "$key,";
            $values .= $this->sq . ",";
            $params[] = $value;
        }
        $fields = substr($fields, 0, -1) . ')';
        $values = substr($values, 0, -1) . ')';
        $query = "INSERT INTO " . $this->getTableName($table_name) . "$fields $values";

        return $this->query($query, $params);
    }
    public function update($table_name, $row, $where = false, $params = array()){
        if(!count($row)) return false;
        $query = "UPDATE " . $this->getTableName($table_name) . " SET ";

        $params_add = array();
        foreach ($row as $key => $value) {
            $query .= $key . '=' . $this->sq . ',';
            $params_add[] = $value;
        }
        $query = substr($query, 0, -1);

        if($where){
            $query .= " WHERE $where";
            $params = array_merge($params_add, $params);
        } else $params = $params_add;

        return $this->query($query, $params);
    }
    public function delete($table_name, $where = false, $params = array()){
        $query = "DELETE FROM {$this->getTableName($table_name)}";
        if($where) $query .= " WHERE $where";

        $this->query($query, $params);
    }

    private function query($query, $params){
        $success = $this->mysqli->query($this->getQuery($query, $params));
        if(!$success) return false;
        if($success === 0) return true;
        return $this->mysqli->insert_id;
    }
    private function getResultSet(AbstractSelect $query, $zero, $one){ // получить результат (false, false - не 0 и не 1), принимается готоый запрос
        $result = $this->mysqli->query($query);
        if($result === false) return false;

        if(!$zero && $result->num_rows == 0) return false;
        if(!$one && $result->num_rows == 1) return false;

        return $result;
    }
}