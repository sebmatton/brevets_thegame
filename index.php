<?php

// Jeu brevets Mars 2018
//
// Author : Sébastien Matton (seb.matton@gmail.com)
// Date : Feb. 2018
//

// Init
ini_set('display_errors', 'On');
error_reporting(E_ALL | E_STRICT);
date_default_timezone_set('Europe/Brussels');
session_start();

// Requires
{
	require_once 'config.php';
	require_once 'includes/constants.php';
	require_once 'includes/routing.php';
	require_once 'includes/functions.php';
	require_once 'includes/login.php';
	require_once 'includes/template.php';
	require_once 'includes/Medoo/medoo.php';
	require_once 'includes/GUMP/gump.class.php';
	require_once 'includes/lessphp/lessc.inc.php';
	// Models
	require_once 'includes/model_users.php';
	require_once 'includes/model_games.php';
	require_once 'includes/model_missions.php';
	require_once 'includes/model_players.php';
	require_once 'includes/model_videos.php';
}

// Redirect to https if needed
redirectHttps(USE_HTTPS);

// Medoo initialization
try {
	$db = new medoo(getDbConfig());
}catch(Exception $e) {
	echo 'Database connection error.';
	exit;
}

// Less initialisation
$less = new lessc;
try {
	$less->checkedCompile("assets/css/style.less", "assets/css/style.css");
	$less->checkedCompile("assets/css/screen.less", "assets/css/screen.css");
} catch (exception $e) {
	echo "fatal error: " . $e->getMessage();
}

// Login initialisation
$login = new Login($db);

// Models initialisation
$users = new Users($db);
$game = new Games($db);
$missions = new Missions($db);
$players = new Players($db);
$videos = new Videos($db);

// Template initialization
$template = new Template('templates/', $login);

// Routing
	// If user is not connected
	if(isPage('screen')){
		$current_game = $game->getCurrentGame();
		$template->setData('game', $current_game);
		$template->setData('solutionprogress', $game->getSolutionProgress());
		$template->render('screen', false, true);
		return;
	}elseif(isPage('video')){
		$template->setData('video', $videos->getCurrent());
		$template->render('video', false, true);
		return;
	}elseif(isPage('letters')){
		$current_game = $game->getCurrentGame();
		$template->setData('game', $current_game);
		$template->setData('solutionprogress', $game->getSolutionProgress());
		$template->render('letters', false, true);
		return;
	}
	if(!$login->isConnected()){
		$login->tryAutoLogin();
		if(!isPage('login')){
			redirect('login');	// Redirect to login page
			return;
		}else{
			if($login->isFormPosted()){
				$check = $login->checkPost();
				if($check === true){
					redirect('atoma_dashboard');
				}else{
					$template->setData('errors', $check);
				}
			}
			$template->setData('token', $login->getUserToken());
			$template->render('login'); // Render login page
			return;
		}
	// If user is connected
	}else{
		if(isPage('logout')){
			$login->logout();
			redirect('index');
			return;
		}

		if($login->getUserPermissions() == $config['permissions']['player']){
			// Init game and register player
			$current_game = $game->getCurrentGame();
			$player_id = $players->registerPlayer($current_game['game_id']);

			if(isPage('game')){
				if(hasGet('action')){
					if(isGet('action', 'new_mission')){
						$missions->getNewMission($player_id);
					}elseif(isGet('action', 'validate_mission') && isset($_POST['solution'])){
						$missions->validateMission($player_id, intval($_POST['solution']));
					}
					redirect('game');
					return;
				}
				$missions_list = $missions->getPlayerMissions($player_id);
				$template->setData('missions_list', $missions_list);
				$template->render('game');
			}else{
				redirect('game');
				return;
			}
		}else{
			if(isPage('admin')){
				if(hasGet('action')){
					if(isGet('action', 'game_start')){
						$game->start();
					}elseif(isGet('action', 'game_stop')){
						$game->stop();
					}elseif(isGet('action', 'game_restart')){
						$game->restart();
					}elseif(isGet('action', 'game_bonus')){
						$game->bonus();
					}elseif(isGet('action', 'game_malus')){
						$game->malus();
					}elseif(isGet('action', 'set_video') && hasGet('video')){
						$current_game = $game->getCurrentGame();
						$videos->setVideo($_GET['video'], $current_game['game_id']);
					}
					redirect('admin');
					return;
				}
				$current_game = $game->getCurrentGame();
				$template->setData('videos', $videos->getVideos());
				$template->setData('solutionprogress', $game->getSolutionProgress());
				$template->setData('game', $current_game);
				$template->setData('missions', $missions->getMissions());
				$template->setData('players', $players->getPlayers($current_game['game_id']));
				$template->render('admin');
				return;
			}elseif(isPage('shuffle')){		
				echo $game->shuffle();
				exit;
			}else{
				redirect('admin');
				return;
			}
		}
	}