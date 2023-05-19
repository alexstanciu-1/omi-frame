<?php

namespace Omi\Dev;

/**
 * 
 */
class Sync
{
	protected static $last_run = 0;
	protected static $last_return;
	protected static $info_by_class;
	protected static $types_dir;
	protected static $extended_by_path;
	
	public static function Ensure_Sync(bool $force = false)
	{
		# only run once !
		if ((!$force) && self::$last_run)
			return static::$last_return;
		
		$origin_url = $_GET['origin'] ? filter_var($_GET['origin'], FILTER_SANITIZE_URL) : null;
		
		if (!$origin_url)
		{
			// var_dump(session_status(), $_SESSION, $_GET);
			throw new \Exception('Missing origin.');
		}
		$url_parts = parse_url($origin_url);
		if (!($url_parts && $url_parts['host'] && $url_parts['path']))
			throw new \Exception('Failed origin parsing.');
		
		$remote_app_dir = rtrim($_SERVER['DOCUMENT_ROOT'], '/').'/'.trim($url_parts['path'], '/')."/";
		if (!is_dir($remote_app_dir))
			throw new \Exception('No solution found to pull metadata.');
		$code_temp_dir = $remote_app_dir.'code/temp/';
		if (!file_exists($code_temp_dir."autoload.php"))
			throw new \Exception('No solution found to pull metadata.');
		if (!is_dir($code_temp_dir."types/"))
			throw new \Exception('No solution found to pull metadata.');
		static::$types_dir = $code_temp_dir."types/";
		
		static::$extended_by_path = $code_temp_dir.'extended_by.php';
		if (!file_exists(static::$extended_by_path))
			throw new \Exception('No solution found to pull metadata.');
		
		$cache_dir_path = $remote_app_dir.'temp/code/';
		$cache_file_path = $cache_dir_path.'sync_info_by_class.php';
		
		if (($url_parts['host'] === $_SERVER['HTTP_HOST']) && file_exists($cache_file_path) && (fileowner($cache_file_path) == getmyuid()))
		{
			$local_file = "temp/sync/".trim($url_parts['path'], '/')."/sync_info_by_class.php";
			if (!is_dir(dirname($local_file)))
				qmkdir(dirname($local_file));
			else if (file_exists($local_file) && (filemtime($local_file) === filemtime($cache_file_path)))
			{
				// no action ... we are in sync
			}
			else
			{
				copy($cache_file_path, $local_file);
				touch($local_file, filemtime($cache_file_path));
				opcache_invalidate($cache_file_path, true); # make sure PHP reloads it
			}
			
			$_DATA;
			require($local_file);
			static::$info_by_class = $_DATA;
			
			static::$last_run = time();
			return (static::$last_return = ['info_by_class' => $cache_file_path]);
		}
		else
			throw new \Exception('No solution found to pull metadata.');
	}
	
	public static function Get_Info_By_Class()
	{
		return static::$info_by_class;
	}
	
	public static function Get_Types_Dir()
	{
		return static::$types_dir;
	}
	
	public static function Get_Main_Model_Class()
	{
		# @TODO - detect/deterine | use config
		return 'Omi\App';
	}
	
	public static function Get_Extended_By_Path()
	{
		return static::$extended_by_path;
	}
}
