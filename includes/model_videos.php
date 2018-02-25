<?php

class Videos
{
	private $database;
	private $dir = 'assets/videos/';
	function __construct($db) {
		$this->database = $db;
	}

	public function getVideos(){
		if(!is_dir($this->dir)){
			flash('danger', 'Video directory does not exist.');
			return false;
		}
		$files = scandir($this->dir);

		$videos = array();
		foreach ($files as $file) {
			if(substr($file, -4) == '.mp4'){
				$videos[] = $file;
			}
		}
		return $videos;
	}

	public function setVideo($video, $game_id){
		$update = $this->database->update('games', array('video' => $video), array('game_id' => $game_id));
		if($update == false || $update <= 0){
			flash('danger', 'Error when setting video');
			return;
		}
		flash('success', 'Video updated!');
	}

	public function getCurrent(){
		$get = $this->database->get('games', 'video', array('ORDER' => array('game_id' => 'DESC')));
		if($get == false){
			$videos = $this->getVideos();
			return $videos[0];
		}else{
			return $get;
		}
	}
}

?>