<?php

namespace ifteam\EmailAuth\session\login;

use ifteam\EmailAuth\session\NaverSession;

class AbuserCheck {
	/**
	 *
	 * @var NaverSession
	 */
	private $curl;
	private $checkData;
	public function __construct(&$curl, $checkData) {
		$this->curl = $curl;
		$this->checkData = $checkData;
	}
	public function check() {
		//{"result":{"abuser":false,"csImageUrl":"","csKey":""},"isSuccess":true}
		$response = $this->curl->postURL ( "http://cafe.naver.com/AbuserCheckAjax.nhn", $this->checkData )["body"];
		return (strpos ( $response, "isSuccess\":true" )) ? true : false;
	}
}

?>