<?php

class Games
{
	private $database;

	function __construct($db) {
		$this->database = $db;
	}

	public function getCurrentGame(){
		$results = $this->database->get('games', '*', array('ORDER' => array('game_id' => 'DESC')));
		if($results == false || count($results) <= 1){
			return array();
		}else{
			return $results;
		}
	}

	public function start(){
		$this->database->update('games', array('active' => false, 'stop_time' => date('c')), array('active' => true));
		$game_id = $this->database->insert('games', array(
			'active' => true
		));
		if($game_id > 0){
			flash('success', 'New game started !');
		}else{
			flash('danger', 'Error when starting new game !');
		}
	}

	public function stop(){
		$updated_count = $this->database->update('games', array('active' => false, 'stop_time' => date('c')), array('active' => true));

		// Check if insertion was successful
		if($updated_count == 1){
			flash('success', 'Game stopped !');
		}else{
			flash('danger', 'Error when stopping game !');
		}
	}

	public function restart(){
		$current = $this->getCurrentGame();
		$updated_count = $this->database->update('games', array('active' => true, 'stop_time' => ''), array('game_id' => $current['game_id']));

		if($updated_count == 1){
			flash('success', 'Game restarted !');
		}else{
			flash('danger', 'Error when restarting game !');
		}
	}

	public function getSolutionProgress(){
		global $config;

		// Get current game
		$game = $this->getCurrentGame();
		$game_id = $game['game_id'];
		$bonus = $game['bonus'];

		// Count progression (accomplished missions)
		$progress_count = $this->database->count('history', 
			array('[>]players' => 'player_id'), 
			'result',
			array(
				'AND' => array(
						'game_id' => $game_id,
						'result[>=]' => 1
						)
			)
		);
		if($progress_count == false || $progress_count < 1){
			$progress_count = 0;
		}

		// Array containing indexes to show !
		$show_indexes = array_slice($config['final_phrase_shuffle'], 0, ($progress_count + $bonus));
		
		// Parse solution
		$letters_count = mb_strlen($config['final_phrase'], 'UTF-8');
		$letters_count_nospaces = 0;
		$letters = array();
		for ($i = 0; $i < $letters_count; $i++) {
			$letter = mb_substr($config['final_phrase'], $i, 1, 'UTF-8');
			$letters[$i] = array(
				'letter' => $letter,
				'space' => ($letter == ' '),
				'show' => in_array($i, $show_indexes)
			);
			if($letter != ' '){
				$letters_count_nospaces++;
			}
		}
		return array(
			'progress' => $progress_count,
			'total' => $letters_count_nospaces,
			'letters' => $letters
		);
	}

	private function addBonus($value){
		$results = $this->database->update('games', array('bonus['.(($value<0)?'-':'+').']' => abs($value)), array('ORDER' => array('game_id' => 'DESC'), 'LIMIT' => 1));
		// echo $this->database->last_query();
		// exit;
		if($results == false || count($results) == 0){
			return false;
		}else{
			return true;
		}
	}

	public function bonus(){
		$this->addBonus(1);
	}

	public function malus(){
		$this->addBonus(-1);
	}

	public function shuffle(){
		global $config;

		if(isset($_GET['sub'])){
			$sub = intval($_GET['sub']);
		}else{
			$sub = 10;
		}

		$letters_count = mb_strlen($config['final_phrase'], 'UTF-8');
		$letters = array();
		for ($i = 0; $i < $letters_count; $i++) {
			$letter = mb_substr($config['final_phrase'], $i, 1, 'UTF-8');
			if($letter != ' '){
				$letters[] = $i;
			}
		}


		if(count($letters) > $sub){
			$sub_letters = array_slice($letters, 0, (count($letters)-$sub));
			shuffle($sub_letters);
			$letters = array_merge($sub_letters, array_slice($letters, -$sub));
		}

		return '$config[\'final_phrase_shuffle\'] = array(' . implode($letters, ', ') . ');';
	}
}

?>