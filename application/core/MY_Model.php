<?php
class MY_Model extends CI_Model{

	//属性配置数组
	protected $_attributes = NULL;
	//example
	/**
	 protected $_attributes = array(
	 	'userid', //属性声明，表示BaseEntity有userid这个属性
	 	'realname'=>'name', //表示BaseEntity有realname这个属性值，并且数据是从数据数组中的name键值对应的值
	 	'username'=>array('username'), //表示BaseEntity有username这个属性值，并且数据是从数据数组中的username键值对应的值
	 	'nickname'=>array('nickname','default_nickname')//表示BaseEntity有nickname这个属性值，并且数据是从数据数组中的nickname键值对应的值，若数据数组中的值为空或者不存在时，给一个default_nickname的默认值。
	 );
	 * 可以借助setData方法实现的代码，来理解属性配置数组的用法。
	 */

	//使用toArray方法时，需要隐藏的属性数组
	protected $_hidden;
	//example
	//protected $_hidden = array('userid');
	
	//主键名称
	protected $_primaryKey = 'id';

	//默认需要隐藏的属性
	protected $_hidden_default = array('_attributes','_hidden','_hidden_default','_primaryKey');

	//验证规则
	public static $_rules = array();
	
	/**
	 * [__construct description]
	 * @param array
	 */
	function __construct(array $data = NULL){
		if(!empty($this->_hidden)){
			$this->_hidden_default = array_merge($this->_hidden, $this->_hidden_default);
		}
		if(!empty($data)){
			$this->setData($data);
		}
	}

	/**
	 * 对象初始化后执行
	 */
	public function initialize(){
		
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
			if(is_null($this->_attributes)){
				foreach ($data as $key => $value) {
					$this->{$key} = $value;
				}
			}
			//定义了属性，那么按照定义的属性来读取
			if(is_array($this->_attributes)){
				foreach ($this->_attributes as $key => $value) {
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
		$this->initialize();
		return $this;
	}

	/**
	 * 设置需要隐藏的属性
	 *@param string|array $keys
	 *@return $this
	 */
	public function addHidden($keys = NULL){
		if(!empty($keys)){
			if(is_array($keys)){
				$this->_hidden_default = array_merge($this->_hidden_default, $keys);
			}
			if(is_string($keys) && array_search($keys, $this->_hidden_default) === FALSE){
				$this->_hidden_default[] = $keys;
			}
		}
		return $this;
	}

	/**
	 * 添加属性
	 *@param string|array $key 属性名称
	 *@param  mixed $value 属性的值
	 *@return $this
	 */
	public function addAttr($key, $value = NULL){
		$this->{$key} = $value;
		return $this;
	}

	/**
	 * 转化为数组类型
	 *@param array $keys
	 *@return array
	 */
	public function toArray(array $keys = NULL){
		$return = array();
		if(!empty($keys)){
			foreach ($this as $key => $value) {
				//排除设定属性
				if(in_array($key, $keys) && !in_array($key, $this->_hidden_default)){
					$return[$key] = $value;
				}
			}
		}else{
			foreach ($this as $key => $value) {
				if(!in_array($key, $this->_hidden_default)){
					$return[$key] = $value;
				}
			}
		}
		return $return;
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
		$this->addHidden('get_'.$class);
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
				$where = array_merge($array, $where);
			}
			return $class::find(array('where'=>$where));
		};
		$this->addHidden('get_'.$class.'s');
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
		$this->addHidden('get_'.$class);
	}

	/**
	 * 返回数据库表名称
	 * @return string 表名称默认为类名称
	 */
	public static function getSource(){
		return strtolower(get_class(new static));
	}

	/**
	 * 创建一个当前类对象
	 *@param array $data
	 *@return object
	 */
	public static function createObject(array $data){
		return new static($data);
	}

	/**
	 * 创建一个当前类对象集合
	 *@param array $datas
	 *@return array 
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
		$CI = & get_instance();
		$table = static::getSource();
		$data = NULL;
		if(is_array($array)){
			$select = isset($array['select']) ? $array['select'] : '*';
			$where = isset($array['where']) ? $array['where'] : NULL;
			$order = isset($array['order']) ? $array['order'] : NULL;
			$CI->db->select($select);
			if(!empty($where)){
				$CI->db->where($where);
			}
			if(!empty($order)){
				$CI->db->order_by($order);
			}
		}
		if(is_string($array) && !empty($array)){
			$CI->db->where($array);
		}
		if(is_int($array)){
			$CI->db->where($object->_primaryKey, $array);
		}
		$data = $CI->db->limit(1,0)->get($table)->row_array();
		return !empty($data) ? static::createObject($data) : NULL;
	}

	/**
	 * 查询并返回一个对象数组
	 * @param  mixed  $array 查询条件
	 * @return array(object) 返回包含多个对象的数组
	 */
	public static function find($array = ''){
		$CI = & get_instance();
		$table = static::getSource();
		if(is_array($array)){
			$select = isset($array['select']) ? $array['select'] : '*';
			$where = isset($array['where']) ? $array['where'] : NULL;
			$order = isset($array['order']) ? $array['order'] : NULL;
			$limit = isset($array['limit']) ? $array['limit'] : NULL;
			$CI->db->select($select);
			if(!empty($where)){
				$CI->db->where($where);
			}
			if(is_array($limit) && isset($limit[0])){
				$CI->db->limit($limit[0],isset($limit[1]) ? $limit[1] : 0);
			}
			if(is_int($limit)){
				$CI->db->limit($limit);
			}
			if(!empty($order)){
				$CI->db->order_by($order);
			}
		}
		if(is_string($array) && !empty($array)){
			$CI->db->where($array);
		}
		$data = $CI->db->get($table)->result_array();
		return static::createObjects($data);
	}

	/**
	 * 获取总数
	 * @param  mixed  $where 筛选条件
	 * @return int           数量
	 */
	public static function count($where = NULL){
		$CI = & get_instance();
		$table = static::getSource();
		if(!empty($where)){
			$CI->db->where($where);
		}
		return $CI->db->from($table)->count_all_results();
	}

	/**
	 * 求和
	 * @param  [type] $field 求和的字段名
	 * @param  [type] $where 求和的条件
	 * @return int           返回结果
	 */
	public static function sum($field, $where = NULL){
		$CI = & get_instance();
		$table = static::getSource();
		if(!empty($where)){
			$CI->db->where($where);
		}
		$array = $CI->db->select_sum($field)->from($table)->get()->row_array();
		return $array[$field];
	}

	/**
	 * 将对象数组转化为JSON字符串
	 * @param  [type] $array 对象数组
	 * @param  [type] $keys  对象需要转化的属性名称数组
	 * @return string        返回JSON序列化的字符串
	 */
	public static function listToJSON($array, $keys = NULL){
		$datas = array();
		foreach ($array as $key => $value) {
			$datas[] = $value->toArray($keys);
		}
		return json_encode($datas);
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
		$CI->validation->setData($data);
		$CI->validation->set_rules(static::$_rules);
		return $CI->validation->run();
	}

	/**
	 * 提取错误信息
	 * @return array 返回错误信息数组
	 */
	public static function validateError(){
		$CI =& get_instance();
		if(!isset($CI->validation)){
			return NULL;
		}else{
			return $CI->validation->messages();
		}
	}

}