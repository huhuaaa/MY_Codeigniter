<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Welcome extends MY_Controller {

	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -  
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in 
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see http://codeigniter.com/user_guide/general/urls.html
	 */
	public function index()
	{
		//查找一条主键数据为2的用户，并创建对象
		$user = user_model::findFirst(2);
		//输出用户salt属性
		echo $user->salt;
		//输出转数组
		var_dump($user->toArray());
		//输出转为JSON
		var_dump($user->toJSON());
		//根据声明的关联关系，获取用户的项目数据
		$user_projects = $user->get_project_models();
		//需要新增的用户数据
		$user_data = array('username'=>'1234','salt'=>'1123','password'=>'1231');
		//检验用户数据是否符合
		if(user_model::validate($user_data)){
			$new_user = user_model::create($user_data);
			//输出新增的用户转成array对象
			var_dump($new_user->toArray());
		}else{
			//若不符合要求，那么输出提示信息
			$messages = user_model::validateMessages();
			var_dump($messages);
		}
		//将多个用户项目对象转化为数组
		var_dump(user_model::listToArray($user_projects));

		//$this->load->view('welcome_message');
	}
}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */