<?php

namespace ifteam\EmailAuth\session;

class CurlSession {
	private $session;
	private $cookieFile;
	private $closed = true;
	public function __construct($cookieFile) {
		$this->cookieFile = $cookieFile;
	}
	public function __destruct() {
		$this->closeSession ();
	}
	public function openSession() {
		if ($this->closed) {
			$this->session = curl_init ();
			curl_setopt ( $this->session, CURLOPT_COOKIEJAR, $this->cookieFile );
			curl_setopt ( $this->session, CURLOPT_COOKIEFILE, $this->cookieFile );
		}
	}
	public function getSession() {
		return $this->session;
	}
	public function isClosed() {
		return $closed;
	}
	public function closeSession() {
		if (! $this->closed) {
			$this->closed = true;
			curl_close ( $this->session );
		}
	}
	/**
	 * GETs an URL using cURL
	 *
	 * @param
	 *        	$page
	 * @param int $timeout
	 *        	default 10
	 * @param array $extraHeaders        	
	 *
	 * @return bool|mixed
	 */
	public function getURL($page, $timeout = 10, array $extraHeaders = []) {
		if ($extraHeaders == null) {
			$extraHeaders = [ 
					'User-Agent' => "Mozilla/5.0 (Windows NT 6.1; WOW64; rv:12.0) Gecko/20100101 Firefox/12.0 TAKOYAKI",
					'Accept' => 'text/html,application/xhtml+xml,' . 'application/xml;q=0.9,*/*;q=0.8',
					'Accept-Language' => 'ko-KR,ko;q=0.8,en-US;q=0.5,en;q=0.3',
					'Accept-Encoding' => 'gzip, deflate',
					'Referer' => 'http://www.naver.com/',
					'Content-Type' => 'application/x-www-form-urlencoded' 
			];
		}
		
		curl_setopt ( $this->session, CURLOPT_URL, $page );
		curl_setopt ( $this->session, CURLOPT_HTTPHEADER, $extraHeaders );
		curl_setopt ( $this->session, CURLOPT_AUTOREFERER, true );
		curl_setopt ( $this->session, CURLOPT_SSL_VERIFYPEER, false );
		curl_setopt ( $this->session, CURLOPT_SSL_VERIFYHOST, 2 );
		curl_setopt ( $this->session, CURLOPT_FORBID_REUSE, 1 );
		curl_setopt ( $this->session, CURLOPT_FRESH_CONNECT, 1 );
		curl_setopt ( $this->session, CURLOPT_FOLLOWLOCATION, true );
		curl_setopt ( $this->session, CURLOPT_RETURNTRANSFER, true );
		curl_setopt ( $this->session, CURLOPT_CONNECTTIMEOUT, ( int ) $timeout );
		curl_setopt ( $this->session, CURLOPT_TIMEOUT, ( int ) $timeout );
		curl_setopt ( $this->session, CURLOPT_HEADER, 1 );
		curl_setopt ( $this->session, CURLOPT_COOKIEJAR, $this->cookieFile );
		curl_setopt ( $this->session, CURLOPT_COOKIEFILE, $this->cookieFile );
		//$result = mb_convert_encoding ( curl_exec ( $this->session ), "cp949" );
		//$result = explode ( "\r\n\r\n", $result, 2 );
		$result = explode ( "\r\n\r\n", curl_exec ( $this->session ), 2 );
		
		$header = isset ( $result [0] ) ? $result [0] : '';
		$content = isset ( $result [1] ) ? $result [1] : '';
		
		return array (
				'body' => $content,
				'header' => $header 
		);
	}
	/**
	 * POSTs data to an URL
	 *
	 * @param
	 *        	$page
	 * @param array|string $args        	
	 * @param int $timeout        	
	 * @param array $extraHeaders        	
	 *
	 * @return bool|mixed
	 */
	public function postURL($page, $args, $timeout = 10, array $extraHeaders = []) {
		if ($extraHeaders == null) {
			$extraHeaders = [ 
					'User-Agent' => "Mozilla/5.0 (Windows NT 6.1; WOW64; rv:12.0) Gecko/20100101 Firefox/12.0 TAKOYAKI",
					'Accept' => 'text/html,application/xhtml+xml,' . 'application/xml;q=0.9,*/*;q=0.8',
					'Accept-Language' => 'ko-KR,ko;q=0.8,en-US;q=0.5,en;q=0.3',
					'Accept-Encoding' => 'gzip, deflate',
					'Referer' => 'http://www.naver.com/',
					'Content-Type' => 'application/x-www-form-urlencoded' 
			];
		}
		
		curl_setopt ( $this->session, CURLOPT_URL, $page );
		curl_setopt ( $this->session, CURLOPT_POST, 1 );
		curl_setopt ( $this->session, CURLOPT_SSL_VERIFYPEER, false );
		curl_setopt ( $this->session, CURLOPT_SSL_VERIFYHOST, 0 );
		curl_setopt ( $this->session, CURLOPT_FORBID_REUSE, 1 );
		curl_setopt ( $this->session, CURLOPT_FRESH_CONNECT, 1 );
		curl_setopt ( $this->session, CURLOPT_POSTFIELDS, $args );
		curl_setopt ( $this->session, CURLOPT_AUTOREFERER, true );
		curl_setopt ( $this->session, CURLOPT_FOLLOWLOCATION, true );
		curl_setopt ( $this->session, CURLOPT_HTTPHEADER, $extraHeaders );
		curl_setopt ( $this->session, CURLOPT_RETURNTRANSFER, true );
		curl_setopt ( $this->session, CURLOPT_CONNECTTIMEOUT, ( int ) $timeout );
		curl_setopt ( $this->session, CURLOPT_TIMEOUT, ( int ) $timeout );
		curl_setopt ( $this->session, CURLOPT_HEADER, 1 );
		curl_setopt ( $this->session, CURLOPT_COOKIEJAR, $this->cookieFile );
		curl_setopt ( $this->session, CURLOPT_COOKIEFILE, $this->cookieFile );
		//$result = mb_convert_encoding ( curl_exec ( $this->session ), "cp949" );
		//$result = explode ( "\r\n\r\n", $result, 2 );
		$result = explode ( "\r\n\r\n", curl_exec ( $this->session ), 2 );
		
		$header = isset ( $result [0] ) ? $result [0] : '';
		$content = isset ( $result [1] ) ? $result [1] : '';
		
		return array (
				'body' => $content,
				'header' => $header 
		);
	}
}
?>