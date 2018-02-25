<?php

$config['version'] = file_get_contents('.version');
if(!$config['version']) $config['version'] = 'Undefined version';

$config['template'] = array(
	'site_name' => 'Licorne',
	'base_url' => BASE_URL,
);

$config['upload'] = array(
	'directory' => 'files_input/',
	'filesize' => 512000,
);

$config['permissions'] = array(
	'megatest' => -1,
	'superadmin' => 0,
	'admin' => 1,
	'player' => 5,
	'disabled' => 9,
);

$config['permissions_text'] = array(
	$config['permissions']['megatest'] 		=> array('title' => 'MegaTester', 			'description' => 'Test User'),
	$config['permissions']['superadmin'] 	=> array('title' => 'Super-Administrateur', 'description' => 'Full access'),
	$config['permissions']['admin'] 		=> array('title' => 'Administrateur', 		'description' => 'Lecture, modification et administration'),
	$config['permissions']['player'] 		=> array('title' => 'Joueur', 				'description' => 'Joueur'),
	$config['permissions']['disabled'] 		=> array('title' => 'Désactivé', 			'description' => 'Compte désactivé'),
);

$config['final_phrase'] = "Le lapin de Pâques est enfermé dans une cachette secrète protégée avec un cadenas. Le code du cadenas est le 3320. La pièce où se trouve le lapin est situé au rez-de chaussée, il s'agit de la classe E03.";
$config['final_phrase_shuffle'] = array(172, 23, 28, 68, 107, 46, 152, 98, 132, 153, 166, 36, 32, 183, 147, 54, 143, 66, 109, 173, 162, 67, 179, 106, 76, 77, 119, 128, 135, 125, 99, 34, 44, 96, 42, 20, 184, 164, 115, 75, 110, 1, 163, 29, 112, 182, 33, 116, 79, 13, 80, 78, 133, 168, 111, 174, 87, 41, 58, 127, 83, 60, 88, 61, 3, 59, 102, 100, 15, 9, 150, 57, 170, 160, 0, 97, 140, 187, 142, 38, 193, 51, 121, 24, 134, 40, 43, 14, 137, 49, 120, 176, 144, 124, 86, 156, 91, 92, 157, 26, 81, 118, 69, 113, 17, 151, 55, 63, 171, 74, 16, 64, 138, 27, 45, 89, 62, 131, 5, 53, 25, 122, 95, 10, 103, 177, 47, 84, 180, 19, 141, 12, 6, 4, 7, 148, 154, 192, 94, 31, 71, 37, 189, 167, 159, 50, 161, 21, 186, 146, 104, 72, 181, 130, 190, 169, 52, 194, 195, 196, 197, 199, 200, 201, 202);

$config['mission_min_time'] = 60; // Min 60s between start of mission and validation
?>