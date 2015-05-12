<?php
class MY_Model extends CI_Model{

	//使用toArray方法时，需要隐藏的属性数组
	protected $_hidden = array();
	//example
	//protected $_hidden = array('userid');

	//默认需要隐藏的属性，这个不需要重写
	protected $_hidden_default = array('_hidden','_hidden_default');

	//属性配置数组
	public static $_attributes = NULL;
	//example
	/**
	 public static $_attributes = array(
	 	'userid', //属性声明，表示BaseEntity有userid这个属性
	 	'realname'=>'name', //表示BaseEntity有realname这个属性值，并且数据是从数据数组中的name键值对应的值
	 	'username'=>array('username'), //表示BaseEntity有username这个属性值，并且数据是从数据数组中的username键值对应的值
	 	'nickname'=>array('nickname','default_nickname')//表示BaseEntity有nickname这个属性值，并且数据是从数据数组中的nickname键值对应的值，若数据数组中的值为空或者不存在时，给一个default_nickname的默认值。
	 );
	 * 可以借助setData方法实现的代码，来理解属性配置数组的用法。
	 */

	//主键名称
	public static $_primaryKey = 'id';

	//验证规则
	public static $_rules = array();
	
	/**
	 * [__construct description]
	 * @param array
	 */
	function __construct(array $data = NULL){
		$this->_hidden = array_merge($this->_hidden, $this->_hidden_default);
		if(!empty($data)){
			$this->setData($data);
		}
		//执行初始化方法
		$this->initialize();
	}

	/**
	 * 对象初始化后执行
	 */
	protected function initialize(){
		
	}

	/**
	 * 对象方法调用重写，允许调用注册的对象方法
	 * @param  string $method 方法名
	 * @param  array $args   参数数组
	 * @return void
	 */
	public function __call($method, $args){
		if(isset($this->$method)){
			return call_user_func_array($this->$method, $args);
		}else{
			throw new Exception('Call to undefined method '.get_class($this).'::'.$method.'()', 0);
		}
	}

	/**
	 * 设置数据
	 * @param  array $data
	 */
	public function setData(array $data){
		if(is_array($data)){
			//未声明属性，那么默认取出所有数据库中的字段作为属性
			if(is_null(static::$_attributes)){
				foreach ($data as $key => $value) {
					$this->{$key} = $value;
				}
			}
			//定义了属性，那么按照定义的属性来读取
			if(is_array(static::$_attributes)){
				foreach (static::$_attributes as $key => $value) {
					//同名属性
					if(is_int($key)){
						$this->{$value} = isset($data[$value]) ? $data[$value] : NULL;
					}else{
						if(is_array($value)){
							if(isset($value[1])){
								$this->{$key} = isset($data[$value[0]]) ? $data[$value[0]] : $value[1];
							}else{
								$this->{$key} = isset($data[$value[0]]) ? $data[$value[0]] : NULL;
							}
						}else{
							$this->{$key} = isset($data[$value]) ? $data[$value] : NULL;
						} 
					}
				}
			}
		}
		return $this;
	}

	/**
	 * 添加默认隐藏属性
	 * @param string|array $keys
	 * @return  $this
	 */
	public function addHiddenDefault($keys){
		if(!empty($keys)){
			if(is_array($keys)){
				$this->_hidden_default = array_merge($this->_hidden_default, $keys);
			}
			if(is_string($keys) && !in_array($keys, $this->_hidden_default)){
				$this->_hidden_default[] = $keys;
			}
		}
		return $this->addHidden($keys);
	}

	/**
	 * 设置需要隐藏的属性
	 * @param string|array $keys
	 * @return $this
	 */
	public function addHidden($keys){
		if(is_array($keys)){
			$this->_hidden = array_merge($this->_hidden, $keys);
		}
		if(is_string($keys) && !in_array($keys, $this->_hidden)){
			$this->_hidden[] = $keys;
		}
		return $this;
	}

	/**
	 * 添加属性
	 * @param string|array $key 属性名称
	 * @param  mixed $value 属性的值
	 * @return $this
	 */
	public function addAttr($key, $value = NULL){
		$this->{$key} = $value;
		return $this;
	}

	/**
	 * 转化为数组类型
	 * @param array $keys
	 * @return array
	 */
	public function toArray(array $keys = NULL){
		$return = array();
		if(!empty($keys)){
			foreach ($this as $key => $value) {
				//排除设定属性
				if(in_array($key, $keys) && !in_array($key, $this->_hidden)){
					$return[$key] = $value;
				}
			}
		}else{
			foreach ($this as $key => $value) {
				if(!in_array($key, $this->_hidden)){
					$return[$key] = $value;
				}
			}
		}
		return $return;
	}

	/**
	 * 将所有属性转化为数组，数组内的所有值只能是string或者int，用于数据库更新
	 * @return array 返回转化后的数组
	 */
	protected function allArray(array $keys = NULL){
		$return = array();
		if(is_null($keys)){
			foreach ($this as $key => $value) {
				if(!in_array($key, $this->_hidden_default)){
					//值为对象时需要转化为字符串
					if(is_object($value) || is_array($value)){
						$return[$key] = json_encode($value);
					}else{
						$return[$key] = $value;
					}
				}
			}
		}
		if(is_array($keys)){
			foreach ($this as $key => $value) {
				if(in_array($key, $keys) && !in_array($key, $this->_hidden_default)){
					//值为对象时需要转化为字符串
					if(is_object($value) || is_array($value)){
						$return[$key] = json_encode($value);
					}else{
						$return[$key] = $value;
					}
				}
			}
		}
		return $return;
	}

	/**
	 * 验证当前模型对象是否符合校验规则
	 * @return boolean
	 */
	public function validateSelf(){
		return static::validate($this->allArray());
	}

	/**
	 * 将对象转化为JSON格式的字符串数据
	 * @param  array|null $keys 需要转化的键值
	 * @return string           JSON格式的字符串
	 */
	public function toJSON(array $keys = NULL){
		return json_encode($this->toArray($keys));
	}

	/**
	 * 1-1的外键关联方法
	 * @param  string  $foreignKey 外键字段
	 * @param  string  $class      类名称
	 * @param  string  $field      关联字段
	 * @return void
	 */
	public function hasOne($foreignKey, $class, $field){
		$this->{'get_'.$class} = function() use($foreignKey, $class, $field){
			$where = array($field=>$this->{$foreignKey});
			return $class::findFirst(array('where'=>$where));
		};
		$this->addHiddenDefault('get_'.$class);
	}

	/**
	 * 1-n的外键关联方法，注册获取多个关联对象的方法。
	 * @param  string  $foreignKey 外键字段
	 * @param  string  $class      类名称
	 * @param  string  $field 	   关联字段
	 * @return void 			   
	 */
	public function hasMany($foreignKey, $class, $field){
		$this->{'get_'.$class.'s'} = function($array = NULL) use($foreignKey, $class, $field){
			$where = array($field=>$this->{$foreignKey});
			if(!empty($array)){
				$array['where'] = isset($array['where']) ? array_merge($array['where'], $where) : $where;
			}else{
				$array = array('where'=>$where);
			}
			return $class::find($array);
		};
		$this->addHiddenDefault('get_'.$class.'s');
	}

	/**
	 * n-1的外键关联方法，注册获取外键归属对象的方法
	 * @param  string $field      字段名称
	 * @param  string $class      类名称
	 * @param  string $foreignKey 外键字段
	 * @return void
	 */
	public function belongsTo($field, $class, $foreignKey){
		$this->{'get_'.$class} = function() use($field, $class, $foreignKey){
			$where = array($foreignKey=>$this->{$field});
			return $class::findFirst(array('where'=>$where));
		};
		$this->addHiddenDefault('get_'.$class);
	}

	/**
	 * 将对象的值保存到数据库中，建议传递$keys
	 * @param array|null $keys 需要保存修改到数据库的属性值
	 * @return boolean
	 */
	public function save(array $keys = NULL){
		$primaryKey = static::$_primaryKey;
		$db = static::query();
		$db->where($primaryKey, $this->{$primaryKey})->update(static::getSource(), $this->allArray($keys));
		return $db->affected_rows();
	}


	/**
	 * 设置对象属性并保存到数据库
	 * @param  array  $data 修改的数据
	 * @return boolean
	 */
	public function saveData(array $data){
		$this->setData($data);
		$keys = array();
		foreach ($data as $key => $value) {
			$keys[] = $key;
		}
		return $this->save($keys);
	}

	/**
	 * 摧毁对象(删除数据库中的数据)
	 * @return boolean
	 */
	public function destroy(){
		$db = static::query();
		$db->delete(static::getSource(), array(static::$_primaryKey=>$this->{static::$_primaryKey}));
		return $db->affected_rows();
	}

	/**
	 * 返回数据库表名称
	 * @return string 表名称默认为类名称
	 */
	public static function getSource(){
		return strtolower(get_class(new static));
	}

	/**
	 * 获取ci的db对象，并且已经设置对应的表
	 * @return $CI->db
	 */
	public static function query(){
		$CI =& get_instance();
		if(!isset($CI->db)){
			$CI->load->database();
		}
		return $CI->db->from(static::getSource());
	}

	/**
	 * 创建一个当前类对象
	 * @param array $data
	 * @return object
	 */
	public static function createObject(array $data){
		return new static($data);
	}

	/**
	 * 创建一个当前类对象集合
	 * @param array $datas
	 * @return array 
	 */
	public static function createObjects(array $datas){
		$objects = array();
		foreach ($datas as $value) {
			$objects[] = static::createObject($value);
		}
		return $objects;
	}

	/**
	 * 查询并返回一个数据对象
	 * @param  mixed  $array 查询条件
	 * @return object        返回数据模型对象
	 */
	public static function findFirst($array = NULL){
		$db = static::query();
		$data = NULL;
		if(is_array($array)){
			$select = isset($array['select']) ? $array['select'] : '*';
			$where = isset($array['where']) ? $array['where'] : NULL;
			$or_where = isset($array['or_where']) ? $array['or_where'] : NULL;
			$order = isset($array['order']) ? $array['order'] : NULL;
			$db->select($select);
			if(!empty($where)){
				$db->where($where);
			}
			if(!empty($or_where)){
				$db->or_where($or_where);
			}
			if(!empty($order)){
				$db->order_by($order);
			}
		}
		if(is_string($array) && !empty($array)){
			$db->where($array);
		}
		if(is_int($array)){
			$db->where(static::$_primaryKey, $array);
		}
		$data = $db->limit(1,0)->get()->row_array();
		return !empty($data) ? static::createObject($data) : NULL;
	}

	/**
	 * 查询并返回一个对象数组
	 * @param  mixed  $array 查询条件
	 * @return array(object) 返回包含多个对象的数组
	 */
	public static function find($array = NULL){
		$db = static::query();
		if(is_array($array)){
			$select = isset($array['select']) ? $array['select'] : '*';
			$where = isset($array['where']) ? $array['where'] : NULL;
			$or_where = isset($array['or_where']) ? $array['or_where'] : NULL;
			$order = isset($array['order']) ? $array['order'] : NULL;
			$limit = isset($array['limit']) ? $array['limit'] : NULL;
			$db->select($select);
			if(!empty($where)){
				$db->where($where);
			}
			if(!empty($or_where)){
				$db->or_where($or_where);
			}
			if(is_int($limit)){
				$db->limit($limit > 0 ? $limit : 0);
			} else if (is_array($limit) && isset($limit[0])){
				$db->limit($limit[0] > 0 ? intval($limit[0]) : 0,isset($limit[1]) && $limit[1] > 0 ? intval($limit[1]) : 0);
			}
			if(!empty($order)){
				$db->order_by($order);
			}
		}
		if(is_string($array) && !empty($array)){
			$db->where($array);
		}
		$data = $db->get()->result_array();
		return static::createObjects($data);
	}

	/**
	 * 删除
	 * @param  [type] $id [description]
	 * @return int        影响行数
	 */
	public static function remove($where){
		$db = static::query();
		if(is_int($where)){
			$db->delete(static::getSource(), array($this->_primaryKey=>$id));
			return $db->affected_rows();
		} else if(is_string($where) or is_array($where)){
			$db->delete(static::getSource(), $where);
			return $db->affected_rows();
		}
		return 0;
	}

	/**
	 * 获取总数
	 * @param  mixed  $where 筛选条件
	 * @return int           数量
	 */
	public static function count($where = NULL){
		$db = static::query();
		if(!empty($where)){
			$CI->db->where($where);
		}
		return $db->count_all_results();
	}

	/**
	 * 求和
	 * @param  [type] $field 求和的字段名
	 * @param  [type] $where 求和的条件
	 * @return int           返回结果
	 */
	public static function sum($field, $where = NULL){
		$db = static::query();
		if(!empty($where)){
			$CI->db->where($where);
		}
		$array = $db->select_sum($field)->get()->row_array();
		return $array[$field];
	}

	/**
	 * 创建一个新对象，并将数据添加到数据库
	 * @param  [type] $data [description]
	 * @return [type]       [description]
	 */
	public static function create($data){
		$db = static::query();
		$db->insert(static::getSource(), $data);
		$insert_id = $db->insert_id();
		return static::findFirst($insert_id);
	}

	/**
	 * 将对象数组转化为JSON字符串
	 * @param  [type] $array 对象数组
	 * @param  [type] $keys  对象需要转化的属性名称数组
	 * @return string        返回JSON序列化的字符串
	 */
	public static function listToArray($array, $keys = NULL){
		$datas = array();
		foreach ($array as $key => $value) {
			$datas[] = $value->toArray($keys);
		}
		return $datas;
	}

	/**
	 * 将对象数组转化为JSON字符串
	 * @param  [type] $array 对象数组
	 * @param  [type] $keys  对象需要转化的属性名称数组
	 * @return string        返回JSON序列化的字符串
	 */
	public static function listToJSON($array, $keys = NULL){
		return json_encode(static::listToArray($array, $keys));
	}

	/**
	 * 验证数据是否符合要求
	 * @param  array  $data 数据数组
	 * @return boolean      数据验证是否通过
	 */
	public static function validate(array $data){
		$CI =& get_instance();
		if(!isset($CI->validation)){
			$CI->load->library('validation');
		}
		$CI->validation->setData($data, static::$_primaryKey);
		$CI->validation->set_rules(static::$_rules);
		return $CI->validation->run();
	}

	/**
	 * 提取错误信息
	 * @return array 返回错误信息数组
	 */
	public static function validateMessages(){
		$CI =& get_instance();
		if(!isset($CI->validation)){
			return NULL;
		}else{
			return $CI->validation->messages();
		}
	}

}