<?php

namespace ifteam\EmailAuth\session;

use phpseclib\Crypt\RSA;
use phpseclib\Math\BigInteger;

class NaverSession {
	public $session;
	public $cookieFile;
	public $closed = false;
	public $logined = false;
	public function __construct($cookieFile) {
		$this->cookieFile = $cookieFile;
		$this->session = curl_init ();
		curl_setopt ( $this->session, CURLOPT_COOKIEJAR, $this->cookieFile );
		curl_setopt ( $this->session, CURLOPT_COOKIEFILE, $this->cookieFile );
	}
	public function __destruct() {
		$this->close ();
	}
	public function close() {
		if (! $this->closed) {
			$this->closed = true;
			curl_close ( $this->session );
		}
	}
	public function login($user_id, $user_pw, $do_finalize = true) {
		$keys = $this->getKeys ();
		
		$rsa = new RSA ();
		$rsa->modulus = new BigInteger ( $keys ['nvalue'], 16 );
		$rsa->exponent = new BigInteger ( $keys ['evalue'], 16 );
		$rsa->publicExponent = new BigInteger ( $keys ['evalue'], 16 );
		$rsa->k = strlen ( $rsa->modulus->toBytes () );
		$rsa->setEncryptionMode ( CRYPT_RSA_ENCRYPTION_PKCS1 );
		
		$rsa->loadKey ( $rsa->_convertPublicKey ( $rsa->modulus, $rsa->exponent ), CRYPT_RSA_PRIVATE_FORMAT_PKCS1 );
		
		$raw_data = $this->getLenChar ( $keys ['sessionkey'] ) . $keys ['sessionkey'] . $this->getLenChar ( $user_id ) . $user_id . $this->getLenChar ( $user_pw ) . $user_pw;
		$enc_data = $rsa->encrypt ( $raw_data );
		
		$login_url = 'https://nid.naver.com/nidlogin.login';
		$headers = [ 
				'User-Agent' => "Mozilla/5.0 (Windows NT 6.1; WOW64; rv:12.0) Gecko/20100101 Firefox/12.0 TAKOYAKI",
				'Accept' => 'text/html,application/xhtml+xml,' . 'application/xml;q=0.9,*/*;q=0.8',
				'Accept-Language' => 'ko-KR,ko;q=0.8,en-US;q=0.5,en;q=0.3',
				'Accept-Encoding' => 'gzip, deflate',
				'Referer' => 'http://www.naver.com/',
				'Content-Type' => 'application/x-www-form-urlencoded' 
		];
		
		$params = "enctp" . "=" . "1";
		$params .= "&encpw" . "=" . bin2hex ( $enc_data );
		$params .= "&encnm" . "=" . $keys ['keyname'];
		$params .= "&svctype" . "=" . "0";
		$params .= "&url=http://www.naver.com/&enc_url=http%3A%2F%2Fwww.naver.com%2F&postDataKey=&nvlong=&saveID=&smart_level=undefined";
		$params .= "&id" . "=" . "";
		$params .= "&pw" . "=" . "";
		$resp = $this->postURL ( $login_url, $params, 10, $headers );
		
		// echo "\n\nheader\n" . $resp ["header"] . "\n";
		// echo "\n\nbody\n" . $resp ["body"] . "\n";
		$this->logined = true;
		echo "\n로그인에 성공했습니다\n";
		if (strpos ( $resp ["body"], "새로운" )) { // NEW DEVICE CHECK
			$key = $this->getKey ( $resp ["body"] );
			
			$result = $this->Accept ( $key );
			$exp = explode ( 'Set-Cookie: ', $result );
			$NID_SES3 = explode ( 'Set-Cookie: NID_AUT=', $result );
			$work = $NID_SES3 [1];
			$NID_SES2 = explode ( ';', $work );
			$NID_SES = $NID_SES2 [0];
			$NID_AUT3 = explode ( 'Set-Cookie: NID_AUT=', $result );
			$work2 = $NID_AUT3 [1];
			$NID_AUT2 = explode ( ';', $work2 );
			$NID_AUT = $NID_AUT2 [0];
			$this->logined = true;
			echo "\n새장치 등록에 성공했습니다\n";
		} elseif (strpos ( $resp ["body"], "않습니다" )) {
			$this->logined = false;
		}
		
		if ($do_finalize and strpos ( $resp ["body"], "https://nid.naver.com/login/sso/finalize.nhn" )) {
			$finalize_url = explode ( "replace(\"", $resp ["body"], 2 )[1];
			$finalize_url = explode ( "\")", $finalize_url, 2 )[0];
			// echo "finalize_url: " . $finalize_url . "\n";
			
			$headers = [ 
					'User-Agent' => "Mozilla/5.0 (Windows NT 6.1; WOW64; rv:12.0) Gecko/20100101 Firefox/12.0 TAKOYAKI",
					'Accept' => 'text/html,application/xhtml+xml,' . 'application/xml;q=0.9,*/*;q=0.8',
					'Accept-Language' => 'ko-KR,ko;q=0.8,en-US;q=0.5,en;q=0.3',
					'Accept-Encoding' => 'gzip, deflate',
					'Referer' => 'https://nid.naver.com/nidlogin.login' 
			];
			$resp = $this->postURL ( $finalize_url, $headers );
			echo "파이널라이즈에 성공했습니다\n";
			// var_dump ( $resp );
		}
	}
	public function logout() {
		$this->getURL ( "http://nid.naver.com/nidlogin.logout" );
	}
	public function getKeys() {
		$fetch_url = "http://static.nid.naver.com/loginv3/js/keys_js.nhn";
		
		$headers = [ 
				'User-Agent' => "Mozilla/5.0 (Windows NT 6.1; WOW64; rv:12.0) Gecko/20100101 Firefox/12.0 TAKOYAKI",
				'Accept' => 'text/html,application/xhtml+xml,' . 'application/xml;q=0.9,*/*;q=0.8',
				'Accept-Language' => 'ko-KR,ko;q=0.8,en-US;q=0.5,en;q=0.3',
				'Accept-Encoding' => 'gzip, deflate',
				'Referer' => 'http://www.naver.com/',
				'Content-Type' => 'application/x-www-form-urlencoded' 
		];
		
		$content = $this->getURL ( 'http://static.nid.naver.com/enclogin/keys.nhn', 10, $headers );
		$key_pattern = explode ( ",", $content ['body'] );
		
		$keys = array ();
		$keys ["sessionkey"] = $key_pattern [0];
		$keys ["keyname"] = $key_pattern [1];
		$keys ["nvalue"] = $key_pattern [2];
		$keys ["evalue"] = $key_pattern [3];
		
		return $keys;
	}
	public function getKey($html) {
		$doc = new \DOMDocument ();
		@$doc->loadHTML ( $html );
		$items = $doc->getElementsByTagName ( 'input' );
		foreach ( $items as $tag ) { // FIND VALUE AS FOREACH FUNC()
			$name = $tag->getAttribute ( 'name' );
			if ($name == "key") {
				$value = $tag->getAttribute ( 'value' );
			}
		}
		return $value;
	}
	public function Accept($authkey) {
		$content = "regyn=N&nvlong=&mode=device&key=$authkey&enctp=2&encpw=&encnm=&svctype=0&svc=&viewtype=&locale=ko_KR&postDataKey=&smart_LEVEL=1&logintp=&url=http%3A%2F%2Fwww.naver.com%2F&mode=&secret_yn=&pre_id=&resp=&exp=&ru=";
		curl_setopt ( $this->session, CURLOPT_URL, "https://nid.naver.com/nidlogin.login?svctype=0" );
		curl_setopt ( $this->session, CURLOPT_HEADER, 1 );
		curl_setopt ( $this->session, CURLOPT_HTTPHEADER, [ 
				'User-Agent' => "Mozilla/5.0 (Windows NT 6.1; WOW64; rv:12.0) Gecko/20100101 Firefox/12.0 TAKOYAKI",
				'Accept' => 'text/html,application/xhtml+xml,' . 'application/xml;q=0.9,*/*;q=0.8',
				'Accept-Language' => 'ko-KR,ko;q=0.8,en-US;q=0.5,en;q=0.3',
				'Accept-Encoding' => 'gzip, deflate',
				'Referer' => 'http://www.naver.com/',
				'Content-Type' => 'application/x-www-form-urlencoded' 
		] );
		curl_setopt ( $this->session, CURLOPT_POSTFIELDS, $content );
		curl_setopt ( $this->session, CURLOPT_POST, 1 );
		curl_setopt ( $this->session, CURLOPT_RETURNTRANSFER, true );
		curl_setopt ( $this->session, CURLOPT_COOKIEJAR, $this->cookieFile );
		curl_setopt ( $this->session, CURLOPT_COOKIEFILE, $this->cookieFile );
		$response = curl_exec ( $this->session );
		return $response;
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
		$result = explode ( "\r\n\r\n", curl_exec ( $this->session ), 2 );
		
		$header = isset ( $result [0] ) ? $result [0] : '';
		$content = isset ( $result [1] ) ? $result [1] : '';
		
		return array (
				'body' => $content,
				'header' => $header 
		);
	}
	public function getLenChar($texts) {
		return chr ( strlen ( $texts ) );
	}
}

?>