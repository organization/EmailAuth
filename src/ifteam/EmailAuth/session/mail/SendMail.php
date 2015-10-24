<?php

namespace ifteam\EmailAuth\session\mail;

use ifteam\EmailAuth\session\NaverSession;

class SendMail {
	/**
	 *
	 * @var NaverSession
	 */
	private $curl;
	public function __construct(&$curl) {
		if (self::$instance !== null)
			return;
		$this->curl = $curl;
	}
	public function write($targetEmailName, $targetDomain, $id, $nickName, $subject, $html) {
		$aId = $this->getAId ( $id );
		$postData = $this->getPostData ( $targetEmailName, $targetDomain, $id, $nickName, $subject, $html, $aid );
		$page = "http://mail.naver.com/json/write/send/?aId=" . $aId;
		$this->curl->postURL ( $page, $postData );
		echo "TEST SEND COMPLETE\n";
	}
	public function getAId($id) {
		echo "getAId()\n";
		$html = $this->curl->postURL ( "http://mail.naver.com/json/write", "orderType%3Dnew%26lists%3D%26charset%3D%26u%3D" . $id ) ["body"];
		file_put_contents ( "c:\testcode.txt", $html );
		echo $html;
	}
	public function getPostData($targetEmailName, $targetDomain, $id, $nickName, $subject, $html, $aid) {
		$postData = "&aCount=0";
		$postData .= "&aSize=0";
		$postData .= "senderName%3D";
		
		$postData .= $nickName;
		
		$postData .= "%26to%3D";
		$postData .= $targetEmailName . "%2540" . $targetDomain;
		
		$postData .= "%253B%26cc%3D%26bcc%3D%26subject%3D";
		$postData .= $subject;
		
		$postData .= "%26body%3D%253Chtml%253E%253Chead%253E%253Cstyle%253Ep%257B";
		$postData .= "margin-top%253A0px%253Bmargin-bottom%253A0px%253B%257D%253C%252F";
		$postData .= "style%253E%253C%252Fhead%253E%253Cbody%253E%253Cdiv%2520style";
		$postData .= "%253D%2522font-size%253A10pt%253B%2520font-family%253AGulim%253B%2522%253E%253Cp%253E";
		$postData .= $html;
		
		$postData .= "%253C%252Fp%253E%253C%252Fdiv%253E%253C%252Fbody%253E%253C%252Fhtml%253E%26rawBody%3D%253Cp%253E";
		$postData .= $html;
		
		$postData .= "%253C%252Fp%253E%26contentType%3Dhtml%26sendSeparately%3Dfalse%26saveSentBox%3Dtrue%26type%3Dnew%26fromMe%3D0%26attachID%3D";
		$postData .= $aId;
		
		$postData .= "%26reserveDate%3D%26reserveGMT%3D%26reserveTime%3D%26calendarVal%3D%26";
		$postData .= "autoSaveMailSN%3D19016%26attachCount%3D0%26attachSize%3D0%26bigfile%3D";
		$postData .= "0%26sessionID%3D%26seqNums%3D%26priority%3D0%26ndriveFileInfos%3D%26";
		$postData .= "lists%3D%26serviceID%3D%26bigfileCount%3D0%26uploaderType%3Dhtml5%26bigfileNotice%3D";
		$postData .= "false%26bigfileHost%3Dbigfile.mail.naver.com%26u%3D";
		$postData .= $id;
		
		return $postData;
	}
}

?>