<?php

require '../../sireum.php';

// load database module
$sireum->enableDB(array('localhost', 'root', '', 'restaurant'));

$sireum->add('index', function() {
	// use simple echo, for beginner user
	echo 'hello world, <a href="test.php?act=db">test db</a>.';
});

$sireum->add('db', function() use ($sireum){
	$sireum->view->simple($sireum->db->tables(), true);
});