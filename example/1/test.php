<?php

require '../../sireum.php';

// load database module
$sireum->setDB(array('localhost', 'root', '', 'restaurant'));

$sireum->add('index', function() {
	echo 'hello world, <a href="test.php?act=db">test db</a>.';
});

$sireum->add('db', function() use ($sireum){
	$sireum->view->simple($sireum->db->tables(), true);
});