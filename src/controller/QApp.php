<?php

/**
 * Each main entry script should call QApp::Run() to handle the request
 * 
 * @todo QApp::Run() without a parameter should load index.url.php, or the controller in the running folder
 */
class QApp extends QAppModule
{
	/**
	 * This is the main entry point of the app's data
	 * 
	 * @var QIModel
	 */
	protected static $Data;
	/**
	 * The Data Container
	 *
	 * @var QIStorage|QIStorage[] $container
	 */
	protected static $DataContainer;
	/**
	 * The class to be initialized for the data class
	 * 
	 * @var string
	 */
	protected static $DataClass = "QModel";
	/**
	 * The id of the data object. By default it should be 1.
	 * 
	 * @var integer|string
	 */
	protected static $DataId = 1;
	/**
	 * The default storage.
	 *
	 * @var QIStorage
	 */
	protected static $Storage;
	/**
	 * The list of all the storages
	 *
	 * @var QIStorage[]
	 */
	protected static $StorageList;
	/**
	 * The default QCodeStorage object
	 *
	 * @var QCodeStorage
	 */
	protected static $Code;
	/**
	 *
	 * @var QIUrlController
	 */
	public static $UrlController = null;
	/**
	 * @var boolean
	 */
	protected static $AutoSyncDbStructure = false;
	/**
	 * @var boolean
	 */
	protected static $UseClusterAlterQueries = false;
	/**
	 * @var boolean
	 */
	protected static $HasStorageChanges = false;
	protected static $CallbacksAfterResponse = [];
	protected static $CallbacksAfterResponseLast = [];
	protected static $MultiCallElements = 0;
	
	protected static $MultiCallIndex = 1;
	protected static $MultiCallResponseIndex = 1;
	
	protected static $MultiCallUniqid = null;
	protected static $LegacyErrorHandling = false;
	
	public static $DemoMode = false;

	public static $_PATH_TO_HREF = [];

	protected static $QWebRequest_HandleShutdown_Registered = false;

	public static $StartTime = null;

	public static $RunWithoutCallbacks = false;
	
	public static $PerformanceData = [];

	public static $InCallbackExecution = false;
	
	/**
	 * The language
	 *
	 * @var string
	 */
	public static $Language_Dim = "en";
	/**
	 * The default language
	 *
	 * @var string
	 */
	public static $DefaultLanguage = "en";
	/**
	 * The default language
	 *
	 * @var string
	 */
	public static $DefaultLanguage_Dim = "en";
	
	/**
	 * This is a static class do not create instances for it
	 */
	protected function __construct()
	{
		
	}
	
	/**
	 * Run is called to process the request
	 * 
	 * @param QIUrlController|QIUrlController[] $controllers
	 */
	public static function Run($controllers = null)
	{
		$request_uri = \QWebRequest::$REQUEST_URI ?: (\QWebRequest::$REQUEST_URI = $_SERVER["REQUEST_URI"]);

		$_return = null;
		
		$dev_mode = QAutoload::GetDevelopmentMode();
		
		if ($dev_mode)
			session_start();
		
		register_shutdown_function(function ()
		{
			// we need to include a few more classes
			\QWebRequest::HandleShutdown(ob_get_clean());
		});
		static::$QWebRequest_HandleShutdown_Registered = true;
		
		$tst = static::$StartTime = microtime(true);
		if ($dev_mode && 
				((($sub_url = substr($request_uri, strlen(Q_APP_REL), strlen("~dev/"))) === "~dev/") || ($sub_url === "~dev")))
		{
			// QAutoload::IsDevelopmentAuthenticated();
			self::$UrlController = new QDevModePage();
			$_return = QWebRequest::Process(get_called_class(), self::$UrlController);
		}
		else if ($controllers)
		{
			$one_controller = (!is_array($controllers)) ? $controllers : ((!next($controllers)) ? reset($controllers) : null);
		
			if ($one_controller)
			{
				self::$UrlController = $one_controller;
				$_return = QWebRequest::Process(get_called_class(), $one_controller);
			}
			else 
			{
				$default_controller = null;
				foreach ($controllers as $url => $controller)
				{
					if ($url && is_string($url))
					{
						$url_no_slash = rtrim($url, "/");
						$url_with_slash = $url."/";

						if ((($sub_url = substr($request_uri, strlen(Q_APP_REL), strlen($url_with_slash))) === $url_with_slash) || ($sub_url === $url_no_slash))
						{
							self::$UrlController = $controller;
							$_return = QWebRequest::Process(get_called_class(), $controller, $sub_url);
							// break;
						}
					}
					else if (!$default_controller)
						$default_controller = $controller;
				}
				if (($_return === null) && $default_controller)
				{
					self::$UrlController = $controller;
					$_return = QWebRequest::Process(get_called_class(), $controller);
				}
			}
		}
		else
		{
			// @todo - implement controller on index.url.php 
		}

		$runWithoutCallbacks = (static::$RunWithoutCallbacks || $_GET["q_run_without_callbacks"]);

		if (!$runWithoutCallbacks)
		{
			// execute callbacks after response
			static::ExecuteCallbacks();
		}
		return $_return;
	}
	/**
	 * Execute callbacks after response
	 */
	public static function ExecuteCallbacks()
	{
		q_remote_log_sub_entry([
			[
				'Timestamp_ms' => (string)microtime(true),
				'Tags' => ["tag" => "ExecuteCallbacks"],
				'Traces' => (new \Exception())->getTraceAsString(),
				'Data' => ['CallbacksAfterResponse' => count(static::$CallbacksAfterResponse), 'CallbacksAfterResponseLast' => count(static::$CallbacksAfterResponseLast)],
			]]);
		
		$had_callbacks = false;
		if (static::$CallbacksAfterResponse || static::$CallbacksAfterResponseLast)
		{
			if (!($_GET["q_exec_with_callbacks"]))
			{
				$had_callbacks = true;
				// option 1 ... return & continue (maybe best)
				ignore_user_abort(true);
				set_time_limit(60 * 5);
				if(session_id())
					session_write_close();
				ob_end_flush();
				ob_flush();
				flush();
				if (function_exists("fastcgi_finish_request"))
					fastcgi_finish_request();

			}
		}

		static::$InCallbackExecution = true;

		while (static::$CallbacksAfterResponse)
		{
			list($after_callback, $after_callback_params) = reset(static::$CallbacksAfterResponse);
			call_user_func_array($after_callback, $after_callback_params);
			array_splice(static::$CallbacksAfterResponse, key(static::$CallbacksAfterResponse), 1);
		}
		
		// exec them in reverse order
		if (static::$CallbacksAfterResponseLast && is_array(static::$CallbacksAfterResponseLast))
		{
			rsort(static::$CallbacksAfterResponseLast);
		}

		while (static::$CallbacksAfterResponseLast)
		{
			list($after_callback, $after_callback_params) = reset(static::$CallbacksAfterResponseLast);
			call_user_func_array($after_callback, $after_callback_params);
			array_splice(static::$CallbacksAfterResponseLast, key(static::$CallbacksAfterResponseLast), 1);
		}
		
		q_remote_log_end('end-of-callbacks');
		
		static::$InCallbackExecution = false;
	}

	public static function ClosureDump(\Closure $c) 
	{
		$str = 'function (';
		$r = new \ReflectionFunction($c);
		$params = array();
		foreach($r->getParameters() as $p) {
			$s = '';
			if($p->isArray()) {
				$s .= 'array ';
			} else if($p->getClass()) {
				$s .= $p->getClass()->name . ' ';
			}
			if($p->isPassedByReference()){
				$s .= '&';
			}
			$s .= '$' . $p->name;
			if($p->isOptional()) {
				$s .= ' = ' . var_export($p->getDefaultValue(), TRUE);
			}
			$params []= $s;
		}
		$str .= implode(', ', $params);
		$str .= '){' . PHP_EOL;
		$lines = file($r->getFileName());
		for($l = $r->getStartLine(); $l < $r->getEndLine(); $l++) {
			$str .= $lines[$l];
		}
		return $str;
	}

	/**
	 * Gets the running controller
	 * 
	 * @return QIUrlController
	 */
	public function GetController()
	{
		return self::$UrlController;
	}
	
	/**
	 * Gets the Data entry point
	 * 
	 * @return QIModel
	 */
	public static final function Data()
	{
		if (!self::$Data)
		{
			self::$Data = new static::$DataClass();
			self::$Data->setId(static::$DataId);
			if (self::$DataContainer)
				self::$Data->setContainers(static::$DataContainer);
			self::$Data->init();
		}
		return self::$Data;
	}
	
	/**
	 * Gets the Data entry point
	 * 
	 * @return QIModel
	 */
	public static final function QNewData()
	{
		return self::NewData();
	}
	
	/**
	 * Gets the Data entry point
	 * 
	 * @return QIModel
	 */
	public static final function NewData()
	{
		$data = new static::$DataClass();
		$data->setId(static::$DataId);
		if (self::$DataContainer)
			$data->setContainers(self::$DataContainer);
		$data->init();
		return $data;
	}
	
	/**
	 * @return int
	 */
	public static function GetDataId(): int
	{
		return self::$DataId;
	}

	public static final function UnsetData()
	{
		self::$Data = null;
	}
	
	/**
	 * Sets the default storage
	 * 
	 * @param QIStorage $storage
	 */
	public static final function SetStorage(QIStorage $storage, $key = null)
	{
		\QAutoload::$Storage_Init = true;
		
		if ((!self::$Storage) || ($key === null))
			self::$Storage = $storage;
		if (!self::$StorageList)
		{
			self::$StorageList = [];
			self::$StorageList[$key] = $storage;
		}
		// make sure we have it
		else if (!in_array($storage, self::$StorageList, true))
			self::$StorageList[$key] = $storage;
	}
	
	/**
	 * Gets the default storage
	 * 
	 * @return QIStorage
	 */
	public static final function GetStorage($key = null)
	{
		if (!$key)
			return self::$Storage;
		else
			return self::$StorageList[$key];
	}
	
	/**
	 * Sets the data class by class name
	 * 
	 * @param string $class
	 */
	public static final function SetDataClass($class, $auto_sync_db_structure = null, $force_sync = false, $sync_dev_admin = false)
	{
		self::$DataClass = $class;
		
		$sql_statements = "";

		if ($force_sync || (self::$HasStorageChanges && QAutoload::GetDevelopmentMode()))
		{
			ob_start();
			$do_auto_structure_sync = ($auto_sync_db_structure || (($auto_sync_db_structure === null) && self::$AutoSyncDbStructure));
			// enable this to resync your DB structure
			$sql_statements = \QSqlModelInfoType::ResyncDataStructure(null, $do_auto_structure_sync);

			$dump = ob_get_clean();

			if (strlen(trim($sql_statements)) > 0)
			{
				$statements_dir = QAutoload::GetRuntimeFolder()."_sql/";
				// we have changes
				if (!is_dir($statements_dir))
					qmkdir($statements_dir);
				$sql_file = date("Y-m-d H-i-s").".sql";
				
				file_put_contents($statements_dir.$sql_file, $sql_statements);
				file_put_contents($statements_dir.$sql_file.".info.html", $dump);
				
				if (defined('Q_DEV_MODE_ONLY_IF_USER_MATCH_INDEX_FILE_OWNER') && Q_DEV_MODE_ONLY_IF_USER_MATCH_INDEX_FILE_OWNER)
				{
					$c_unix_user = posix_geteuid();
					$f_owner = fileowner($_SERVER['SCRIPT_FILENAME']);
					if ($c_unix_user != $f_owner) # not allowed
						$do_auto_structure_sync = false;
				}

				if ($do_auto_structure_sync)
				{
					$storage = self::GetStorage();
					
					if (($res = $storage->connection->multi_query($sql_statements)) === false)
					{
						qvar_dump($sql_statements);
						throw new Exception($storage->connection->error);
					}
					
					// we need to wait for all the queries to finish
					do
					{
						$ret_code = $storage->connection->next_result();
						if ((!$ret_code) && $storage->connection->error)
						{
							if (\QAutoload::GetDevelopmentMode()) {
								qvar_dump('$ret_code', $ret_code, '$sql_statements', $sql_statements);
							}
							throw new Exception($storage->connection->error);
						}
					}
					while ($ret_code);
				}
			}
		}
		
		\QSecurity::InitSecurity();
		
		return $sql_statements;
	}
	
	/**
	 * Sets the data class by class name
	 * 
	 * @param string $class
	 */
	public static final function SetDataClass_Internal($class)
	{
		self::$DataClass = $class;
	}
	
	/**
	 * Gets the data class name
	 * 
	 * @return string 
	 */
	public static final function GetDataClass()
	{
		return self::$DataClass;
	}
	
	/**
	 * Gets the web path of a server side path.
	 * 
	 * @param string $full_path
	 * @return string
	 */
	public static function GetWebPath($full_path)
	{
		$m = null;
		
		if (defined('Q_CODE_DIR'))
		{
			if (($full_path === false) && \QAutoload::GetDevelopmentMode())
				throw new \Exception('Invaild argument. Possible realpath failure.');
			if ($full_path[0] !== '/')
				$full_path = Q_RUNNING_PATH . ltrim($full_path, " \\/\t");
			if (substr($full_path, 0, strlen(Q_CODE_DIR)) !== Q_CODE_DIR)
			{
				if (\QAutoload::GetDevelopmentMode())
					qvar_dumpk($full_path);
				throw new \Exception('Path outside app.');
			}
			
			return substr($full_path, strlen(Q_CODE_DIR));
			# return substr($full_path, strlen(Q_RUNNING_PATH) - strlen(BASE_HREF));
		}
		else
		{
			if (is_array($full_path))
				$full_path = reset($full_path);

			if (!empty(static::$_PATH_TO_HREF) && preg_match("'".implode("|", array_keys(static::$_PATH_TO_HREF))."'", $full_path, $m) && 
					$m[0] && static::$_PATH_TO_HREF[$m[0]])
			{
				return (preg_replace("'{$m[0]}'", static::$_PATH_TO_HREF[$m[0]], $full_path));
			}

			return substr($full_path, strlen(Q_RUNNING_PATH) - strlen(BASE_HREF));
		}
	}
	
	/**
	 * Gets a url based on a tag
	 * 
	 * @param string $tag
	 * @param mixed $_arg0
	 * @param mixed $_arg1
	 * @param mixed $_arg2
	 * @param mixed $_arg3
	 * @param mixed $_arg4
	 * @param mixed $_arg5
	 * @param mixed $_arg6
	 * @param mixed $_arg7
	 * @return string
	 */
	public static function Url($tag, $_arg0 = null, $_arg1 = null, $_arg2 = null, $_arg3 = null, $_arg4 = null, $_arg5 = null, $_arg6 = null, $_arg7 = null, $_arg8 = null, $_arg9 = null, $_arg10 = null, $_arg11 = null, $_arg12 = null, $_arg13 = null, $_arg14 = null, $_arg15 = null)
	{
		return self::$UrlController->getUrlForTag($tag, $_arg0, $_arg1, $_arg2, $_arg3, $_arg4, $_arg5, $_arg6, $_arg7, $_arg8, $_arg9, $_arg10, $_arg11, $_arg12, $_arg13, $_arg14, $_arg15);
	}
   
	/**
	 * Enables or disables auto sync of the DB structure
	 * 
	 * @param boolean $value
	 */
	public static function AutoSyncDbStructure($value = true)
	{
		// if ($value && (!QAutoload::GetDevelopmentMode()))
		//  throw new Exception("AutoSyncDbStructure only works in development mode. See QAutoload::EnableDevelopmentMode.");
		self::$AutoSyncDbStructure = $value;
	}
   
	/**
	 * 
	 * @return boolean
	 */
	public static function GetAutoSyncDbStructure()
	{
		return self::$AutoSyncDbStructure;
	}
	
	/**
	 * Enables or disables auto sync of the DB structure
	 * 
	 * @param boolean $value
	 */
	public static function UseClusterAlterQueries($value = true)
	{
		// if ($value && (!QAutoload::GetDevelopmentMode()))
		//  throw new Exception("AutoSyncDbStructure only works in development mode. See QAutoload::EnableDevelopmentMode.");
		self::$UseClusterAlterQueries = $value;
	}
   
	/**
	 * 
	 * @return boolean
	 */
	public static function GetUseClusterAlterQueries()
	{
		return self::$UseClusterAlterQueries;
	}
 
	/**
	 * 
	 * @param boolean $value
	 */
	public static function SetHasStorageChanges($value = true)
	{
		self::$HasStorageChanges = $value;
	}
   
	/**
	 * 
	 * @return boolean
	 */
	public static function GetHasStorageChanges()
	{
	   return self::$HasStorageChanges;
	}

	public static function GetLanguages()
	{
		return QModel::$DimsDef ? QModel::$DimsDef["lang"] : null;
	}
	
	public static function GetLanguage()
	{
		return QModel::$Dims ? QModel::$Dims["lang"] : null;
	}
	
	public static function AddCallbackAfterResponse($callback, $params = [])
	{
		static::$CallbacksAfterResponse[] = [$callback, $params];
	}
	
	public static function AddCallbackAfterResponseLast($callback, $params = [])
	{
		static::$CallbacksAfterResponseLast[] = [$callback, $params];
	}

	public static function GetMultiCallUniqid()
	{
		return static::$MultiCallUniqid;
	}

	public static function MultiResponseExec($callback, $params, $first = true)
	{
		$request_multi_id = \QWebRequest::GetMultiRequestId();
		// \QApp::Log($request_multi_id ? "we enter REQU" : "we enter CACHE");
		if ($request_multi_id)
			list(static::$MultiCallUniqid, static::$MultiCallResponseIndex) = explode("-", $request_multi_id);
		else if (!static::$MultiCallUniqid)
			static::$MultiCallUniqid = uniqid("", true);

		$multi_folder = "temp/multi-request/";
		if (!is_dir($multi_folder))
			qmkdir($multi_folder);

		if ($request_multi_id)
		{
			$rf_file_name = $multi_folder.$request_multi_id.".php";
			$pending_file_name = $multi_folder.$request_multi_id.".pending.php";

			#\QApp::Log(["multi req: ", $pending_file_name, file_exists($pending_file_name)]);

			if (!file_exists($pending_file_name))
			{
				// no request
				#\QApp::Log("we exit responder | NO REQ");
				return [];
			}

			// get multi
			if (\QWebRequest::GetMultiRequestNoWait() && !file_exists($rf_file_name))
			{
				#\QApp::Log("we exit responder | NO RESP AND WE DON'T WAIT");
				return ["__no_response__" => true];
			}

			$wait_step_in_ms = 50;
			set_time_limit(60 * 5);
			
			$max_wait = 120 / ($wait_step_in_ms / 1000); // wait up to 120 sec 
			while (($max_wait--) && (!file_exists($rf_file_name)))
				// wait 50 ms | A microsecond is one millionth of a second.
				usleep($wait_step_in_ms * 1000);
			
			if (!file_exists($rf_file_name))
			{
				// error, we failed to process result
				unlink($pending_file_name);
				#\QApp::Log("we exit responder | NO CACHE");
				return [];
			}
			else
			{
				// wait to make sure it's completed
				usleep(30000);
				
				// ensure it's ready
				$lock = new \QFileLock($rf_file_name);
				$lock->lock_do();
				$lock->unlock();

				include($rf_file_name);
				$result = $__TMP_MULTI_REQ;

				#$isLive = (defined('IS_LIVE') && IS_LIVE);
				#$onPreProd = (defined('ON_PREPRODUCTION') && ON_PREPRODUCTION);
				#if (!((!$isLive) || $onPreProd))
				{
					unlink($rf_file_name);
					unlink($pending_file_name);
				}
				// wait 20 ms
				usleep(20000);

				// how do we know if we have more pending ???
				$next_id = static::$MultiCallUniqid . "-" . (static::$MultiCallResponseIndex + 1);
				#\QApp::Log(["multi req phase 2: ", "elements: ".count($result), $next_id, $multi_folder.$next_id.".pending.php", file_exists($multi_folder.$next_id.".pending.php")]);
				if (file_exists($multi_folder.$next_id.".pending.php"))
					\QWebRequest::SetMultiResponseId($next_id);
				#\QApp::Log("we exit responder | {$next_id} | DATA [".count($result)."] ");

				return $result ?: [];
			}
		}
		else
		{
			$result = null;
			// here we need to do some wrapping for things to work
			$current_id = static::$MultiCallUniqid . "-" . static::$MultiCallIndex;
	
			if (!$first)
			{
				file_put_contents($multi_folder.$current_id.".pending.php", "");
				// \QApp::Log("we create: ".$multi_folder.$current_id.".pending.php");
			}
			
			$result = call_user_func_array($callback, $params);
			if (!$first)
				static::$MultiCallElements--;
			
			if (static::$MultiCallElements > 0)
			{
				// increment index
				static::$MultiCallIndex++;
				
				$next_id = static::$MultiCallUniqid . "-" . static::$MultiCallIndex;
				// test if more elements were setup to be executed
				if ($first)
					\QWebRequest::SetMultiResponseId($next_id);
				
				// we know there will be more, flag that
				file_put_contents($multi_folder.$next_id.".pending.php", "");
				// \QApp::Log("we create: ".$multi_folder.$next_id.".pending.php");
			}
			
			if (!$first)
			{
				// put the result in cache
				$wf_file_name = $multi_folder.$current_id.".php";
				
				// qVarExport($data, $export_obj_nulls = false, \SplObjectStorage $refs = null, &$obj_count_index = 1)
				$str_to_write = "<?php\n\n\$__TMP_MULTI_REQ = ".qVarExport($result, false, new \SplObjectStorage()).";";
				
				$lock = new \QFileLock($wf_file_name, "w");
				$lock->lock_do();
				$lock->write($str_to_write);
				$lock->unlock();
				// \QApp::Log("we cache: ".$multi_folder.$current_id.".php");
			}
			
			// \QApp::Log("we exit cacher");
			return $result;
		}
	}
	
	public static function MultiResponseProcess($callback, $params)
	{
		static::$MultiCallElements++;
		return static::AddCallbackAfterResponse(["QApp", "MultiResponseExec"], [$callback, $params, false]);
	}
	
	public static function Log($data)
	{
		$txt = "[".date("Y-m-d H:i:s.u")."] ".qVarExport($data, true).PHP_EOL;
		// file_put_contents("debug_log.txt", $txt , FILE_APPEND);
		$f = fopen("debug_log.txt", "at");
		fwrite($f, $txt);
		fclose($f);
	}
	
	public static function MergeFromArray($destination, $data = null, $selector = true, $type = "auto", bool $trigger_provision = true, bool $trigger_events = true, bool $trigger_save = false, bool $trigger_import = false)
	{
		$app_data = [];
		
		if (is_string($destination) && qis_array($data))
			$app_data[$destination] = $data;
		else if (is_array($destination) && ($data === null))
			$app_data = $destination;
		else
			throw new \Exception('Invalid input');
		
		// ensure id
		$app_data["Id"] = self::NewData()->getId();
		
		$class = static::GetDataClass();
		$obj = $class::FromArray($app_data, $class, ($selector === true) ? null : $selector, false);
		
		if ($obj instanceof QIModel)
			return $obj->merge($selector, null, $trigger_provision, $trigger_events, $trigger_save, $trigger_import);
		else
			return false;
	}
	
	public static function MergeObjects($destination, $data = null, $selector = true, $type = "auto", bool $trigger_provision = true, bool $trigger_events = true, bool $trigger_save = false, bool $trigger_import = false)
	{
		$app_obj = self::NewData();
		
		$app_data = [];
		if (is_string($destination) && qis_array($data))
		{
			$app_data[$destination] = $data;
			if (is_array($selector))
				$selector = [$destination => $selector];
			else if (is_string($selector))
				$selector = $destination.".{{$selector}}";
		}
		else if (is_array($destination) && ($data === null))
			$app_data = $destination;
		else
			throw new \Exception('Invalid input');
		
		$m_ty = \QModelQuery::GetTypesCache(get_class($app_obj));
		foreach ($app_data as $k => $v)
		{
			if (qis_array($v))
			{
				$p_inf = $m_ty[$k];
				if (!$p_inf)
					throw new \Exception('Missing app property: '.$k);
				if ($p_inf["[]"])
				{
					$arr = new \QModelArray();
					$app_obj->$k = $arr;
					foreach ($v as $itm)
						$app_obj->$k[] = $itm;
				}
				else
					throw new \Exception('The property `'.$k.'` is not a collection.');
			}
			else
				$app_obj->{"set{$k}"} = $v;
		}
		
		return $app_obj->merge($selector, null, $trigger_provision, $trigger_events, $trigger_save, $trigger_import);
	}
	
	/**
	 * @return boolean
	 */
	public static function GetLegacyErrorHandling()
	{
		return static::$LegacyErrorHandling;
	}
	
	public static function EnableLegacyErrorHandling()
	{
		static::$LegacyErrorHandling = true;
	}
	
	public static function Get_QWebRequest_HandleShutdown_Registered()
	{
		return static::$QWebRequest_HandleShutdown_Registered;
	}
	
	public static function CleanupForRemovedElements($remove_elements)
	{
		return \QSqlTable::CleanupForRemovedElements($remove_elements);
	}
	
	/**
	 * Cleans up the entire database
	 * 
	 * @param bool $return_output
	 * @return array[]
	 */
	public static function CleanupDatabase(bool $return_output = false, bool $sync_data_class = false)
	{
		// make sure you are in sync before we trigger it
		if ($sync_data_class)
			static::SetDataClass(self::$DataClass, true, true);
		
		if ($return_output)
			ob_start();
		
		$queries = \QSqlTable::GetCleanupQueries(\QApp::GetStorage());//, 'Offers');//, 'Omi\Comm\Offer\Offer');
		$mysqli = \QApp::GetStorage()->connection;
		echo "<div style='font-family: monospace;'>";
		foreach ($queries as $q)
		{
			echo "<b>{$q}</b><br/>\n";
			$res = $mysqli->query($q);
			if (!$res)
			{
				echo "<span style='color: red;'>ERROR [{$mysqli->errno}]: {$mysqli->error}</span><br/>\n";
			}
			else
			{
				if (!$mysqli->affected_rows)
					echo "No change<br/>\n";
				else
					echo "<span style='color: blue;'>Affected Rows: ".$mysqli->affected_rows."</span><br/>\n";
			}
			echo "--------------------------------------------<br/>\n";
		}
		echo "</div>";
		
		if ($return_output)
			return [$queries, ob_get_clean()];
		else
			return [$queries];
	}
	
	/**
	 * 
	 * @param string $from
	 * @param string $selector
	 * @param array $parameters
	 * 
	 * @return QIModel
	 */
	public static function Before_API_Query($from, $selector = null, $parameters = null, $only_first = false, $id = null)
	{
		return [false, null];
	}
	
	public static function GetDefaultLanguage()
	{
		return (\QModel::$DimsDef && \QModel::$DimsDef["lang"]) ? reset(\QModel::$DimsDef["lang"]) : (static::$DefaultLanguage ?: static::$Language);
	}
}
