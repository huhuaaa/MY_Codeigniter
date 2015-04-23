<?php
/**
 * 用户类
 */
class user_model extends MY_Model{

	//指定对象拥有的属性值，若不指定那么默认为数据库内所有字段
	protected $_attributes = array('id','username','password', 'salt');
	//指定转array或JSON时，不可见的属性
	protected $_hidden = array('password', 'salt');
	//数据校验规则
	public static $_rules = array(
			array(
				'field'=>'username',
				'label'=>'username',
				'rules'=>'required|is_unique[users.username]|min_length[4]|max_length[20]'
				)
		);
	/**
	 * 对象初始化执行方法
	 */
	protected function initialize(){
		//声明关联关系
		$this->hasMany('id', 'project_model', 'userid');
	}

	/**
	 * 指定数据库表名称
	 * @return string        数据库表名称
	 */
	public static function getSource()
	{
		return 'users';
	}
}