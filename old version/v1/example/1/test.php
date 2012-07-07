<?php

require '../../sireum.php';

class test{
	
	function index(){
		echo 'this my index';
	}
	
	// test.php?act=somepage
	function somepage(){
		echo  'this is some page';
	}
	
}(new SIREUM(null, 'test'));