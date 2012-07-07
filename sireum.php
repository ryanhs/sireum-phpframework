<?php
/* 
 * ******************************************
 * *  Sireum, minipack Ajax-PHP framework   *
 * *                                        *
 * *  Created By:	Ryan H. S.              *
 * *  Version   :	2.1                     *
 * ******************************************
 */

define('PHP_VERSION_INVALID', "SIREUM 2.0 can't run.. please upgrade your php version, at least 5.3.0");
define('ERR_MYSQLI_LIB', "Server error, can't find mysqli class");
define('ERR_MYSQLI_CNF', "Server error, mysql config not properly assigned");
define('ERR_MYSQLI_CNT', "Server error, can't connect to mysql server");
define('CONTROLLER_NOBJECT', "Invoked controller isn't object");
define('CONTROLLER_NEXISTS', "Controller name isn't class");
define('HTTP_404', "404- not found");

if (version_compare(PHP_VERSION, '5.3.0', '<'))
	exit(PHP_VERSION_INVALID);
	
if(!class_exists('mysqli'))
	exit(ERR_MYSQLI_LIB);

	
$mtime = microtime(); 
$mtime = explode(" ",$mtime); 
$mtime = $mtime[1] + $mtime[0]; 
$starttime = $mtime;
			
if(!class_exists('SIREUM')){
	class SIREUM{
		private $actions;
		private $dbconfig;
		public $db;
		public $view;
		public $session;
		
		
		public function __construct() {
			$this->view = new view();
			$this->actions = array();
			
			// run all the code in when the php want to shutdown the page
			register_shutdown_function(array($this, 'run'));
		}
		
		public function enableDB($dbconfig){
			$this->dbconfig = $dbconfig;
			if(is_array($dbconfig))
				$this->db = sDB::getInstance($dbconfig);
		}
		
		public function enableSession($config = array()) {
			$default = array(
				'name' => 'sireumCookie',
				'key' => '123',
				'expires' => time() + 60*60*24*30*12, // ~12 months
				'dir' => '/',
				'site' => '',
			);
			
			$param = array_merge($default, $config);
			$sess = new session($param['name'], $param['key'], $param['expires'], $param['dir'], $param['site']);
			$this->session = &$sess->data;
		}
		
		public function add($act, $func) {
			$this->actions[$act] = $func;
		}
		
		public function run() {
			$action = 'index';
			if(!empty($_GET['act']))
				$action = $_GET['act'];
			if(array_key_exists($action, $this->actions))
				$this->actions[$action]();
			else
				exit(HTTP_404);
		}
		
		public function timer() {
			global $starttime;
			$mtime = microtime(); 
			$mtime = explode(" ",$mtime); 
			$mtime = $mtime[1] + $mtime[0]; 
			$endtime = $mtime; 
			$totaltime = ($endtime - $starttime);
			return number_format($totaltime, 3);
		}
		
		// alias function
		public function render($view, $data = null, $output = false) { return $this->view->render($view, $data, $output); }
		public function simple($data, $debug = false) { return $this->view->simple($data, $debug); }
	}
}

if(!class_exists('sDB')){
	class sDB{
		private $_db;
		private $_lastQuery = '';
		
		private $_select = '*';
		private $_set = array();
		private $_join = array();
		private $_where = array();
		private $_limit = array();
		
		function flushCache(){
			$this->_select = '*';
			$this->_set = array();
			$this->_join = array();
			$this->_where = array();
			$this->_limit = array();
		}
		
		function select($select){
			$this->_select = $select;
		}
		
		function set($k, $v){
			$this->_set[$k] = $v;
		}
		
		function join($tbl, $foreignKey1, $foreignKey2){
			$this->_join[] = array(
				'table' => $tbl,
				'foreignKey1' => $foreignKey1,
				'foreignKey2' => $foreignKey2,
			);
		}
		
		function where($k, $v){ $this->__where($k, $v, '=', 'AND');}
		function whereOr($k, $v){ $this->__where($k, $v, '=', 'OR');}
		function like($k, $v){ $this->__where($k, $v, 'LIKE', 'AND');}
		function likeOr($k, $v){ $this->__where($k, $v, 'LIKE', 'OR');}
		function __where($k, $v, $op, $method){
			$this->_where[] = array(
				'method' => $method,
				'key' => $k,
				'operator' => $op,
				'value' => $v,
			);
		}
		
		function limit($perpage = 1, $start = 0){
			$this->_limit['perpage'] = $perpage;
			$this->_limit['start'] = $start;
		}
		
		function get($tblName){return $this->query($this->__select($tblName, 'SELECT'));}
		function count($tblName){return $this->query($this->__select($tblName, 'COUNT'));}
		function __select($tblName, $method = 'SELECT'){
			if($method == 'COUNT')
				$sql = 'SELECT count(*)';
			else
				$sql = 'SELECT ' . $this->_select;
				
			$sql .= "\n FROM " . $tblName;
			
			if(count($this->_join) > 0){
				foreach($this->_join as $join) {
					$sql .= "\n JOIN " . $join['table'];
					$sql .= ' ON ' . $join['foreignKey1'];
					$sql .= ' = ' . $join['foreignKey2'];
				}
			}
				
			if(count($this->_where) > 0){
				$sql .= "\n WHERE ";
				$i = 0;
				foreach($this->_where as $where) {
					$sql .= $i > 0 ? " {$where['method']} " : '';
					
					$sql .= "({$where['key']}";
					$sql .= " {$where['operator']}";
					$sql .= $where['operator'] == 'LIKE' ? " '{$where['value']}')" : " {$where['value']})";
					$i++;
				}
			}
			
			if((count($this->_limit) == 2) && ($method == 'SELECT'))
				$sql .= " \nLIMIT {$this->_limit['start']}, {$this->_limit['perpage']}";
			
			$sql .= ';';
			return $sql;
		}
		
		function delete($tblName){return $this->query($this->__delete($tblName));}
		function __delete($tblName){
			$sql = 'DELETE FROM ' . $tblName;
			if(count($this->_where) > 0){
				$sql .= "\n WHERE ";
				$i = 0;
				foreach($this->_where as $where) {
					$sql .= $i > 0 ? " {$where['method']} " : '';
					
					$sql .= "({$where['key']}";
					$sql .= " {$where['operator']}";
					$sql .= $where['operator'] == 'LIKE' ? " '{$where['value']}')" : " {$where['value']})";
					$i++;
				}
			}
			
			$sql .= ';';
			return $sql;
		}
		
		function insert($tblName){return $this->query($this->__insert($tblName));}
		function __insert($tblName){
			$sql = 'INSERT INTO ' . $tblName;
			$sql .= '(';
			$i = 0;
			foreach($this->_set as $k => $v) {
				$sql .= $i > 0 ? ", " : '';
				$sql .= "{$k}";
				$i++;
			}
			$sql .= ') VALUES(';
			$i = 0;
			foreach($this->_set as $k => $v) {
				$sql .= $i > 0 ? ", " : '';
				$sql .= "'{$v}'";
				$i++;
			}
			$sql .= ')';
			
			$sql .= ';';
			return $sql;
		}
		
		function update($tblName){return $this->query($this->__update($tblName));}
		function __update($tblName){
			$sql = 'UPDATE ' . $tblName;
			if(count($this->_set) > 0){
				$sql .= "\n SET";
				$i = 0;
				foreach($this->_set as $k => $v) {
					$sql .= $i > 0 ? ", " : ' ';
					$sql .= "{$k} = '{$v}'";
					$i++;
				}
			}

			if(count($this->_where) > 0){
				$sql .= "\n WHERE ";
				$i = 0;
				foreach($this->_where as $where) {
					$sql .= $i > 0 ? " {$where['method']} " : '';
					
					$sql .= "({$where['key']}";
					$sql .= " {$where['operator']}";
					$sql .= $where['operator'] == 'LIKE' ? " '{$where['value']}')" : " {$where['value']})";
					$i++;
				}
			}
			
			$sql .= ';';
			return $sql;
		}
		
		function tables(){return $this->query('SHOW TABLES');}
		function databases(){return $this->query('SHOW DATABASES');}
		
		function query($sql){
			$this->_lastQuery = $sql;
			return $this->_db->query($sql);
		}
		
		function error(){
			return $this->_db->error;
		}
		
		function __construct($dbconfig){
			if(count($dbconfig) < 4)
				exit(ERR_MYSQLI_CNF);
				
			$this->_db = new mysqli($dbconfig[0], $dbconfig[1], $dbconfig[2], $dbconfig[3]);
			if ($this->_db->connect_errno)
				exit(ERR_MYSQLI_CNT);
		}
		
		function __destruct(){
			if(!empty($this->_db))
				$this->_db->close();
		}
		
		// for magic calling
		protected static $instance = null;
		public static function getInstance($dbconfig = null){
			if(sDB::$instance == null && is_array($dbconfig))
				sDB::$instance = new sDB($dbconfig);
			return sDB::$instance;
		}
	}
}

if(!class_exists('view')){
	class view{
		
		public function simple($data, $debug = false){
			echo $debug ? '<pre>' : '<p>';
			if($debug)
				var_dump($data);
			else
				echo is_array($data) ? json_encode($data) : $data;
			echo $debug ? '</pre>' : '</p>';
		}
		
		public function render($view, $data = null, $output = false) {
			global $sireum;
			
			// change to sireum path, usefull for fix bug in register_shutdown_function()
			chdir(dirname(__FILE__));
			
			if(is_file($view . '.php')) {
				if(is_array($data))
					extract($data);
				if($output)
					ob_start();
				require $view . '.php';
				if($output)
					return ob_get_clean();
				else
					return true;
			}
			return false;
		}
	}
}

if(!class_exists('session')){
	class session{
		private $cookie_name;
		private $cookie_expires;
		private $cookie_dir;
		private $cookie_site;
		
		private $key;
		public $data;
		
		public function __construct($name, $key, $expires, $dir, $site) {
			$this->cookie_name = $name;
			$this->cookie_expires = $expires;
			$this->cookie_dir = $dir;
			$this->cookie_site = $site;
			$this->key = $key;
			$this->data = (object) (!empty($_COOKIE[$name]) ? json_decode(self::decrypt($this->key, $_COOKIE[$name])) : array());
			
			// auto save cookie
			register_shutdown_function(array($this, 'save'));
		}
		
		private static function encrypt($key, $str) {
			$encrypted = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, md5($key), $str, MCRYPT_MODE_CBC, md5(md5($key))));
			return $encrypted;
		}
		
		private static function decrypt($key, $encrypted) {
			$decrypted = rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5($key), base64_decode($encrypted), MCRYPT_MODE_CBC, md5(md5($key))), "\0");
			return $decrypted;
		}
		
		public function save(){
			$data = self::encrypt($this->key, json_encode($this->data));
			setcookie($this->cookie_name, $data, $this->cookie_expires, $this->cookie_dir, $this->cookie_site);
		}
	}
}



// auto call sireum
$sireum = new SIREUM();
function getInstance()
{
	global $sireum;
	return $sireum;
}