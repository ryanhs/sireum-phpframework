<?php

require '../../sireum.php';

class test{
	private $view;
	
	public function __construct() {
		$this->view = new view();
	}
	
	public function index(){
		$o = $this->view->render('./simpleview', array(
			'v' => 'hello world!'
		));
		
		if(!$o)
			$this->view->simple('file not exists.');
	}
	
}(new SIREUM(null, 'test'));