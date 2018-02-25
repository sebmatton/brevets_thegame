<?php
function redirect($page, $get=array()){
	global $routing;
	// echo 'location: '.BASE_URL.$routing[$page]['url'];exit;
	if(is_array($get) && count($get) > 0){
		header('location: '.BASE_URL.$routing[$page]['url'] . '?' . http_build_query($get));
	}else{
		header('location: '.BASE_URL.$routing[$page]['url']);
	}
}

function isPage($page){
	global $routing;
	global $login;
	$request_uri = substr($_SERVER['REQUEST_URI'], 1);
	$get_post = strpos($request_uri, '?');
	if($get_post !== false){
		$request_uri = substr($request_uri, 0, $get_post);
	}

	if(preg_match('/^'.str_replace('/','\/', SUBDIR_URL).'(.*)/', $request_uri, $matches)){
		$request_uri = $matches[1];
	}
	// echo $request_uri;exit;
	foreach ($routing as $route_key => $route_value) {
		if($route_value['url'] == $request_uri){
			if($route_key == $page){
				if(isset($route_value['req_permission']) && !$login->hasPermissions($route_value['req_permission'])){
					global $template;
					$template->render('error_permission');
					exit;
				}else{
					return true;
				}
			}
		}
	}
	return false;
}

function isReadOnly($echo=true) {
	global $login, $config;
	if($login->hasPermissions('admin')){
		return false;
	}else{
		if($echo){
			flash('danger', 'Vous n\'avez pas les droits nécessaires pour opérer à des modifications !');
		}
		return true;
	}
}

function cleanFlash(){
	$_SESSION['flash'] = array();
}

function redirectHttps($https_enable = true){
	if($https_enable){
		if(empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == "off"){
		    $redirect = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		    header('HTTP/1.1 301 Moved Permanently');
		    header('Location: ' . $redirect);
		    exit();
		}
	}else{
		if(isset($_SERVER['HTTPS'])){
		    $redirect = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		    header('HTTP/1.1 301 Moved Permanently');
		    header('Location: ' . $redirect);
		    exit();
		}
	}
}

function getDbConfig(){
	return array(
	    'database_type' => DATABASE_TYPE,
	    'database_name' => DATABASE_NAME,
	    'server' => DATABASE_SERVER,
	    'username' => DATABASE_USERNAME,
	    'password' => DATABASE_PASSWORD,
	    'charset' => 'utf8',
	    'prefix' => DATABASE_PREFIX
	);
}

function hasPost($post, $value = false){
	if($value != false){
		return isset($_POST[$post]) && !empty($_POST[$post]) && $_POST[$post] == $value;
	}
	return isset($_POST[$post]) && !empty($_POST[$post]);
}

function hasGet($get){
	return isset($_GET[$get]) && !empty($_GET[$get]);
}

function isGet($get, $value){
	if(hasGet($get)){
		return $_GET[$get] == $value;
	}
}

function isAction($post){
	return isset($_POST['action']) && $_POST['action'] == $post;
}

function flash($type, $text){
	$_SESSION['flash'][] = array('type' => $type, 'text' => $text);
}

?>