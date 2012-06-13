<?php

require '../../sireum.php';

class test{
	public $db;
	
	function __construct(){
		$this->db = sDB::getInstance();
	}
	
	function index(){
		$q = $this->db->databases();
		//$q = $this->db->tables();
		
		while($row = $q->fetch_row()){
			echo $row['0'] . '<br/>';
		}
	}
	
	function insert(){
		$username = 'myuser';
		$password = 'mypass';
		
		$this->db->set('username', $username);
		$this->db->set('password', $password);
		
		$this->db->insert('user');
		
		if(!$this->db->error())
			echo "insert user {$username} failed!";
		else
			echo 'ok';
	}
	
	function select(){
		$this->db->where('id', 1);
		$user = $this->db->get('user')->fetch_assoc();
		
		var_dump($user);
	}
	
}(new SIREUM(array('localhost', 'root', '', 'mysql'), 'test'));