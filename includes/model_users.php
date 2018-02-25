<?php

class Users
{
	private $database;

	function __construct($db) {
		$this->database = $db;
	}

	public function getUser($id){
		$results = $this->database->get('users', array('user_id', 'login', 'firstname', 'lastname', 'permissions'), array('user_id' => $id));
		if($results == false || count($results) <= 1){
			return array();
		}else{
			return $results;
		}
	}

	public function getUsers($min_permission = 9999, $order = 'user_id', $hide_superadmin = false){
		global $config;
		$results = $this->database->select(
			'users', 
			array('user_id', 'login', 'firstname', 'lastname', 'permissions'), 
			array('AND' => array(
				'permissions[<=]' => $min_permission, 
				'permissions[>]' => $hide_superadmin?$config['permissions']['superadmin']:($config['permissions']['superadmin']-1)
			), 
			'ORDER' => $order));
		if($results == false || count($results) < 1){
			return array();
		}else{
			return $results;
		}
	}

	public function proceedPost(){
		global $config;

		$action = $_POST['action'];
		$actions = array('insert', 'modify', 'delete', 'profile', 'cookies');
		if(!isset($action) || !in_array($action, $actions)){
			flash('danger', 'Une erreur est survenue lors de l\'envoi des données.');
		}else{
			GUMP::add_validator("empty_or_length", function($field, $input, $param = NULL) {
				$param = explode('-', $param);
				$min_len = $param[0];
				$max_len = $param[1];
				return empty($input[$field]) || (strlen($input[$field]) >= $min_len && strlen($input[$field]) <= $max_len);
			});

			$validator = new GUMP();
			if($action == 'insert'){
				$rules = array(
					'user_login' 				=> 'required|alpha_numeric|max_len,20|min_len,3',
					'user_firstname' 			=> 'required|max_len,20|min_len,2',
					'user_lastname' 			=> 'required|max_len,20|min_len,2',
					'user_permissions' 			=> 'required|contains_list,'.implode(';',$config['auth_permissions']),
					'insert_password' 			=> 'required|max_len,20|min_len,6',
					'insert_password_confirm' 	=> 'equalsfield,insert_password',
				);
				$filters = array(
					'user_login' 				=> 'trim',
					'user_firstname' 			=> 'trim',
					'user_lastname' 			=> 'trim',
				);
				$_POST = $validator->filter($_POST, $filters);
				$validated = $validator->validate($_POST, $rules);

				// If there is errors within the post values
				if($validated !== true){
					$errors = array();
					$errors_text= array(
						'user_login' 				=> 'L\'identifiant du nouvel utilisateur n\'est pas correctement défini.',
						'user_firstname' 			=> 'Le prénom du nouvel utilisateur n\'est pas correctement défini.',
						'user_lastname' 			=> 'Le nom du nouvel utilisateur n\'est pas correctement défini.',
						'user_permissions' 			=> 'Les droits d\'accès du nouvel utilisateur ne sont pas correctement définis.',
						'insert_password' 			=> 'Le mot de passe du nouvel utilisateur n\'est pas correctement défini.',
						'insert_password_confirm' 	=> 'Les mots de passe du nouvel utilisateur ne correspondent pas.',
					);
					// Parse errors : 1 error for each field
					foreach ($validated as $error)
						$errors[$error['field']] = $errors_text[$error['field']];
					// Flash errors !
					foreach ($errors as $e)
						flash('danger', $e);
				// If the login already exists
				}elseif(count($this->database->get('users', '*', array('login'=>$_POST['user_login']))) > 1){
					flash('danger', 'Le login "'.$_POST['user_login'].'" existe déjà !');
				// Everything is correct, proceed with new user insertion
				}else{
					// Values are correct, we can proceed with db insertion !
					$insert_id = $this->database->insert('users', array(
						'login' => $_POST['user_login'],
						'firstname' => $_POST['user_firstname'],
						'lastname' => $_POST['user_lastname'],
						'permissions' => intval($_POST['user_permissions']),
						'password' => md5($_POST['insert_password']),
					));
					// Check if insertion was successful
					if($insert_id > 0){
						flash('success', 'Le nouvel utilisateur "'.$_POST['user_firstname']. ' ' .$_POST['user_lastname'] .'" a bien été créé.');
					}else{
						flash('danger', 'Une erreur est survenue lors de l\'enregistrement du nouvel utilisateur.');
					}
				}
			}elseif($action == 'modify'){
				$rules = array(
					'modify_userid'				=> 'required|integer',
					'user_firstname' 			=> 'required|max_len,20|min_len,2',
					'user_lastname' 			=> 'required|max_len,20|min_len,2',
					'user_permissions' 			=> 'required|contains_list,'.implode(';',$config['auth_permissions']),
					'modify_password' 			=> 'empty_or_length,6-20',
					'modify_password_confirm' 	=> 'equalsfield,modify_password',
				);
				$filters = array(
					'user_firstname' 			=> 'trim',
					'user_lastname' 			=> 'trim',
				);
				$_POST = $validator->filter($_POST, $filters);
				$validated = $validator->validate($_POST, $rules);

				// If there is errors within the post values
				if($validated !== true){
					$errors = array();
					$errors_text= array(
						'modify_userid'				=> 'L\'utilisateur choisi n\'est pas valide.',
						'user_firstname' 			=> 'Le prénom de l\'utilisateur n\'est pas correctement défini.',
						'user_lastname' 			=> 'Le nom de l\'utilisateur n\'est pas correctement défini.',
						'user_permissions' 			=> 'Les droits d\'accès de l\'utilisateur ne sont pas correctement définis.',
						'modify_password' 			=> 'Le mot de passe de l\'utilisateur n\'est pas correctement défini.',
						'modify_password_confirm' 	=> 'Les mots de passe ne correspondent pas.',
					);
					// Parse errors : 1 error for each field
					foreach ($validated as $error)
						$errors[$error['field']] = $errors_text[$error['field']];
					// Flash errors !
					foreach ($errors as $e)
						flash('danger', $e);
				// If the login doesn't exist
				}elseif(!(count($this->database->get('users', '*', array('user_id'=>$_POST['modify_userid']))) > 1)){
					flash('danger', 'L\'utilisateur choisi n\'existe pas !');
				// Everything is correct, proceed with new user insertion
				}else{
					// Values are correct, we can proceed with db update !
					$new_values = array(
						'user_id'		=> $_POST['modify_userid'],
						'firstname' 	=> $_POST['user_firstname'],
						'lastname' 		=> $_POST['user_lastname'],
						'permissions' 	=> intval($_POST['user_permissions']),
					);
					if(!empty($_POST['modify_password'])){
						$new_values['password'] = md5($_POST['modify_password']);
						$new_values['password_update'] = date('c');
					}
					if(count($this->database->get('users', '*', array('and' => $new_values))) > 1){
						flash('warning', 'Aucune donnée à modifier pour l\'utilisateur "'.$_POST['user_firstname']. ' ' .$_POST['user_lastname'] .'".');
						return;
					}
					$updated_count = $this->database->update('users', $new_values, array('user_id'=>$_POST['modify_userid']));
					// Check if insertion was successful
					if($updated_count == 1){
						flash('success', 'L\'utilisateur "'.$_POST['user_firstname']. ' ' .$_POST['user_lastname'] .'" a bien été modifié.');
					}else{
						flash('danger', 'Une erreur est survenue lors de la modification de l\'utilisateur.');
					}
				}
			}elseif($action=='delete'){
				if(!(count($this->database->get('users', '*', array('user_id'=>intval($_POST['delete_userid'])))) > 1)){
					flash('danger', 'L\'utilisateur choisi n\'existe pas !');
				}else{
					if($this->database->delete('users', array('user_id' => intval($_POST['delete_userid']))) > 0){
						flash('success', 'L\'utilisateur a bien été supprimé.');
					}else{
						flash('danger', 'Erreur lors de la suppression de l\'utilisateur !');
					}
				}
			}elseif($action=='profile'){
				$rules = array(
					'user_firstname' 			=> 'required|max_len,20|min_len,2',
					'user_lastname' 			=> 'required|max_len,20|min_len,2',
					'modify_password' 			=> 'empty_or_length,6-20',
					'modify_password_confirm' 	=> 'equalsfield,modify_password',
					'current_password_confirm'	=> 'required|max_len,20|min_len,6',
				);
				$filters = array(
					'user_firstname' 			=> 'trim',
					'user_lastname' 			=> 'trim',
				);
				$_POST = $validator->filter($_POST, $filters);
				$validated = $validator->validate($_POST, $rules);
				global $login;
				$user_id = $login->getUserID();

				// If there is errors within the post values
				if($validated !== true){
					$errors = array();
					$errors_text= array(
						'user_firstname' 			=> 'Le prénom de l\'utilisateur n\'est pas correctement défini.',
						'user_lastname' 			=> 'Le nom de l\'utilisateur n\'est pas correctement défini.',
						'modify_password' 			=> 'Le nouveau mot de passe de l\'utilisateur n\'est pas correctement défini.',
						'modify_password_confirm' 	=> 'Les nouveaux mots de passe ne correspondent pas.',
						'current_password_confirm'	=> 'Le mot de passe actuel n\'est pas correctement défini.',
					);
					// Parse errors : 1 error for each field
					foreach ($validated as $error)
						$errors[$error['field']] = $errors_text[$error['field']];
					// Flash errors !
					foreach ($errors as $e)
						flash('danger', $e);
				// If the login doesn't exist
				}elseif(!(count($this->database->get('users', '*', array('user_id'=>$user_id))) > 1)){
					flash('danger', 'L\'utilisateur choisi n\'existe pas !');
				// If current password is not correct
				}elseif(!(count($this->database->get('users', '*', array('and' => array('user_id'=>$user_id, 'password'=>md5($_POST['current_password_confirm']))))) > 1)){
					flash('danger', 'Le mot de passe actuel est incorrect !');
				// Everything is correct, proceed with new user insertion
				}else{
					// Values are correct, we can proceed with db update !
					$new_values = array(
						'user_id'		=> $user_id,
						'firstname' 	=> $_POST['user_firstname'],
						'lastname' 		=> $_POST['user_lastname'],
					);
					if(!empty($_POST['modify_password'])){
						$new_values['password'] = md5($_POST['modify_password']);
						$new_values['password_update'] = date('c');
					}
					if(count($this->database->get('users', '*', array('and' => $new_values))) > 1){
						flash('warning', 'Aucune donnée à modifier pour l\'utilisateur "'.$_POST['user_firstname']. ' ' .$_POST['user_lastname'] .'".');
						return;
					}
					$insert_id = $this->database->update('users', $new_values, array('user_id'=>$user_id));
					// Check if insertion was successful
					if($insert_id == 1){
						flash('success', 'L\'utilisateur "'.$_POST['user_firstname']. ' ' .$_POST['user_lastname'] .'" a bien été modifié.');
					}else{
						flash('danger', 'Une erreur est survenue lors de la modification de l\'utilisateur.');
					}
				}
			}elseif('cookies'){
				global $login;
				$user_id = $login->getUserID();
				$total_autologin = $this->database->select('autologin', '*', array('AND'=> array('user_fk' => $user_id, 'expires[>]' => time())));
				if($total_autologin == false || count($total_autologin)==0){
					$total_autologin = 0;
					flash('warning', 'Il n\'y a aucun appareil à déconnecter.');
				}else{
					$total_autologin = count($total_autologin);
					$deleted = $this->database->delete('autologin', array('AND'=> array('user_fk' => $user_id, 'expires[>]' => time())));
					if($deleted == $total_autologin){
						flash('success', 'Les appareils ont bien été déconnectés !');
					}else{
						flash('danger', 'Erreur lors de la déconnexion des appareils (d='.$deleted.'/t='.$total_autologin.')');
					}
				}
				
			}else{
				flash('danger', 'Une erreur est survenue lors de la soumission du formulaire.');
			}
		}
	}

	public function getAutoLogin($id){
		$results = $this->database->select('autologin', array('expires', 'user_agent', 'created_at'), array('AND'=> array('user_fk' => $id, 'expires[>]' => time())));
		if($results == false || count($results) < 1){
			return array();
		}else{
			$autologins = array();
			foreach ($results as $autologin) {
				$user_agent = explode(' ## ', $autologin['user_agent']);
				$autologins[] = array(
					'expires' => $autologin['expires'],
					'os' => (isset($user_agent[0])?$user_agent[0]:'Non-défini'),
					'browser' => (isset($user_agent[1])?$user_agent[1]:'Non-défini'),
					'created_at' => $autologin['created_at'],
				);
			}
			return $autologins;
		}
	}
}

?>