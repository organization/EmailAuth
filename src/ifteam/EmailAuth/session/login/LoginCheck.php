<?php

namespace ifteam\EmailAuth\session\login;

use ifteam\EmailAuth\session\NaverSession;

class LoginCheck {
	/**
	 *
	 * @var NaverSession
	 */
	private $curl;
	private $response = null;
	public function __construct(&$curl) {
		$this->curl = $curl;
	}
	public function check() {
		$this->response = $this->curl->getURL ( "http://cafe.naver.com/LoginCheck.nhn?m=check" )["body"];
		return (strpos ( $this->response, "LOGIN" )) ? true : false;
	}
	public function getResponse(){
		return $this->response;
	}
}
?>