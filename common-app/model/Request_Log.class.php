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
	 * @storage.oneToMany Request
	 * 
	 * @var Request_Log_Trace[]
	 */
	protected $Traces;
	
	/**
	 * @storage.type CHAR(26)
	 * @storage.index
	 * 
	 * @var string
	 */
	protected $Remote_RId;
	/**
	 * @storage.type VARCHAR(64)
	 * @storage.index
	 * 
	 * @var string
	 */
	protected $Remote_Idf;
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
	 * @storage.type CHAR(16)
	 * @storage.index
	 * 
	 * @var string
	 */
	protected $IP_v4;
	/**
	 * @var boolean
	 */
	protected $Is_Ajax;
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
	 * @storage.type TEXT
	 * @storage.compressed
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
	
	public static function Log_Request()
	{
		if (defined('Q_DISABLE_Log_Request') && Q_DISABLE_Log_Request)
			return;
		
		$req = static::$Current_Request = new static;
		$req->Date = date("Y-m-d H:i:s", $_SERVER['REQUEST_TIME_FLOAT']);
		$req->Method = $_SERVER['REQUEST_METHOD'];
		$req->IP_v4 = $_SERVER['REMOTE_ADDR'];
		$req->Is_Ajax = \QWebRequest::IsAjaxRequest();
		# $req->Is_Fast_Call = ($_POST["__qFastAjax__"] || $_GET["__qFastAjax__"]);
		
		$req->Timestamp_ms = $_SERVER['REQUEST_TIME_FLOAT'];
		$req->Session_Id = session_id() ?: null;
		
		$req->Request_URI = parse_url($_SERVER['SCRIPT_URI'], PHP_URL_PATH);
		$req->Cookies = $_SERVER['HTTP_COOKIE'];
		$req->User_Agent = $_SERVER['HTTP_USER_AGENT'];
		
		$req->HTTP_GET = $_SERVER['REQUEST_URI']; # (!empty($_GET)) ? json_encode($_GET, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_LINE_TERMINATORS | JSON_INVALID_UTF8_SUBSTITUTE) : null;
		#  php://input is a read-only stream that allows you to read raw data from the request body. php://input is not available with enctype="multipart/form-data". 
		$req->HTTP_POST = file_get_contents('php://input'); # (!empty($_POST)) ? json_encode($_POST, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_LINE_TERMINATORS | JSON_INVALID_UTF8_SUBSTITUTE) : null;
		$req->HTTP_FILES = (!empty($_FILES)) ? json_encode($_FILES, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_LINE_TERMINATORS | JSON_INVALID_UTF8_SUBSTITUTE) : null;
		
		$app = \QApp::NewData();
		$app->Request_Logs = new \QModelArray();
		$app->Request_Logs[] = $req;
		$app->db_save('Request_Logs.*');
				
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

				. "??Is_Ajax?<AND[Is_Ajax IN (?)]"
				. "??QINSEARCH_IP_v4?<AND[(IP_v4=?)]"

				. "??IP_v4?<AND[IP_v4=?]"
				. "??QINSEARCH_Is_Ajax?<AND[Is_Ajax IN(?)]"
				
				. "??Session_Id?<AND[Session_Id=?]"
				. "??QINSEARCH_Session_Id?<AND[Session_Id IN(?)]"
				
				. "??Remote_Idf?<AND[Remote_Idf LIKE(?)]"
				. "??QINSEARCH_Remote_Idf?<AND[Remote_Idf LIKE(?)]"
				
				. "??Remote_RId?<AND[Remote_RId=?]"
				. "??QINSEARCH_Remote_RId?<AND[Remote_RId=?]"
				
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

