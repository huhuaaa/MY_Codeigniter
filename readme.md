## MY_Codeigniter

	MY_Codeigniter为PHP Codeigniter框架2.2.0版本的一个自定义分支版本。

## 调整CI框架原因

CI自带的Model功能较弱，且没有将model转化为一个对象来进行使用。那么为了解决以上弊端，在参考了其他一些PHP框架的Model功能后，我在CI基础上进行了一些调整。详情看后续。

## 1、建立面向对象的Model基类MY_Model

面向对象的基类MY_Model，用于将数据库数据直接转化为对应Model类型的对象。那么在建立各个Model时，可以声明相关关联关系、以及对应字段属性的类型限制验证等。


## 2、将入口文件移动，解放代码安全策略

将站点入口index.php转移到web/index.php，那么在配置网站根目录是就直接指向web目录。这样使得代码目录不在web访问范围内，解放web服务器采用配置限制目录访问，也可以省去php头文件上的那句if ( ! defined('BASEPATH')) exit('No direct script access allowed');。

## 3、引进自动加载类方法

添加自动加载model类的方法，解放以前使用一个model时，总是要先添加一句$this->load->model('user_model')用以确保已引入这个类型。当然这里会添加一些约束，model类名称必须以_model结尾，这点已经在以前的项目中都这么约束了。而library则不能出现于model一样的结尾。由于helper都是一些方法，那么helper的调用将继续采用CI的加载方法。当然原有CI的load功能同样都可以使用。

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

## 示例代码说明

看了代码之后，你肯定发现了已经没有以往$this->load->model('user_model')这样的代码了。当然可能会对user_model::validate()这个方法以及user_model下的public static $_rules参数有疑问。validate方法是根据$_rules设置的规则进行验证数据是否符合要求的。如果用过CI的form_validation类的话，会发现这个规则是一样的。是的，我只是在这个基础上做了调整而已。当然后续会根据需求再做逐步调整。

## MY_Model用法详解

首先创建一个用户的model：
	
	//用户model类
	class user_model extends MY_Model{
		
		//指定转array或JSON时，不可见的属性
		protected $_hidden = array('password');
	
		//指定主键为userid，若不指定那么默认为id
		public static $_primaryKey = 'userid';
		
		//初始化执行方法，若无需声明关联关系或者其他操作，则不需要重写
		protected function initialize(){
			//声明关联关系，一个用户拥有多个图片，若为1-1关系那么使用hasOne
			$this->hasMany('userid', 'picture_model', 'userid');
		}

		//数据校验规则,email字段为邮箱类型，并且必须rules参数使用参考CI的form validation
		public static $_rules = array(
				array(
					'field'=>'email',
					'label'=>'email',
					'rules'=>'required|is_unique[users.username]|valid_email'
					)
			);

		/**
		 * 指定数据库表名称
		 * @return string        数据库表名称
		 */
		public static function getSource()
		{
			return 'users';
		}
	}

创建图片model：

	//图片类
	class picture extends MY_Model{

		//指定数据检验规则
		public static $_rules = array(
				array(
					'field'   => 'name', 
					'label'   => 'name', 
					'rules'   => 'required|min_length[1]|max_length[30]'
					),
				array(
					'field'   => 'userid',
					'label'   => 'userid',
					'rules'	  => 'required'
					)
			);
	
		//初始化
		protected function initialize(){
			//图片归用户所有，声明归属关系
			$this->belongsTo('userid', 'user_model', 'userid');
		}
	
		/**
		 * 指定数据库表名称
		 * @return string        数据库表名称
		 */
		public static function getSource()
		{
			return 'pictures';
		}
		
		/**
		 * 创建一个新对象，并将数据添加到数据库（用户表的主键不为int类型，名称也不为id，需要重写）
		 * @param  [type] $data 
		 * @return [type]
		 */
		public static function create($data){
			$db = static::query();
			if(!empty($data) && isset($data['userid'])){
				$db->insert(static::getSource(), $data);
				return static::findFirst(array('where'=>array('userid'=>$data['userid'])));
			}else{
				return NULL;
			}
		}
		
	}

新增：
	
	$array = array('email'=>'qk@detu.com','username'=>'huhuaaa','password'=>'password');
	
	//验证数据是否符合要求
	if(user_model::validate($array)){
		//符合要求，则新增并创建用户对象
		$user = user_model::create($array);
	}else{
		//不符合，那么提取错误信息，错误信息为一个数组，如：array('email'=>'some thing error','username'=>'error message')
		$messages = user_model::validateMessages();
	}

查询单个：
	
	//若主键为int型，那么使用主键查询
	$picture = picture_model::findFirst(1);

	//使用where语句查询
	$picture = picture_model::findFirst('userid = 1');
	
	//使用where数组
	$picture = picture_model::findFirst(array('where'=>array('userid'=>1)));

	//对象转数组
	$picture->toArray();

	//指定属性转数组
	$keys = array('id','name');
	$picture->toArray($keys);

查询多个：
	
	$select = 'id,name,userid';
	$where = array('userid'=>1); //where也可以为字符串语句，如： 'userid = 1'
	$order = 'id DESC';
	$limit = 10; //limit可以为单个数字或者数组 array(10, 5)。数字的作用等同于array(10, 0)
	$pictures = picture_model::find(array('select'=>$select,'where'=>$where,'order'=>$order,'limit'=>$limit));

	//将$pictures转化为数组
	$array = picture_model::listToArray($picture);

	//如果只需要部分属性转化为数组，那么采用指定属性值
	$keys = array('id','name');
	$array = picture_model::listToArray($picture, $keys);

注：select、where、order、limit都不是必须的。当然一般情况下where基本上都有。

修改保存数据(对象属性设置方式)：

	$picture = picture_model::findFirst(1);
	//设置图片名称
	$picture->name = '图片名称';

	//指定参数保存，推荐采用此方法
	$picture->save(array('name'));

	//也可以直接保存所有属性
	$picture->save();

修改保存数据(数组直接保存方式)：

	$picture = picture_model::findFirst(1);
	$picture->saveData(array('name'=>'图片名称'));


删除对象在数据库中的数据：
	
	//调用对象删除方法
	$picture = picture_model::findFirst(1);
	//删除
	$picture->destroy();

	//条件批量删除，使用静态方法
	$where = array('id'=>1);
	picture_model::remove($where);

根据声明的关联关系，获取数据：
	
	$user = user_model::findFirst();
	//获取用户的图片
	$user_pictures = $user->get_picture_models();
	
	$picture = picture_model::findFirst();
	//根据图片查询所有者
	$picture_user = $picture->get_user_model();

注：采用了belongsTo,hasMany,hasOne三个关联关系声明后，才拥有以上这些方法。方法名的规则如下：
	
	belongsTo($key, $model, $foreignKey) => get_[$model]
	hasMany($foreignKey, $model, $key) 	 => get_[$model]s
	hasOne($foreignKey, $model, $key)    => get_[$model]

>get_[$model]s($array = array())方法可以接收一个数组参数，使用方法同model的find方法传入一个数组。如：$user->get_picture_models(array('select'=>'id', 'where'=>array('isdelete'=>1)));

## 其他
	待续。。。
