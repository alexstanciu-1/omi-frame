<?php

class Q_Log
{
	const Default_Dir = '../temp/q_logs/';
	const Persist_Base_Dir = '../persist_log/';
	
	protected static $Current = [];
	
	protected static $enabled = false;
	protected static $has_threads_support = null;
	protected static $enabled_segments = [];
	
	protected static $persist_days = null;
	protected static $persist_tag = null;
	protected static $logs_dir = null;
	
	protected static $node_increment = 0;
	
	protected static $log_entry_increment = 0;
	
	protected static $first_log_done = false;
	
	/**
	 * # how to use it
	 * try
	 * {
	 * 	list($log_node) = \Q_Log::log_block();
	 * 	
	 * 	# code here ...
	 * }
	 * finally
	 * {
	 * 	\Q_Log::log_block_end($log_node);
	 * }
	 * 
	 * @param array $data
	 * @return \stdClass
	 */
	public static function log_block($data = [], string $segment = null)
	{
		if (!static::is_enabled($segment))
			return;
		
		if (is_callable($data))
			$data = $data();

		$stack_id = static::get_calling_stack_id();
		
		$node = new \stdClass();
		$node->id = ++static::$node_increment;
		$node->stack_id = $stack_id;
		
		# also link it to the main thread/stack if no parent
		$node->parent = static::current();
		static::$Current[$stack_id] = $node;
		
		$data['$.log_block'] = true;
		# call the log
		return static::log($data, $segment, null, $stack_id, $node);
	}
	
	public static function log_block_end($data = [], string $segment = null)
	{
		if (!static::is_enabled($segment))
			return;
		
		if (is_callable($data))
			$data = $data();

		$stack_id = static::get_calling_stack_id();
		$c_node = static::current();
		
		$ret = null;
		if ($c_node)
		{
			$data['$.log_block_end'] = true;
			# call the log
			$ret = static::log($data, $segment, null, $stack_id, $c_node);

			# step back on the tree

			static::$Current[$stack_id] = null; # reset on this stack
			($parent = $c_node->parent) ? (static::$Current[$parent->stack_id] = $parent) : null;
			# static::$Current[$stack_id] = $c_node->parent ?? null;
		}
		
		return $ret;
	}
	
	public static function enable(array $segments = null, int $persist_days = 0, string $persist_tag = null)
	{
		if (!isset(static::$logs_dir)) {
			static::$logs_dir = static::Default_Dir;
		}
		if ($segments === null) {
			static::$enabled = true;
			static::$persist_days = $persist_days;
			static::$persist_tag = $persist_tag;
			if ($persist_days || $persist_tag) {
				static::$logs_dir = static::Persist_Base_Dir . "{$persist_tag}_".((int)$persist_days)."/";
				if (!is_dir(static::$logs_dir)) {
					qmkdir(static::$logs_dir);
				}
			}
			static::$enabled_segments[0] = true;
		}
		else {
			foreach ($segments as $s) {
				static::$enabled_segments[$s] = $s;
			}
			# static::$enabled_segments = array_combine(array_values($segments), array_values($segments));
		}
	}
	
	public static function disable(array $segments = null)
	{
		if ($segments === null) {
			static::$enabled = false;
			static::$persist_days = null;
			static::$persist_tag = null;
			static::$enabled_segments[0] = false;
		}
		else if ((!empty($segments)) && (!empty(static::$enabled_segments)))
		{
			foreach ($segments as $seg)
				static::$enabled_segments[$seg] = false;
		}
	}

	public static function handle_first_log()
	{
		if (static::$first_log_done)
			return;
		
		static::$first_log_done = true;
		
		$data = [
			# @TODO 
			'@caption' => 'init',
			'@server' => $_SERVER,
		];
		
		register_shutdown_function(function ()
		{
			# call register_shutdown_function in a stack to make sure it's executed at the very end
			register_shutdown_function(function ()
			{
				register_shutdown_function(function ()
				{
					$end_data = [
						# @TODO 
						'$.finsihed_time' => microtime(true),
						'@caption' => 'finish',
					];

					static::log($end_data, null, null, 0, null, 'end');
				});
			});
		});
		
		return static::log($data, null, null, 0, null, 'start');
	}
	
	public static function is_enabled(string $segment = null)
	{
		if (q_remote_log_is_bot() || ((!static::$enabled) && empty(static::$enabled_segments)))
#				||
#			((!static::$enabled) && (($segment === null) || (!(static::$enabled_segments[$segment] ?? null))))
#			)
		{
			# exit now
			return false;
		}
		else {
			return (static::$enabled_segments[$segment ?? 0] ?? false) ? true : false;
		}
	}

	public static function log($data = [], string $segment = null, int $data_depth = null, int $stack_id = null, object $c_node = null, string $save_name = null)
	{
		if (!static::is_enabled($segment))
			return;
		
		if (is_callable($data))
			$data = $data();
		
		# set this up at the very start ! 
		static::handle_first_log();
		
		try
		{
			ob_start();
			
			if ($stack_id === null)
				$stack_id = static::get_calling_stack_id();
			
			# q_log(['$.tag' => 'tf_search']);
			$tags = $data['$.tag'] ?? null;
			
			$data_mode = $data['$.data.mode'] ?? null;
			
			if (!($log_id = ($data['$.log_id'] ?? null)))
			{
				$data['$.log_id'] = $log_id = isset($c_node) ? $c_node->id : (++static::$node_increment);
				$tmp_node = $c_node->parent ?? null;
								
				$parents_ids = [];
				while ($tmp_node)
				{
					$parents_ids[] = $tmp_node->id;
					$tmp_node = $tmp_node->parent;
				}
				
				if (!empty($parents_ids))
					$data['$.log_parents_ids'] = $parents_ids;
			}
			
			if (!($time = ($data['$.time'] ?? null)))
				$data['$.time'] = $time =  microtime(true);
			
			if (!($trace = ($data['$.trace'] ?? null)))
			{
				$trace =  (new \Exception())->getTrace();
				while ((($trace[0]['class'] ?? null) === 'Q_Log') || ((!($trace[0]['class'] ?? null)) && 
							["q_log"=>true,"q_log_block"=>true,"q_log_block_end"=>true,][$trace[0]['function']]))
				{
					array_shift($trace);
				}
				
				$data['$.trace'] = $trace;
			}
			
			$data['$.stack_id'] = $stack_id;

			$log_entry_increment = ++self::$log_entry_increment;
			$rid = \QWebRequest::Get_Request_Id();
			# $rid_for_logs = \QWebRequest::Get_Request_Id_For_Logs();
			
			$save_data = static::sanitize($data, ($data_depth ?? (($data_mode === 'rows') ? 3 : 2)));
			
			$save_data['$.metrics'] = q_remote_log_get_metrics();
			$save_data['$.request_id'] = $rid;
			$save_data['$.log_entry'] = $log_entry_increment;
			
			$rc_json = json_encode($save_data, JSON_UNESCAPED_LINE_TERMINATORS | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
			
			$write_path = null;
			if (is_string($rc_json))
			{
				if (!is_dir(static::$logs_dir."{$rid}/"))
					mkdir(static::$logs_dir."{$rid}", 0755, true);
				
				$write_path = static::$logs_dir."{$rid}/".($save_name ?? "{$log_id}_{$log_entry_increment}").".gz";
				file_put_contents($write_path, gzcompress($rc_json));
				
				if ($tags !== null)
				{
					if (is_string($tags))
						$tags = [$tags];
					$f_tags = file_get_contents(static::$logs_dir."{$rid}/tags.gz");
					$existing_tags = is_string($f_tags) ? (json_decode(gzuncompress($f_tags), true, 512, JSON_INVALID_UTF8_SUBSTITUTE) ?: []) : [];
					foreach ($tags as $t)
						$existing_tags[$t] = $t;
					file_put_contents(static::$logs_dir."{$rid}/tags.gz", gzcompress(json_encode($existing_tags, JSON_UNESCAPED_LINE_TERMINATORS | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE)));
				}
			}
						
			return [$save_data, $write_path];
			
		}
		catch (\Exception $ex)
		{
			# we will not handle it
		}
		finally
		{
			ob_end_clean();
		}
	}
	
	public static function is_bot()
	{
		return (php_sapi_name() !== 'cli') && preg_match("/"
						.'(BLEXBot|crawler|applebot|bingbot|Googlebot|Yandex)'
					. "/uis", $_SERVER['HTTP_USER_AGENT']);
	}
	
	public static function Init(array $init_data = null)
	{
		if (static::is_bot())
			return;
		
		if (!isset(static::$logs_dir)) {
			static::$logs_dir = static::Default_Dir;
		}

		if (!is_dir(static::$logs_dir))
			mkdir(static::$logs_dir, 0755, true);

		foreach ($init_data ?? [] as $k => $v)
		{
			if ($k === '@enable')
			{
				if (is_array($v))
					static::enable($v);

				else if ($v && (is_bool($v) || ($v === 1)))
					static::enable();
			}
		}
				
		$ra = $_SERVER['REMOTE_ADDR'];
		if (($ra === Dev_Ip) || ($ra === TF_VPN_IP))
		{
			if (isset($_GET['_enable_logs_']))
				Q_SESSION('_enable_logs_', $_GET['_enable_logs_'] ? 1 : 0);

			if (Q_SESSION('_enable_logs_') ? true : false)
				static::enable();
		}
	}
	
	public static function sanitize($data, int $max_depth = 1, bool $top_level = true, array $model_props = [])
	{
		if (is_scalar($data) || ($data === null))
			return $data;
		else if (is_array($data) || is_object($data))
		{
			if ($max_depth < 0)
				return is_array($data) ? "##array[".q_count($data)."]" : "##object[".get_class($data)."]";

			$ret = [];
			$class = null;
			if (is_object($data))
				$ret['__obj'] = ($class = get_class($data));

			if (is_object($data) && (!($data instanceof \QIModelArray)))
			{
				if ($data instanceof \QIModel)
				{
					$props = $model_props[$class] ?? ($model_props[$class] = \QModel::GetTypeByName($class)->properties);
					foreach (($props ?? $data) as $k => $v)
					{
						if (($k[0] === '_') || (strtolower($k[0]) === $k[0]))
							continue;
						try
						{
							$rv = static::sanitize($data->$k, $max_depth - 1, false, $model_props);
							if ($rv !== null)
								$ret[$k] = $rv;
						}
						catch (\Exception $ex)
						{
							continue;
						}
					}
				}
				else
				{
					foreach ($data as $k => $v)
						$ret[$k] = static::sanitize($v, $max_depth - 1, false, $model_props);
				}
			}
			else
			{
				# $ret['__count'] = q_count($data);
				foreach ($data as $k => $v)
					$ret[$k] = static::sanitize($v, $max_depth - 1, false, $model_props);
			}

			return $ret;
		}
		else if (is_resource($data))
		{
			# bad luck
			return "#resource";
		}
		else
		{
			# bad luck
			return "#".gettype($data);
		}
	}
	
	public static function get_log_uid()
	{
		return \QWebRequest::Get_Request_Id();
	}
	
	public static function current()
	{
		# (static::$Current[$stack_id] ?? (($stack_id > 0) ? (static::$Current[0] ?? null) : null))
		$stack_id = static::get_calling_stack_id();
		return static::$Current[$stack_id] ?? (static::$Current[0] ?? null);
	}
	
	protected static function get_calling_stack_id()
	{
		if (\Fiber::getCurrent())
			return spl_object_id(\Fiber::getCurrent());
		else if ((static::$has_threads_support ?? (static::$has_threads_support = class_exists('Thread'))) && \Thread::getCurrentThread())
			return spl_object_id(\Thread::getCurrentThread());
		else
			return 0;
	}
}
