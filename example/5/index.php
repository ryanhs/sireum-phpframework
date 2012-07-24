<?php
require '../../sireum.php';

$sireum->enableDB(array('localhost', 'root', '', 'test'));

$sireum->add('index', function() use($sireum){
	$sireum->db->flushCache();
	$sireum->db->set('name', 'friends_' . rand(0, 9));
	$sireum->db->insert('friends');
	
	echo $sireum->db->last_id();
});