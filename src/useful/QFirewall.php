<?php

class QFirewall 
{
	/**
	 * @var string
	 */
	# public $ip;
	
	/*
	public static $Limit_mins_01 = 60;
	public static $Limit_mins_05 = 150;
	public static $Limit_mins_10 = 300;
	public static $Limit_mins_30 = 900;
	public static $Limit_hour_01 = 1500;
	public static $Limit_hour_06 = 4500;
	public static $Limit_hour_12 = 5000;
	public static $Limit_days_01 = 5500;
	public static $Limit_days_15 = 20000;
	public static $Limit_week_01 = 15000;
	public static $Limit_week_02 = 20000;
	public static $Limit_mnth_01 = 25000;
	*/

	public static $Limit_mins_01 = 301;
	public static $Limit_mins_05 = 1501;
	public static $Limit_mins_10 = 3001;
	public static $Limit_mins_30 = 9001;
	public static $Limit_hour_01 = 18001;
	public static $Limit_hour_06 = 108001;
	public static $Limit_hour_12 = 216001;
	public static $Limit_days_01 = 432001;
	public static $Limit_days_15 = 6480001;
	public static $Limit_week_01 = 3024001;
	public static $Limit_week_02 = 6048001;
	public static $Limit_mnth_01 = 12960001;
	
	protected $count_mins_01;
	protected $count_mins_05;
	protected $count_mins_10;
	protected $count_mins_30;

	protected $count_hour_01;
	protected $count_hour_06;
	protected $count_hour_12;

	protected $count_days_01;
	protected $count_days_15;

	protected $count_week_01;
	protected $count_week_02;

	protected $count_mnth_01;

	protected $last_mins_01;
	protected $last_mins_05;
	protected $last_mins_10;
	protected $last_mins_30;

	protected $last_hour_01;
	protected $last_hour_06;
	protected $last_hour_12;

	protected $last_days_01;
	protected $last_days_15;

	protected $last_week_01;
	protected $last_week_02;

	protected $last_mnth_01;

	public static $SaveDir = "temp/firewall/";

	public static $DefaultLogPath = "temp/firewall/_firewall_log.txt";

	public static $AllowedCountryCodes = [
		"RO"
	];

	public static $AllowedIps = [
		'37.221.160.67' => '37.221.160.67',
		'127.0.0.1' => '127.0.0.1',
		'37.221.160.93' => '37.221.160.93',
		'109.163.230.*' => '109.163.230.*',
		'82.78.175.39' => '82.78.175.39'
	];

	public static function BlockIP()
	{
		$req_IP = $_SERVER["REMOTE_ADDR"];
		if (isset(static::$AllowedIps[$req_IP]))
			return;

		if (function_exists('Q_Firewall_Block'))
			Q_Firewall_Block();
	}

	/**
	 * Block ip's by country
	 * @return boolean
	 */
	public static function BlockIPByCountry()
	{
		return false;
		
		if (!function_exists("geoip_country_code_by_name"))
			return false;

		$countryCode = geoip_country_code_by_name($_SERVER["REMOTE_ADDR"]);
		if ((($countryCode !== null) && ($countryCode !== false)) && (!isset(static::$AllowedCountryCodes[$countryCode])))
		{
			http_response_code(403);
			q_die('Forbidden');
			return true;
		}
		return false;
	}

	public static function IsIpWhitelisted($req_IP, $diagnoseWhiteListed = false)
	{
		$whitelisted = false;
		
		// add omi whitelisted ips
		$omiAllowedIps = function_exists('Q_Firewall_Get_Omi_Whitelisted_Ips') ? 
			Q_Firewall_Get_Omi_Whitelisted_Ips() : [];
		if (!empty($omiAllowedIps))
			static::$AllowedIps = array_merge($omiAllowedIps, static::$AllowedIps);

		// add partner whitelisted ips
		$partnerAllowedIps = function_exists('Q_Firewall_Get_Custom_Whitelisted_Ips') ? 
			Q_Firewall_Get_Custom_Whitelisted_Ips() : [];
		if (!empty($partnerAllowedIps))
			static::$AllowedIps = array_merge($partnerAllowedIps, static::$AllowedIps);

		if ($diagnoseWhiteListed) 
		{
			echo '<b>Se evalueaza ip-ul: ' . $req_IP . '</b><br/>';
		}
		
		foreach (static::$AllowedIps ?: [] as $allowed_ip)
		{
			if (!($allowed_ip_trimmed = trim($allowed_ip)))
				continue;
			$regexp = str_replace([".","*"], ["\\.", "\\d+"], $allowed_ip_trimmed);
			if ($diagnoseWhiteListed) 
			{
				echo 'Se evalueaza expresia: [' . $allowed_ip . '] - iar expresia regulata folosita este [' . $regexp . ']<br/>';
			}
			if (preg_match( "/{$regexp}/uis", $req_IP))
			{
				$whitelisted = true;
				if ($diagnoseWhiteListed) 
				{
					echo '<div style="color: blue;">Ip-ul [' . $req_IP . '] a fost validat ca fiind in whitelist de catre expresia: [' . $allowed_ip . ']</div>';
				}
				break;
			}
		}

		if ((!$whitelisted) && $diagnoseWhiteListed) 
		{
			echo "<div style='color: red;'>Ip-ul [" . $req_IP . "] nu este in whitelist!</div>";
		}
		
		return $whitelisted;
	}

	/**
	 * Update requests count for ip
	 * @param type $logPath
	 */
	public static function UpdateRequestsCount($logPath = null)
	{
		$doPhpApacheCustomLog = (defined('DO_PHP_APACHE_CUSTOM_LOG') && DO_PHP_APACHE_CUSTOM_LOG);
		$doFirewallCountableReqs = (defined('DO_TRACK_COUNTABLE_REQS') && DO_TRACK_COUNTABLE_REQS);
		
		if ($doPhpApacheCustomLog || $doFirewallCountableReqs)
		{
			$php_access_log_file = 'php_custom_acces_log.log';
			$path = $uri = $_SERVER["REQUEST_URI"];
			if (($p = strpos($uri, "?")) !== false)
				$path = substr($uri, 0, $p);
			$uniqid = uniqid();
			$output = $_SERVER["REMOTE_ADDR"] . " - - [" . date("d/M/Y:H:i:s O") . "] \"{$_SERVER['REQUEST_METHOD']} {$path} {$_SERVER['SERVER_PROTOCOL']}\" " 
				. ($_SERVER['REDIRECT_STATUS'] ?? 0) . " 880 \"".($_SERVER['HTTP_REFERER'] ?? '')."\" \"".($_SERVER['HTTP_USER_AGENT'] ?? '')."||{$uniqid}\"\n";

			if ($doPhpApacheCustomLog)
				file_put_contents($php_access_log_file, $output, FILE_APPEND);
			$php_access_log_verbose_file = 'php_custom_acces_log_verbose.log';
			file_put_contents($php_access_log_verbose_file, "{$uniqid}|" . json_encode($_SERVER) . "|" . json_encode($_GET) . "|" . json_encode($_POST) . "\n", FILE_APPEND);
		}

		$req_IP = $_SERVER["REMOTE_ADDR"];
		#if (isset(static::$AllowedIps[$req_IP]))
		#	return;

		$whitelisted = static::IsIpWhitelisted($req_IP);

		if ($whitelisted)
			return;

		if ($logPath === null)
		{
			if (file_exists('/home/_firewall_log.txt'))
				$logPath = '/home/_firewall_log.txt';
			else
				$logPath = static::$DefaultLogPath;
		}

		if (!is_dir(static::$SaveDir))
			mkdir(static::$SaveDir, 0775, true);

		$countRequest = true;
		$isFastAjax = (($_SERVER["REQUEST_METHOD"] === "POST") && ($_POST && $_POST['__qFastAjax__']));
		if ($isFastAjax)
		{
			$qToExec = isset($_POST['_qb0']["_q_"]) ? $_POST['_qb0']["_q_"] : null;
			$toSkipCalls = ['Omi\Travel\View\Travel.UpdateListDate', ''];
			if ($qToExec && in_array($qToExec, $toSkipCalls))
				$countRequest = false;
		}

		$isListCheck = (($_SERVER["REQUEST_METHOD"] === "GET") && $_GET && isset($_GET['__MultiResponseId']) && isset($_GET['__REQ_UNIQID__']));
		if ($isListCheck)
			$countRequest = false;

		if (!$countRequest)
			return;

		if ($doFirewallCountableReqs)
		{
			$firewall_reqs_log_file = 'q_firewall_requests.log';
			file_put_contents($firewall_reqs_log_file, $output, FILE_APPEND);
		}

		/*
		ob_start();
		var_dump($_SERVER, $_GET, $_POST);
		echo "<br/>======================================================<br/>";
		file_put_contents("firewall_dump.html", ob_get_clean(), FILE_APPEND);
		*/

		
		$reqIpFile = static::$SaveDir . $req_IP . ".txt";
		$content = file_exists($reqIpFile) ? file_get_contents($reqIpFile) : null;
		$current_values = $content ? explode("\n", $content) : null;
		$current_values_pos = 0;
		$record = new static();
		if ($current_values)
		{
			foreach ($record as $k => $v)
			{
				list($role, $type, $len) = explode('_', $k);
				if (!($role && $type && $len))
					continue;
				$record->$k = $current_values[$current_values_pos++];
			}
		}

		$limitsReached = [];
		foreach ($record as $k => $v)
		{
			list($role, $type, $len) = explode('_', $k);
			if (!($role && $type && $len && ($role === 'count')))
				continue;

			$current_start = null;
			// mins, hour, days, week, mnth
			if ($type === 'mins')
				$current_start = mktime(date("H"), ((int)(date('i')/((int)$len))*$len), 0);
			else if ($type === 'hour')
				$current_start = mktime(((int)(date('H')/((int)$len))*$len), 0, 0);
			else if ($type === 'days')
				$current_start = mktime(0, 0, 0, date('n'), ((int)((date('j') - 1)/((int)$len))*$len) + 1);
			else if ($type === 'week')
			{
				$current_start = strtotime('-'.date('w', mktime(0, 0, 0, 1, 1)).' days', mktime(0, 0, 0, 1, 1)) + 
					((((int)((date('W') - 1)/((int)$len))*$len)) * 7 * 24 * 60 * 60);
				# only works for one week:
				# $current_start = strtotime('-'.date('w', mktime(0, 0, 0)).' days',  mktime(0, 0, 0));
			}
			else if ($type === 'mnth')
				$current_start = mktime(0, 0, 0, ((int)((date('n') - 1)/((int)$len))*$len) + 1, 1);
			# int mktime ([ int $hour = date("H") [, int $minute = date("i") [, int $second = date("s") [, int $month = date("n") [, int $day = date("j") [, int $year = date("Y") [, int $is_dst = -1 ]]]]]]] )

			#var_dump($k, $current_start, date('Y-m-d H:i:s', $current_start));
			#echo "<hr/>";

			if ($current_start)
			{
				$last_key = "last_{$type}_{$len}";
				$limit_key = "Limit_{$type}_{$len}";

				if ((int)($record->$last_key) === (int)$current_start)
				{
					# increment
					$record->$k++;
				}
				else
				{
					$record->$k = 1;
					$record->$last_key = (int)$current_start;
				}

				if ((static::$$limit_key > 0) && ($record->$k >= static::$$limit_key))
				{
					$limitsReached[$k] = $record->$k;
					$record->$k = 0;
					# append to log atm
				}
			}
		}

		$reqIpFileContent = "";
		foreach ($record as $k => $v)
		{
			list($role, $type, $len) = explode('_', $k);
				if (!($role && $type && $len))
					continue;
			$reqIpFileContent .= $v . "\n";
		}
		file_put_contents($reqIpFile, $reqIpFileContent);

		// limits were reached
		if ($limitsReached || (function_exists('Q_Firewall_TriggerBlock') && Q_Firewall_TriggerBlock($limitsReached)))
		{		
			$ssl = ((!empty($_SERVER['HTTPS'])) && ($_SERVER['HTTPS'] === 'on')) ? "s" : "";
			$port = $_SERVER['SERVER_PORT'];
			$port = ((!$ssl && ($port == '80')) || ($ssl && ($port == '443'))) ? '' : ':'.$port;

			$req_URL = "http{$ssl}://".$_SERVER['HTTP_HOST'] . $port;
			if (!empty($_GET))
				$req_URL .= "?".http_build_query($_GET);

			/*
			$date = date("Y-m-d H:i:s");
			file_put_contents($logPath, $date . "\n"
				. "---------------------\n" 
				. $_SERVER["REMOTE_ADDR"] . "\n"
				. $req_URL
				. $_SERVER["SERVER_NAME"] . "\n"
				. get_current_user() . "\n"
				. getcwd() . "\n"
				. json_encode($limitsReached) . "\n"
				. "====================================\n\n", FILE_APPEND);
			*/

			if (function_exists('Q_Firewall_Handle'))
				Q_Firewall_Handle($limitsReached);

			$sendFirewallEmailUrl = (defined('SEND_FIREWALL_EMAIL_URL') && SEND_FIREWALL_EMAIL_URL) ? SEND_FIREWALL_EMAIL_URL : null;
			if ($sendFirewallEmailUrl)
			{
				$curl = q_curl_init_with_log($sendFirewallEmailUrl);
				q_curl_setopt_with_log($curl, CURLOPT_POST, 1);
				q_curl_setopt_with_log($curl, CURLOPT_POSTFIELDS, http_build_query([
					"running_dir" => getcwd(),
					"url" => $req_URL,
					"ip" => $_SERVER["REMOTE_ADDR"],
					"user" => get_current_user(),
					"limits_reached" => $limitsReached,
					"date" => $date,
					"server_data" => $_SERVER
				]));

				q_curl_setopt_with_log($curl, CURLOPT_CONNECTTIMEOUT, 3); 
				q_curl_setopt_with_log($curl, CURLOPT_TIMEOUT, 5);
				q_curl_setopt_with_log($curl, CURLOPT_FOLLOWLOCATION, true);
				q_curl_setopt_with_log($curl, CURLOPT_MAXREDIRS, 3);
				q_curl_setopt_with_log($curl, CURLOPT_POSTREDIR, 1);
				q_curl_setopt_with_log($curl, CURLOPT_RETURNTRANSFER, 1);
				$response = q_curl_exec_with_log($curl);
				#if ($response === false)
				#	throw new Exception("Invalid response from: ".static::$SendEmailUrl."\n\n".curl_error($curl));

				//echo $response;
			}

			
		}
		
		//q_die();
	}
}