<?php

namespace Omi;

/**
 * @author Alex
 * 
 * @storage.table Request_Logs
 *
 * @model.captionProperties Request_URI,Method,Date,IP_v4
 *
 * @class.name Request_Log
 */
abstract class Request_Log_mods_model_ extends \QModel
{
	/**
	 * @var Request_Log
	 */
	protected static $Current_Request;
	
	/**
	 * @var int
	 */
	protected $Id;
	/**
	 * @storage.index
	 * 
	 * @var datetime
	 */
	protected $Date;
	/**
	 * @storage.type DECIMAL(20,4)
	 * 
	 * @var float
	 */
	protected $Timestamp_ms;
	/**
	 * @storage.type DECIMAL(20,4)
	 * 
	 * @var float
	 */
	protected $Timestamp_ms_end;
	/**
	 * @storage.index
	 * 
	 * @var float
	 */
	protected $Duration;
	/**
	 * @var boolean
	 */
	protected $Is_Error;
	/**
	 * @storage.type CHAR(8)
	 * 
	 * @var string
	 */
	protected $Method;
	/**
	 * 
	 * @storage.index
	 * @storage.type CHAR(16)
	 * 
	 * @var string
	 */
	protected $IP_v4;
	/**
	 * @var boolean
	 */
	protected $Is_Ajax;
	/**
	 * @var boolean
	 */
	protected $Is_Fast_Call;
	/**
	 * @storage.index
	 * 
	 * @var string
	 */
	protected $Request_URI;
	/**
	 * @storage.type TEXT
	 * @storage.compressed
	 * 
	 * @var string
	 */
	protected $Cookies;
	/**
	 * @storage.type VARCHAR(32)
	 * @storage.index
	 * 
	 * @var string
	 */
	protected $Session_Id;
	/**
	 * @storage.index
	 * @storage.type TEXT
	 * 
	 * @var string
	 */
	protected $User_Agent;
	/**
	 * @storage.type TEXT
	 * @storage.compressed
	 * 
	 * @var string
	 */
	protected $HTTP_GET;
	/**
	 * @storage.type MEDIUMTEXT
	 * @storage.compressed
	 * 
	 * @var string
	 */
	protected $HTTP_POST;
	/**
	 * @storage.type TEXT
	 * @storage.compressed
	 * 
	 * @var string
	 */
	protected $HTTP_FILES;
	/**
	 * @storage.type TEXT
	 * @storage.compressed
	 * 
	 * @var string
	 */
	protected $Tags;
	/**
	 * @storage.type LONGTEXT
	 * 
	 * @var string
	 */
	protected $Traces;
	/**
	 * @storage.oneToMany Request
	 * 
	 * @var Request_Log_Trace[]
	 */
	protected $Traces_List;

	public static function Get_Current_Request()
	{
		return static::$Current_Request;
	}
	
	public function log()
	{
		return static::Log_Request($this);
	}

	public static function Log_Request(Request_Log $req_log = null)
	{
		if (($req_log === null) && static::$Current_Request)
			return false; # already started
		
		if ($req_log)
		{
			$req = $req_log;
			if (($req->HTTP_POST !== null) && (!is_string($req->HTTP_POST)))
				$req->HTTP_POST = json_encode($req->HTTP_POST, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_LINE_TERMINATORS | JSON_INVALID_UTF8_SUBSTITUTE);
			if (($req->HTTP_FILES !== null) && (!is_string($req->HTTP_FILES)))
				$req->HTTP_FILES = json_encode($req->HTTP_FILES, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_LINE_TERMINATORS | JSON_INVALID_UTF8_SUBSTITUTE);
			if (($req->Traces !== null) && (!is_string($req->Traces)))
				$req->Traces = json_encode($req->Traces, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_LINE_TERMINATORS | JSON_INVALID_UTF8_SUBSTITUTE);
		}
		else
		{
			$req = static::$Current_Request = new static;
			$req->_request_time = $_SERVER['REQUEST_TIME_FLOAT'];
			$req->_log_start_time = microtime(true);
			$req->Date = date("Y-m-d H:i:s", $_SERVER['REQUEST_TIME_FLOAT']);
			$req->Method = $_SERVER['REQUEST_METHOD'];
			$req->IP_v4 = $_SERVER['REMOTE_ADDR'];
			$req->Is_Ajax = \QWebRequest::IsAjaxRequest();
			$req->Is_Fast_Call = ($_POST["__qFastAjax__"] || $_GET["__qFastAjax__"]);

			$req->Request_URI = parse_url($_SERVER['SCRIPT_URI'], PHP_URL_PATH);
			$req->Cookies = $_SERVER['HTTP_COOKIE'];
			$req->User_Agent = $_SERVER['HTTP_USER_AGENT'];

			$req->HTTP_GET = $_SERVER['REQUEST_URI']; # (!empty($_GET)) ? json_encode($_GET, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_LINE_TERMINATORS | JSON_INVALID_UTF8_SUBSTITUTE) : null;
			$req->HTTP_POST = (!empty($_POST)) ? json_encode($_POST, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_LINE_TERMINATORS | JSON_INVALID_UTF8_SUBSTITUTE) : null;
			$req->HTTP_FILES = (!empty($_FILES)) ? json_encode($_FILES, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_LINE_TERMINATORS | JSON_INVALID_UTF8_SUBSTITUTE) : null;
			
			$cwd = getcwd();
			
			register_shutdown_function(function () use ($cwd, $req)
			{
				# some dir bug for register_shutdown_function
				chdir($cwd);
				
				$req->setDuration(microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']);

				if (isset($req->_dbg_traces) && ($dbg_traces = $req->_dbg_traces)) {
					$req->Traces = json_encode($dbg_traces, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_LINE_TERMINATORS | JSON_INVALID_UTF8_SUBSTITUTE);
					$req->db_save('Duration,Traces');
				}
				else {
					$req->db_save('Duration');
				}
			});
		}
		
		$app = \QApp::NewData();
		$app->Request_Logs = new \QModelArray();
		$app->Request_Logs[] = $req;
		$app->db_save('Request_Logs.{*,Traces}');
		
		return true;
		/*
		1. upper level request log
		ajax ... what's being called ... etc
		post / no post

		2. QApi log (one level only)
		3. controller log
		4. grid log
		5. API exec log
		 */
		
	}
	
	public static function Append_Trace($data)
	{
		if (!static::$Current_Request)
			return false; # not started yet
		
		if (!isset(static::$Current_Request->_dbg_traces))
			static::$Current_Request->_dbg_traces = [];
		static::$Current_Request->_dbg_traces[] = $data;
	}
	
	/**
	 * Gets a default for a listing selector if none was specified
	 * 
	 * @return string
	 */
	public static function GetListingQuery($selector = null)
	{
		$selector = $selector ?: static::GetListingEntity();
        
		$q = (is_array($selector) ? qImplodeEntity($selector) : $selector)." "
				# . " SELECT {} "
				. " WHERE 1 "
				. "??Id?<AND[Id=?]"
				. "??Id_IN?<AND[Id IN (?)]"

				. "??IP_v4?<AND[IP_v4=?]"
				. "??QINSEARCH_IP_v4?<AND[(IP_v4=?)]"

				. "??Request_URI?<AND[Request_URI LIKE (?)]"
				. "??QINSEARCH_Request_URI?<AND[Request_URI LIKE (?)]"
				
				. "??User_Agent?<AND[User_Agent LIKE (?)]"
				. "??QINSEARCH_User_Agent?<AND[User_Agent LIKE (?)]"

				. "??Is_Ajax?<AND[Is_Ajax IN (?)]"
				. "??QINSEARCH_Is_Ajax?<AND[Is_Ajax IN(?)]"
				
				. "??Session_Id?<AND[Session_Id=?]"
				. "??QINSEARCH_Session_Id?<AND[Session_Id IN(?)]"
				
				. "??HTTP_GET?<AND[HTTP_GET LIKE (?)]"
				. "??QINSEARCH_HTTP_GET?<AND[HTTP_GET LIKE (?)]"
				
				. "??Is_Error?<AND[Is_Error = ?]"
				. "??QINSEARCH_Is_Error?<AND[Is_Error = ?]"
				
				. "??Tags?<AND[Tags LIKE (?)]"
				. "??QINSEARCH_Tags?<AND[Tags LIKE (?)]"
				
			# . " GROUP BY Id "
			. " ORDER BY "
				. "??OBY_Id?<,[Id ?@]"
				. " Id DESC "
			. " ??LIMIT[LIMIT ?,?]";
        
		return $q;
	}
}

