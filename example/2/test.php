<?php

require '../../sireum.php';

$sireum->add('index', function() use ($sireum){
	// load database inside action
	$sireum->enableDB(array('localhost', 'root', '', 'restaurant'));
	
	$o = $sireum->render('example/2/simpleview', array(
		'q' => $sireum->db->tables()
	));
	
	if($o == false)
		$sireum->simple('simpleview can\'t be loaded.');
	
	// use alias function for shortcut
	$sireum->simple('Execution time: ' . $sireum->timer() . 's');
});