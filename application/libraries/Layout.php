<?php
/**
 * PHP模版类
 */
class Layout{

	/**
	 * 视图根目录
	 * @var  string
	 */
	private $_view_dir;

	/**
	 * 视图子内容
	 * @var string
	 */
	private $_content;

	/**
	 * 父视图
	 * @var Layout
	 */
	private $_parent;

	/**
	 * 视图地址
	 * @var string
	 */
	private $_layout;

	function __construct(){
		$this->_view_dir = APPPATH.'views/';
	}

	/**
	 * 设置视图文件路径
	 * @param [type] $layout [description]
	 * @return this
	 */
	function setLayout($layout){
		$this->_layout = $layout;
		return $this;
	}

	/**
	 * 输出视图
	 * @return void
	 */
	function view($data = array()){
		extract($data);
		ob_start();
		include($this->_view_dir.$this->_layout.'.php');
		$content = ob_get_contents();
		ob_end_clean();
		if(!is_null($this->_parent)){
			$this->_parent->setContent($content)->view($data);
		}else{
			echo $content;
		}
	}

	/**
	 * 继承视图
	 * @param  string $layout 视图文件地址
	 * @return void
	 */
	function extend($layout){
		$this->_parent = new static();
		$this->_parent->setLayout($layout);
	}

	/**
	 * 子视图内容
	 * @return string 子视图内容字符串
	 */
	function getContent(){
		return $this->_content;
	}

	/**
	 * 设置视图子内容
	 * @param string $content 视图子内容
	 * @return this
	 */
	function setContent($content){
		$this->_content = $content;
		return $this;
	}
}