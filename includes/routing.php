<?php
$routing['index'] = array(
	'url' => '',
	'hide_sidebar' => true,
);

$routing['login'] = array(
	'url' => 'login',
	'hide_sidebar' => true,
	'text' => 'Connexion',
);

$routing['logout'] = array(
	'url' => 'logout',
	'hide_sidebar' => true,
);

$routing['game'] = array(
	'url' => 'game',
	'hide_sidebar' => true,
	'text' => 'Jeu',
);

$routing['admin'] = array(
	'url' => 'admin',
	'hide_sidebar' => true,
	'text' => 'Administration',
);

$routing['screen'] = array(
	'url' => 'screen',
	'hide_sidebar' => true,
	'text' => 'Ecran'
);

$routing['video'] = array(
	'url' => 'video',
	'hide_sidebar' => true,
	'text' => 'Video'
);

$routing['letters'] = array(
	'url' => 'letters',
	'hide_sidebar' => true,
);

$routing['shuffle'] = array(
	'url' => 'shuffle',
	'hide_sidebar' => true,
);
// Examples
/*
$routing['global'] = array(
	'url' => 'global/detail/',
	'text' => 'Général',
);

$routing['atoma_detail_student'] = array(
	'url' => 'global/detail/',
	'text' => 'Fiche élève',
	'parent' => 'global',
	'icon' => 'icon-graduation',
	'hide_sidebar' => true,
);

$routing['admin_students_example'] = array(
	'url' => 'administration/students/example/',
	'download' => true,
	'location' => 'assets/students.csv',
	'filename' => 'example_students.csv',
	'req_permission' => 'admin',
	'hide_sidebar' => true,
);
*/


?>