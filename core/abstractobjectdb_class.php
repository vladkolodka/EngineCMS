<?php
abstract class AbstractObjectDB{
	const TYPE_TIMESTAMP = 1;
	const TYPE_IP = 2;

	private static $types = array(self::TYPE_TIMESTAMP, self::TYPE_IP);
	protected static $db = NULL;

	private $format_date;
	protected $table_name;
	private $id = NULL;
	private $properties = array();

	public function __construct($table_name, $format_date){
		$this->format_date = $format_date;
		$this->table_name = $table_name;
	}
	public function __get($name){
		if($name == 'id') return $this->getID();

		return array_key_exists($name, $this->properties) ? $this->properties[$name]['value'] : NULL;
	}
	public function __set($name, $value){
		if(array_key_exists($name, $this->properties))
			$this->properties[$name]['value'] = $value;
		else $this->$name = $value;
	}

	public static function setDB($db){
		self::$db = $db;
	}

	public function load($id){ // загрузка по id TODO вопрос как изменить данные без загрузки, по ID
		$id = (int) $id;
		if($id < 0) return false;

		$select = new Select();
		$select->from($this->table_name, $this->getSelectFields())
			   ->where("id=" . self::$db->getSQ(), array($id));
		$row = self::$db->selectRow($select);

		if(!$row) return false;

		if($this->init($row)) return $this->postLoad();
		return false; // для IDE
	}
	public function loadFromField($field, $value){
		$select = new Select();
		$select->from($this->table_name, '*')->where("$field=" . self::$db->getSQ(), array($value));

		$row = self::$db->selectRow($select);
		if($row)
			if($this->init($row)) $this->postLoad();
		return false;
	}
	public function init($row){ // присвоить результат объекту
		foreach ($this->properties as $key => $value) {
			$val = $row[$key];
			switch($value['type']){
				case self::TYPE_TIMESTAMP:
					if(!is_null($val)) $val = strftime($this->format_date, $val);
					break;
				case self::TYPE_IP:
					if(!is_null($val)) $val = long2ip($val);
					break;
			}
			$this->properties[$key]['value'] = $val;
		}
		$this->id = $row['id'];
		$this->postInit();
	}
	public function save(){ // добавить / обновить в базу данных
		$update = $this->isSaved();
		if($update) $commit = $this->preUpdate();
		else $commit = $this->preInsert();
		if(!$commit) return false;

		$row = array();
		foreach ($this->properties as $key => $value) {
			switch($value['type']){
				case self::TYPE_TIMESTAMP:
					if(!is_null($value['value'])) $value['value'] = strtotime($value['value']);
					break;
				case self::TYPE_IP:
					if(!is_null($value['value'])) $value['value'] = ip2long($value['value']);
					break;
			}
			$row[$key] = $value['value'];
		}
		if(count($row) > 0){
			if($update){
				$success = self::$db->update($this->table_name, $row, 'id = ' . self::$db->getSQ(), array($this->getID()));
				if(!$success) throw new Exception();
			} else{
				$this->id = self::$db->insert($this->table_name, $row);
				if(!$this->getID()) throw new Exception();
			}
		}
		if($update) return $this->postUpdate();
		return $this->postInsert();
	}
	public function delete(){
		if(!$this->isSaved()) return false;
		if(!$this->preUpdate()) return false;

		$success = self::$db->delete($this->table_name, 'id = ' . self::$db->getSQ(), array($this->getID()));
		if(!$success) throw new Exception();

		$this->id = NULL;
		return $this->postDelete();
	}

	public function isSaved(){
		return $this->getID() > 0;
	}
	public function getID(){
		return (int) $this->id;
	}

	public static function buildMultiple($class, $data){
		$result = array();

		if(!class_exists($class)) throw new Exception();

		if(!new $class instanceof AbstractObjectDB) throw new Exception();

		foreach ($data as $row) {
			$obj = new $class();
			$obj->init($row);
			$result[$obj->getID()] = $obj;
		}
		return $result;
	}

	public static function getAll($count = false, $offset = false){
		$class = get_called_class();
		return self::getAllWidthOrder($class::$table, $class, 'id', true, $count, $offset);
	}
	public static function getAllOnField($table_name, $class, $field, $value, $order = false, $ask = true, $count = false, $offset = false){
		return self::getAllOnWhere($table_name, $class, "$field=" . self::$db->getSQ(), array($value), $order, $ask, $count, $offset);
	}

	protected static function getAllWidthOrder($table_name, $class, $order = false, $ask = true, $count = false, $offset = false){
		return self::getAllOnWhere($table_name, $class, false, false, $order, $ask, $count, $offset);
	}
	protected static function getAllOnWhere($table_name, $class, $where = false, $values = false, $order = false, $ask = true, $count = false, $offset = false){
		$select = new Select();
		$select->from($table_name, '*');

		if($where) $select->where($where, $values);
		if($order) $select->order($order, $ask);
		else $select->order('id');
		if($count) $select->limit($count, $offset);

		return AbstractObjectDB::buildMultiple($class, self::$db->select($select)); // TODO попробовать заменить на self::
	}

	public static function getCount(){
		$class = get_called_class();
		self::getCountOnWhere($class::$table, false, false);
	}

	protected static function getCountOnField($table_name, $field, $value){
		return self::getCountOnWhere($table_name, "$field=" . self::$db->getSQ(), array($value));
	}
	protected static function getCountOnWhere($table_name, $where = false, $values = false){
		$select = new Select();

		$select->from($table_name, "COUNT(id)");
		if($where) $select->where($where, $values);

		return  self::$db->selectCeil($select);
	}

	protected static function addSubObject($data, $class, $field_in, $field_out){ // data - массив объектов
		$ids = array();

		foreach ($data as $object)
			$ids[] = self::getComplexValue($object, $field_in);

		if(!$ids) return array();

		$new_data = $class::getAllOnIDs($ids);
		if(!count($new_data)) return $data;

		foreach ($data as $id => $object) {
			$in = self::getComplexValue($object, $field_in);
			if(isset($new_data[$in])) $data[$id]->$field_out = $new_data[$in];
		}
		return $data;
	}
	protected static function getComplexValue($obj, $field){
		if(strpos($field, '->') !== false){
			$fields = explode('->', $field);
			$value = $obj;
			foreach ($fields as $f)
				$value = $value->$f;

		} else $value = $obj->$field;
		return $value;
	}

	public static function getAllOnIDs($ids){
		return self::getAllIDsField($ids, 'id');
	}
	public static function getAllIDsField($ids, $field){
		$class = get_called_class();

		$select = new Select();
		$select->from($class::$table, '*');
		$select->whereIn($field, $ids);

		return AbstractObjectDB::buildMultiple($class, self::$db->select($select)); // TODO првоерить self
	}

	protected function getIp(){
		return $_SERVER["REMOTE_ADDR"];
	}
	public static function hash($str, $secret = ""){
		return md5($str . $secret);
	}
	protected function getKey(){
		return md5(uniqid("", true) . mt_rand(0, 1000));
	}
	private function getSelectFields(){
		return array_merge('id', array_keys($this->properties));
	}
	public function getDate($date = false){
		if(!$date) $date = time();
		return strtotime($this->format_date, $date);
	}
	protected function getDay($date = false){
		if(!$date) $date = time();
		return date('d', $date);
	}

	final protected function add($field, $value, $validator, $type){ // TODO добавил final, проверить
		$this->properties[$field] = array('value' => $value, 'validator' => $validator, in_array(self::$types, $type) ? $type : null);
	}

	protected function preInsert(){
		return $this->validate();
	}
	protected function postInsert(){
		return true;
	}
	protected function preUpdate(){
		return $this->validate();
	}
	protected function postUpdate(){
		return true;
	}
	protected function preDelete(){
		return true;
	}
	protected function postDelete(){
		return true;
	}
	protected function postInit(){
		return true;
	}
	protected function preValidate(){
		return true;
	}
	protected function postValidate(){
		return true;
	}
	protected function postLoad(){
		return true;
	}

	private function validate(){
		if(!$this->preValidate()) return false;
		$validators = array();

		foreach ($this->properties as $key => $value) {
			$validators[$key] = new $value['validator']($value['value']);
		}

		$errors = array();
		foreach ($validators as $validator) {
			if(!$validator->isValid()) $errors[$key] = $validator->getErrors();
		}

		if(count($errors) == 0){ // если все данные корректны
			if(!$this->postValidate()) throw new Exception();
			return true;
		}
		else throw new ValidateException($errors);
	}

	// TODO методы для поиска
	// TODO почему используется $this->table_name если есть $class::$table
}