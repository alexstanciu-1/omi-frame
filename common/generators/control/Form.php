<?php

namespace Omi\Gens;

class Form implements IGenerator
{
	public static $ForCollection = false;
	
	public static function GenerateConfig($watch_folder, $batch_id, $className, $prop_name, $namespace, $prefix, $autosync, $autosyncadd, $autosyncremove)
	{
		if ($prop_name)
		{
			$class_short = $prop_name;
		}
		else
		{
			$class_short = is_array($className) ? reset($className) : $className;
			list($class_short, $class_namespace) = qClassShortAndNamespace($class_short);
		}
		
		$config = [];
		$config["generator"] = "Omi\\Gens\\Form";
		$config["classPath"] = $watch_folder."view/{$class_short}/";
		$config["className"] = $namespace."\\View\\".$class_short;
		$config["name"] = $config["className"];
		$config["extends"] = "Omi\\View\\Form";
		
		$config["generatedFor"] = $prop_name ? $className."::".$prop_name : $className."";
		return [$config["className"] => [$config["generator"] => $config]];
	}
	
	public static function Generate($config)
	{
		// this we handle here
		$path = $config["classPath"];
		$class = $config["className"];
		$extends = $config["extends"];
		
		$tpl_tag = $config["templateTag"];
		
		list($short_class, $namespace) = qClassShortAndNamespace($class);
		
		$tpl = 
"<div".($namespace ? " q-namespace=\"{$namespace}\"" : "").($extends ? " extends=\"\\{$extends}\"" : "").">
	".get_called_class()." generated {$class}<br/>
	{$path}\n";
	
	$tpl .= Type::Generate($config, false);
	
$tpl .= "</div>";
		
		if (!is_dir($path))
			qmkdir($path);
		// var_dump($path.$short_class.".tpl");
		\QCodeSync::filePutContentsIfChanged($path.$short_class.($tpl_tag ? ".".$tpl_tag : "").".tpl", $tpl);
	}
}