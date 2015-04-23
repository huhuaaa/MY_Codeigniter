<?php
/**
* 项目类
*/
class project_model extends MY_Model
{
	//指定数据检验规则
	public static $_rules = array(
			array(
				'field'   => 'name', 
				'label'   => 'name', 
				'rules'   => 'required|is_unique[projects.name]|min_length[1]|max_length[20]'
				)
		);

	//初始化声明
	protected function initialize(){
		$this->belongsTo('userid', 'users_model', 'id');
	}

	/**
	 * 指定数据库表名称
	 * @return string        数据库表名称
	 */
	public static function getSource()
	{
		return 'projects';
	}
}