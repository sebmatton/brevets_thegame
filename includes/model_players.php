<?php

class Players
{
	private $database;

	function __construct($db) {
		$this->database = $db;
	}

	public function getPlayers($game_id){
		global $config;

		$results = $this->database->query(
			"SELECT *, (SELECT COUNT(mission_id) FROM ".DATABASE_PREFIX."history WHERE player_id = ".DATABASE_PREFIX."players.player_id AND result <> -1 ) as 'count'
				FROM ".DATABASE_PREFIX."users 
				LEFT JOIN ".DATABASE_PREFIX."players ON user_id = fk_user_id AND game_id = ".$game_id."
				LEFT JOIN ".DATABASE_PREFIX."history ON ".DATABASE_PREFIX."history.player_id = ".DATABASE_PREFIX."players.player_id AND result = -1
				WHERE permissions = ".$config['permissions']['player']." ORDER BY firstname ASC")->fetchAll();

		if($results == false || count($results) < 1){
			return array();
		}else{
			return $results;
		}
	}

	public function registerPlayer($game_id){
		$get = $this->database->get('players', '*', 
			array(
				'AND' => array(
					'fk_user_id' => $_SESSION['login']['user_id'],
					'game_id' => $game_id
				)
			)
		);
		if($get == false || count($get) < 1){
			// Need to create it
			$insert = $this->database->insert('players', array(
					'fk_user_id' => $_SESSION['login']['user_id'],
					'game_id' => $game_id
				));
			if($insert == false || $insert <=0){
				flash('danger', 'Une erreur est survenue lors de l\'envoi des données (register).');
				return false;
			}else{
				return $insert;
			}
		}else{
			// User already registerd
			return $get['player_id'];
		}

	}
}

?>