<?php

class store extends slClass {
	private $cfg;
	function __construct() {
		$GLOBALS["_YP_STORE_OBJ"] = $this;
		$this->set("previousPage",$this->get("currentPage"));
		if (isset($_SERVER["REQUEST_URI"])) $this->set("currentPage",$_SERVER["REQUEST_URI"]);
		if (!defined("_YP_STORE_CONFIG_PATH")) define("_YP_STORE_CONFIG_PATH",realpath(dirname(__FILE__))."/config.php");
		
		$this->cfg = self::getConfig();
		
		if (!$this->getDefaults() && isset($this->cfg["defaults"])) {
			$this->setDefaults($this->cfg["defaults"]);
		}
	}

	public static function getConfig() {
		if (!defined("_YP_STORE_CONFIG_PATH")) define("_YP_STORE_CONFIG_PATH",realpath(dirname(__FILE__))."/config.php");
		if (!isset($GLOBALS["_STORE_CFG"])) {
			$GLOBALS["_STORE_CFG"] = require(_YP_STORE_CONFIG_PATH);
			if (isset($GLOBALS["_STORE_CFG"]["shippingTable"])) {
				$file = $GLOBALS["_STORE_CFG"]["shippingTable"];
				if (is_file($GLOBALS["_STORE_CFG"]["shippingTable"])) {
					$GLOBALS["_STORE_CFG"]["shippingTable"] = self::parseShippingTable($file);
				}
			}
		}
		return $GLOBALS["_STORE_CFG"];
	}

	private static function parseShippingTable($file) {
		$rv = array();
		if ($fp = fopen($file, "r")) {
			$s = 0; $var = -1;
			while (!feof($fp)) {
				if (($line = rtrim(fgets($fp))) !== "") {
					switch ($s) {
						case 0:
							$var ++;
							$rv[$var] = array("var"=>trim($line));
							$s = 1;
							break;
							
						case 1:
							if (preg_match('/^\s+/', $line)) {
								$line = preg_split('/(ADD|MULTIPLY|NEXT)/', $line, -1, PREG_SPLIT_DELIM_CAPTURE);
								$where = trim(array_shift($line));
								foreach ($line as &$v) {
									$v = trim($v);
									if ($v == "ELSE") $v = "true";
								}
								$rv[$var][] = array(
									"where"=>$where,
									"do"=>$line
								);
							} else {
								$s = 0;
							}
							
					}
				}
			}
			fclose($fp);
		}
		return $rv;
	}
	
	public static function redirectCheck() {
		$cfg = self::getConfig();
		if (isset($_POST) && is_array($_POST)) {
			foreach($cfg["links"] as $n=>$to) {
				if (setAndTrue($_POST,$n)) {
					header("Location: ".WWW_RELATIVE_BASE.$to);
					exit();
				}
			}
		}
	}
	
	public static function optCount($options) {
		$cnt = 0;
		foreach ($options as $opt) {
			if (searchify($opt["option"],'') != 'selectone') $cnt++;
		}
		return $cnt;
	}
	
	function getOrderId() {
		return $this->get("order-id");
	}
	
	function get($n, $def = null) {
		if (!isset($_SESSION["_STORE"])) $_SESSION["_STORE"] = array();
		return isset($_SESSION["_STORE"][$n]) ? $_SESSION["_STORE"][$n] : $def;
	}
	
	function set($n,$v) {
		$_SESSION["_STORE"][$n] = $v;
	}
	
	function getDefaults($n = false) {
		$v = $this->getUserData("defaults");
		if ($n) {
			return $v[$n];
		}
		return $v;
	}
	
	function setDefaults($v) {
		$this->setUserData("defaults",$v);
		$this->setUserData("update-cart-defaults",1);
	}
	
	function getUserData($n, $def = null) {
		return $GLOBALS["slSession"]->getUserData("store-".$n, $def);
	}
	
	function setUserData($n,$v) {
		return $GLOBALS["slSession"]->setUserData("store-".$n, $v);
	}
	
	public static function notify($hook, $ob) {
		$cfg = self::getConfig();
		$who = array();
		if ($GLOBALS["slSession"]->isLoggedIn()) {
			$who[] = $GLOBALS["slSession"]->user->get("name");
		} else $who[] = "guest";
		$who[] = $_SERVER["REMOTE_ADDR"];
		
		file_put_contents(SL_DATA_PATH."/log/".date("Y-m-d")."-store.txt", date("Y/m/d g:ia")." ".$hook."\n\t".implode(" ", $who)."\n\t".json_encode($ob)."\n\n",FILE_APPEND);
		$file = realpath(dirname(__FILE__))."/notify/".safeFile(array_shift(explode(".",$hook))).".php";
		if (is_file($file)) require($file);
	}
	
	public static function adminNotification($template,$subject,$emailAddresses,$ob,$ob2=null,$fromEmail = false,$fromName = false) {
		$file = realpath(dirname(__FILE__))."/notify/template/".safeFile($template).".php";
		if (is_file($file)) {
			ob_start();
			
			$cfg = self::getConfig();
			require(SL_WEB_PATH."/inc/store/template/email-header.php");
			require($file);
			require(SL_WEB_PATH."/inc/store/template/email-footer.php");
			
			store::sendEmail($emailAddresses,false,$subject,ob_get_clean(),$fromEmail,$fromName,true);
		}
	}
	
	public static function sendEmail($toEmail,$toName,$subject,$htmlBody,$fromEmail = false,$fromName = false, $isReplyTo = false) {
		
		$htmlBody = translateHTML($htmlBody);
		
		requireThirdParty("PHPMailer");
		$mail = new PHPMailer();
		
		$mail->CharSet = 'UTF-8';
		
		$cfg = self::getConfig();
		
		if ($isReplyTo) $mail->AddReplyTo($fromEmail, $fromName);
		if ($fromEmail === false || $isReplyTo == true) $fromEmail = $cfg["fromEmail"]["email"];
		if ($fromName === false) $fromName = $cfg["fromEmail"]["name"];
		
		if (!$isReplyTo) $mail->AddReplyTo($fromEmail, $fromName);
		$mail->SetFrom($fromEmail, $fromName);
		
		if (strpos($toEmail,",") !== false || strpos($toEmail,"<") !== false) {
			$toEmail = explode(",",$toEmail);
			foreach ($toEmail as $email) {
				if (strpos($email,"<") !== false) {
					$email = explode("<",$email);
					$mail->AddAddress(trim(array_shift(explode(">",$email[1]))),trim($email[0]));
				} else {
					$mail->AddAddress($email);
				}
			}
		} else {
			$mail->AddAddress($toEmail,$toName);
		}

		$mail->Subject = $subject;
		
		$mail->AltBody = htmlToText($htmlBody);
		
		$mail->MsgHTML($htmlBody);
		
		if (!$mail->Send()) {
			echo "<div class=\"error\">E-mail error: ".$mail->ErrorInfo."</div><pre><b>$subject</b><br /><br />".htmlToText($htmlBody)."</pre>";
			return false;
		}
		return true;		
	}
}
