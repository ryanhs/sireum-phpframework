<?php
require '../../sireum.php';
$sireum->enableSession();

$sireum->add('index', function() use ($sireum){
	
	$sireum->simple($sireum->session, true);
	//$sireum->session->s = 'haha2';
});