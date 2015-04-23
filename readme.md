## MY_Codeigniter

	MY_Codeigniter为PHP Codeigniter框架2.2.0版本的一个自定义分支版本。

## 调整CI框架原因

CI自带的Model功能较弱，且没有将model转化为一个对象来进行使用。那么为了解决以上弊端，在参考了其他一些PHP框架的Model功能后，我在CI基础上进行了一些调整。详情看后续。

## 1、建立面向对象的Model基类MY_Model

面向对象的基类MY_Model，用于将数据库数据直接转化为对应Model类型的对象。那么在建立各个Model时，可以声明相关关联关系、以及对应字段属性的类型限制验证等。


## 2、将入口文件移动，解放代码安全策略

将站点入口index.php转移到web/index.php，那么在配置网站根目录是就直接指向web目录。这样使的代码会目录不在web访问范围内，解放web服务器采用配置限制目录访问。也可以省去php头文件上的那句if ( ! defined('BASEPATH')) exit('No direct script access allowed');。

## 3、引进自动加载类方法

添加自动加载model类的方法，解放以前使用一个model时，总是要先添加一句$this->load->model('user_model')用以确保已引入这个类型。当然这里会添加一些约束，model类名称必须以_model结尾，这点已经在以前的项目中都这么约束了。而library则不能出现于model一样的结尾。由于helper都是一些方法，那么helper的调用加继续采用CI的加载方法。当然原有CI的load功能同样都可以使用。

## 代码示例

user_model代码：
	
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
					),
				array(
					'field'=>'salt',
					'label'=>'salt',
					'rules'=>'required|exact_length[4]'
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

projet_model代码：
	
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
	
welcome控制器代码示例：

	<?php
	class welcome extends MY_Controller	{
		public function index(){
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
				//输出新增的用户转成array
				var_dump($new_user->toArray());
			}else{
				//若不符合要求，那么输出提示信息
				$messages = user_model::validateMessages();
				var_dump($messages);
			}
			//将多个用户项目对象列表转化为数组
			var_dump(user_model::listToArray($user_projects));
		}
	}


## 其他
	待续。。。
