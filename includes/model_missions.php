<?php

class Missions
{
	private $database;

	function __construct($db) {
		$this->database = $db;
	}

	public function getMissions(){
		$results = $this->database->select('missions', array(
			'[>]players' => array('busy' => 'player_id'),
			'[>]users' => array('players.fk_user_id' => 'user_id')
		), '*');

		if($results == false || count($results) < 1){
			return array();
		}else{
			return $results;
		}
	}

	public function getPlayerMissions($player_id){
		$results = $this->database->select('history', 
			array('[>]missions' => 'mission_id'), '*', 
			array(
				'player_id' => $player_id, 
				'ORDER' => array('start_time' => 'DESC')
			)
		);
		if($results == false || count($results) < 1){
			return array();
		}else{
			return $results;
		}
	}

	public function getNewMission($player_id){
		// Check if already running mission
		$results = $this->database->select('history', 'history_id', 
			array(
				'AND' => array(
					'player_id' => $player_id, 
					'result' => -1
				)
			)
		);
		if($results == false || count($results) < 1){
			// Create new mission
			$prev_missions = $this->database->select('history', 
				array('[>]missions' => 'mission_id'), array('mission_id', 'sameref'), 
				array(
					'player_id' => $player_id, 
					'ORDER' => array('start_time' => 'DESC')
				)
			);
			$prev_missions_ids = array();
			$prev_missions_samerefs = array();
			if($prev_missions != false && count($prev_missions) > 0){
				foreach ($prev_missions as $r) {
					$prev_missions_ids[] = $r['mission_id'];
					if($r['sameref'] != '')
						$prev_missions_samerefs[] = $r['sameref'];
				}
			}

			// Try to receive a challenge if
			// 	- first mission
			//  - if 2nd mission and first was not a challenge (same as 3rd)
			//  - if 2x more questions than challenges => T>3C

			$new_mission = -1;

			if(count($prev_missions_ids) == 0 || count($prev_missions_ids) >= 3*count($prev_missions_samerefs)){
				 'DEBUG1';
				$and_array = array();
				$and_array['busy'] = 0;
				$and_array['type'] = 1;
				if(count($prev_missions_ids)>0)
					$and_array['mission_id[!]'] = $prev_missions_ids;
				if(count($prev_missions_samerefs)>0)
					$and_array['sameref[!]'] = $prev_missions_samerefs;

				$new_mission = $this->database->select('missions', 'mission_id', array(
					'AND' => $and_array
				));
				
				// Retrieve random new mission if possible
				($new_mission);
				if($new_mission != false && count($new_mission)>0){
					$new_mission = $new_mission[rand(0, count($new_mission)-1)];
				}else{
					$new_mission = -1;
				}
			}
			// If 1st request was not applicable or could not give a result, take any mission randomely !
			if($new_mission == -1){
				$and_array = array();
				$and_array['busy'] = 0;
				if(count($prev_missions_ids)>0)
					$and_array['mission_id[!]'] = $prev_missions_ids;
				if(count($prev_missions_samerefs)>0)
					$and_array['sameref[!]'] = $prev_missions_samerefs;

				$new_mission = $this->database->select('missions', 'mission_id', array(
					'AND' => $and_array
				));


				// Retrieve random new mission if possible
				if($new_mission != false && count($new_mission)>0){
					$new_mission = $new_mission[rand(0, count($new_mission)-1)];
				}else{
					flash('danger', 'Impossible de générer une nouvelle mission ! (step2)');
					return;
				}
			}

			// Reserve mission !
			$history_id = $this->database->insert('history', array('player_id' => $player_id, 'mission_id' => $new_mission, 'result' => '-1'));
			if($history_id == false || $history_id <= 0){
				flash('danger', 'Erreur lors de la génération de la mission ('.$new_mission.')');
				return;
			}
			$update_mission = $this->database->update('missions', array('busy' => $player_id), array('mission_id' => $new_mission));
			if($update_mission == false){
				flash('danger', 'Erreur lors de la mise à jour de la mission');
			}
			return true;
		}else{
			// Running mission already exist !
			return true;
		}
	}

	public function validateMission($player_id, $solution){
		global $config; 

		if($solution < 0 || $solution == ''){
			flash('warning', 'Aucun code entré !');
			return;
		}
		// Retrieve current mission
		$current = $this->database->get('history', 
			array('[>]missions' => 'mission_id'), '*', 
			array(
				'AND' => array(
					'player_id' => $player_id, 
					'result' => -1
				)
			)
		);
		if($current == false || count($current) <=1){
			flash('danger', 'Impossible de récupérer la mission en cours !');
			return false;
		}
		// Check timing !
		print_r($current);
		$start = strtotime($current['start_time']);
		$now = strtotime(date('c'));

		if($now - $start < $config['mission_min_time']){
			flash('danger', 'Veuillez attendre '.($config['mission_min_time'] - $now + $start).'s avant de valider la mission.');
			return false;
		}

		// Mission is success if (solution modulo (map_point + 1)) == 0. Example: 9 % (2 + 1) = 0 !
		$success = (($solution % ($current['map_point'] + 1)) == 0);

		// Update history
		$update_history = $this->database->update('history', 
			array('result' => ($success?'1':'-2')),
			array('history_id' => $current['history_id'])
		);
		if($update_history == false || $update_history <= 0){
			flash('danger', 'Impossible de valider la mission en cours !');
			return false;
		}

		// Update missions availability
		$update_mission = $this->database->update('missions',
			array('busy' => 0),
			array('busy' => $player_id)
		);
		if($update_mission == false || $update_mission <= 0){
			flash('danger', 'Impossible de mettre à jour la mission !');
			return false;
		}

		if($success){
			flash('primary', '<center><strong>Super, la mission est réussie !</strong></center>');
		}else{
			flash('primary', '<center><strong>Malheureusement c\'est raté !</strong></center>');
		}
		return true;
		// var_dump($current);
		// echo $success?'yes':'no';
		// echo $player_id . ' - ' . $solution;
		// exit;
	}
}

?>