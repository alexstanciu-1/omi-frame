<?php


/**
 * @class.name QDevModeAdmin
 */
abstract class QDevModeAdmin_frame_ extends QWebControl
{
	
	
	public $showProperty = null;
	public $content = null;
	
	public function init($recursive = true)
	{
		if ($this->showProperty)
		{
			ob_start();
			static::SyncAdmin($this->showProperty);
			$this->sync_output = ob_get_clean();
		}
		parent::init($recursive);
	}
	
	public function getProperties($class = null)
	{
		$class = $class ?: QApp::GetDataClass();
		
		$class_inf = QModelQuery::GetTypesCache($class);
		
		$props = [];
		
		foreach ($class_inf as $p_name => $pinf)
		{
			if (substr($p_name, 0, 2) === "#%")
				continue;
			
			$props[$p_name] = $p_name;
		}
		ksort($props);
		
		return $props;
	}
	
	public function initProperty($property)
	{
		// we need to make sure we have the component for this
	}
	
	/**
	 * @api.enable
	 */
	public static function SyncAdmin($property = null)
	{
		$properties = $property ? [$property] : static::getProperties();
		if (!$properties)
			return false;
		
		foreach ($properties as $prop)
		{
			$config = [];
			// sync that one
			$config["from"] = $prop;
			$config["className"] = static::GetClassNameForProperty($prop); // (includes namespace)
			$save_dir = \QAutoload::GetRuntimeFolder()."temp/~admin/".ucfirst($prop);
			if (!is_dir($save_dir))
				qmkdir($save_dir);
			$config["classPath"] = $save_dir."/".ucfirst($prop).".php";
					
			Omi\Gens\Grid::Generate($config);
		}
	}
	
	public static function GetClassNameForProperty($property = null)
	{
		return "Omi\\Dev\Admin\\".ucfirst($property);
	}
}
