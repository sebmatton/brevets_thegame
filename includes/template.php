<?php

require_once 'includes/constants.php';
require_once 'includes/Twig/Autoloader.php';
require_once 'includes/Twig/Extensions/Autoloader.php';

// Class used to compile template data and generate the views
class Template
{
	private $twig;
	private $template_data = array (
		'user' => array(),
		'current_page' => 'undefined',
		'errors' => array(
			'db_connect' => false
		),
		'flash' => 'Undefined',
		'config' => '',
		'routing' => array(),
		'isReadOnly' => false
	);

    function __construct($templates_dir, $login=false) {
		// Twig initialization
		{
			global $config, $routing;
			Twig_Autoloader::register();
			$loader = new Twig_Loader_Filesystem($templates_dir);
			$this->twig = new Twig_Environment($loader);
			$this->twig->addExtension(new Twig_Extensions_Extension_Intl());
			$this->twig->addExtension(new Twig_Extensions_Extension_Text());

			$jsonParser = new Twig_SimpleFilter('json_parse', function($str){
				return json_decode($str);
			});
			$this->twig->addFilter($jsonParser);

			if($login != false){
				$this->template_data['user'] = array(
					'username' => $login->getUserName(),
					'id' => $login->getUserID(),
					'token' => $login->getUserToken(),
					'permissions' => $login->getUserPermissions(),
				);
			}

			$this->template_data['layout'] = $config['template'];
			$this->template_data['routing'] = $routing;
			$this->template_data['config_permissions'] = $config['permissions'];
			$this->template_data['version'] = $config['version'];
			if(defined('DEV_ACTIVE')){
				$this->template_data['isDev'] = 'yes';
			}
		}
    }

	public function render($twig_file, $clean_flash=true, $noflash=false){
		$this->template_data['isReadOnly'] = isReadOnly(false);
		$this->template_data['current_page'] = $twig_file;
		if(!$noflash)
			$this->template_data['flash_items'] = (isset($_SESSION['flash'])?$_SESSION['flash']:array());
		echo $this->twig->render($twig_file . '.twig', $this->template_data); 
		if($clean_flash) cleanFlash();
	}

	public function addData($key, $value){
		if(is_array($key)){
			if(count($key) == 2){
				$this->template_data[$key[0]][$key[1]][] = $value;
			}
		}
		$this->template_data[$key][] = $value;
	}

	public function setData($key, $value){
		if(is_array($key)){
			if(count($key) == 2){
				$this->template_data[$key[0]][$key[1]] = $value;
			}
		}
		$this->template_data[$key] = $value;
	}
}

?>