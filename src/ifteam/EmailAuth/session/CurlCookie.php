<?php

namespace ifteam\EmailAuth\session;

class CurlCookie {
	private $fileUrl;
	private $fileName;
	private static $instance = null;
	public function __construct($fileUrl, $fileName) {
		if (self::$instance !== null)
			return;
		
		$this->fileUrl = $fileUrl;
		$this->fileName = $fileName;
		self::$instance = $this;
	}
	public static function getInstance() {
		return self::$instance;
	}
	public function getCookiePath() {
		return $this->fileUrl;
	}
	public function getCookie() {
		return file_get_contents ( $this->fileUrl . $this->fileName );
	}
}

?>