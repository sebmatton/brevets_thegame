<?php

class Login
{
	private $database;
	function __construct($db) {
		$this->database = $db;
	}

	public function isFormPosted() {
		return ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['token']) && $_POST['token'] == session_id());
	}

	public function checkPost(){
		$errors = array();

		if(isset($_SESSION['login_attempts_delay'])){
			if(time() < $_SESSION['login_attempts_delay']){
				$errors['maxattempts'] = $_SESSION['login_attempts_delay']-time();
				return $errors;
			}else{
				unset($_SESSION['login_attempts_delay']);
			}
		}

		// Check if login correctly posted
		if(!isset($_POST['username']) || empty($_POST['username'])){
		// if(!isset($_POST['username']) || empty($_POST['username']) || !isset($_POST['password']) || empty($_POST['password'])){
			$errors['empty'] = true;
			return $errors;
		}

		// Retrieve user
		$db_datas = array();
		try {
			$db_datas = $this->database->get('users', '*', array('login[=]' => trim(strtolower($_POST['username']))));
		}catch(Exception $e){
			// echo 'Unable to read db !';
			$errors['db_read'] = true;
			return $errors;
		}

		// Check username and password
		if($db_datas != false && count($db_datas) > 0){
		// if($db_datas != false && count($db_datas) > 0 && md5($_POST['password']) == $db_datas['password']){
			$init = $this->initSession($db_datas);
			if($init == -1){
				$errors['disabled'] = true;
				return $errors;
			}
			$this->doCookie();
			return true;
		}else{
			if(!isset($_SESSION['login_attempts'])){
				$_SESSION['login_attempts'] = 1;
			}else{
				$_SESSION['login_attempts']++;
			}
			if($_SESSION['login_attempts'] >= LOGIN_MAX_ATTEMPTS){
				$_SESSION['login_attempts_delay'] = time() + LOGIN_DELAY_ATTEMPTS;
				$_SESSION['login_attempts'] = 0;
			}
			$errors['incorrect'] = true;
			return $errors;
		}
	}

	public function tryAutoLogin(){
		// Check if the cookie exists
		if(isset($_COOKIE[COOKIE_NAME])){
			// Read the cookie
			$cookie = array();
			parse_str($_COOKIE[COOKIE_NAME], $cookie);
			$auto_id = $cookie['a'];
			$user = $cookie['b'];
			$hash = $cookie['c'];

			// Get autologin
			$auto_login = array();
			try {
				$auto_login = $this->database->get('autologin', '*', array('auto_id' => $auto_id));
			}catch(Exception $e){
				return -1;
			}

			// Check if autologin select succeeded
			if(count($auto_login) > 1){
				// Check if cookie values are correct
				$user_pass_update = $this->database->get('users', 'password_update', array('user_id' => $auto_login['user_fk']));
				if($user_pass_update == false){
					$this->unsetAutoLogin($auto_id);
					return -3; // User do not exists in db
				}elseif($auto_login['user_fk']==$user && $auto_login['hash']==$hash){
					// Init times
					$user_pass_update = new DateTime($user_pass_update);
					$user_pass_update = intval($user_pass_update->format('U'));

					$user_auto_create = new DateTime($auto_login['created_at']);
					$user_auto_create = intval($user_auto_create->format('U'));
					
					// Check if cookie expired
					if($auto_login['expires'] < time()){
						$this->unsetAutoLogin($auto_id);
						return -2; // Auto-login expired !
					}elseif($user_pass_update > $user_auto_create){
						$this->unsetAutoLogin($auto_id);
						return -4; // Password has changed
					}else{
						$db_datas = 0;
						try{
							$db_datas = $this->database->get('users', '*', array('user_id' => $user));
						}catch(Exception $e){
							return -1;
						}
						if(count($db_datas) > 1){
							$this->initSession($db_datas, $auto_id);
							header('location: '.BASE_URL);
							return 1;
						}
					}
				}
			}
		}
		return 0;
	}

	private function initSession($row, $autologin=0){
		global $config;
		// Check if account is disabled
		if($row['permissions'] >= $config['permissions']['disabled']){
			return -1;
		}
		$_SESSION['login']['connected'] = true;
		$_SESSION['login']['login'] = $row['login'];
		$_SESSION['login']['username'] = $row['firstname'] . ' ' . $row['lastname'];
		$_SESSION['login']['user_id'] = $row['user_id'];
		$_SESSION['login']['permissions'] = $row['permissions'];
		$_SESSION['flash'] = array();
		$_SESSION['login']['logout'] = false;
		if($autologin>0){
			$_SESSION['login']['autologin'] = $autologin;
		}
		return 1;
	}

	public function logout(){
		if(isset($_SESSION['login']['autologin'])){
			$this->unsetAutoLogin($_SESSION['login']['autologin']);
		}
		$_SESSION = array();
		session_unset();
		return;
	}

	private function doCookie(){
		if(!isset($_POST['remember']) || $_POST['remember'] != 1)
			return 0; // User did not asked to remember password

		// Define data to store
		$user = $_SESSION['login']['user_id'];
		$hash = md5(rand(1000, 9999) . $_SESSION['login']['user_id'] . rand(1000, 9999));
		$expires = time()+COOKIE_TIME;

		require_once 'includes/Browser.php/browser.php';
		$browser = new Browser();
		$user_agent = $browser->getPlatform() . ' ## '. $browser->getBrowser() .' '. $browser->getVersion() .' ## ';
		$user_agent .= substr($browser->getUserAgent(),0,250-strlen($user_agent));
 
		// Insert data in db
		$last_id = -1;
		try{
			$last_id = $this->database->insert('autologin', array('user_fk' => $user, 'hash' => $hash, 'expires' => $expires, 'user_agent' => $user_agent));
		}catch(Exception $e){
			return -1;
		}

		// Create cookie
		setcookie(COOKIE_NAME, 'a='.$last_id.'&b='.$user.'&c='.$hash, $expires);

		$_SESSION['login']['autologin'] = $last_id;

		return 1;
	}

	private function unsetAutoLogin($auto_login_id){
		// Remove cookie
		if(isset($_COOKIE[COOKIE_NAME])){
		    unset($_COOKIE[COOKIE_NAME]);
		    setcookie(COOKIE_NAME, '', time() - 3600, '/'); // empty value and old timestamp
		}

		// Remove db record
		try{
			$this->database->delete('autologin',array('auto_id' => $auto_login_id));
		}catch(Exception $e){
			return -1;
		}
		return 1;
	}

	public function isConnected(){
		return isset($_SESSION['login']) && isset($_SESSION['login']['connected']) && $_SESSION['login']['connected'];
	}

	public function getUserName(){
		if($this->isConnected()){
			return $_SESSION['login']['username'];
		}else{
			return 'undefined';
		}
	}

	public function getUserID(){
		if($this->isConnected()){
			return $_SESSION['login']['user_id'];
		}else{
			return 'undefined';
		}
	}

	public function getUserToken(){
		return session_id();
	}

	public function getUserPermissions(){
		if($this->isConnected()){
			return $_SESSION['login']['permissions'];
		}else{
			return 'undefined';
		}
	}

	public function hasPermissions($level){
		global $config;
		if(!isset($_SESSION['login']) || !isset($_SESSION['login']['permissions'])){
			return false;
		}
		return ($_SESSION['login']['permissions'] <= $config['permissions'][$level]);
	}
}