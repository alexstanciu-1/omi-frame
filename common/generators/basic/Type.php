<?php

namespace Omi\Gens;

class Type implements IGenerator
{
	public static $ForCollection = false;
	
	public static function GenerateConfig($watch_folder, $batch_id, $className, $prop_name, $namespace, $prefix, $autosync, $autosyncadd, $autosyncremove)
	{
		if ($prop_name)
		{
			$class_short = $prop_name;
			
			$property_class = \QModel::GetTypeByName($className);
			$property = $property_class->properties[$prop_name];
			$types = is_array($property->types) ? $property->types : [$property->types];
		}
		else
		{
			$class_short = is_array($className) ? reset($className) : $className;
			list($class_short, $class_namespace) = qClassShortAndNamespace($class_short);
		}
		
		$listed_class = reset($types);
		if ($listed_class instanceof \QModelAcceptedType)
			$listed_class = reset($listed_class->options);
		
		$selector_str = $listed_class::GetModelEntity();
		$selector = is_string($selector) ? $selector : qParseEntity($selector_str);

		// var_dump("$className::$prop_name for ".$listed_class." // ".$class_short);
		
		$config = [];
		$config["generator"] = "Omi\\Gens\\Type";
		$config["classPath"] = $watch_folder."view/{$class_short}/";
		$config["className"] = $namespace."\\View\\".$class_short;
		$config["name"] = $config["className"];
		$config["extends"] = "Omi\\View\\Type";
		$config["selector"] = $selector;
		
		//var_dump($className, $prop_name);
		
		$config["generatedFor"] = $prop_name ? $className."::".$prop_name : $className."";
		return [$config["className"] => [$config["generator"] => $config]];
	}
	
	public static function Generate($config, $write_to_file = true)
	{
		// this we handle here
		$path = $config["classPath"];
		$class = $config["className"];
		$extends = $config["extends"];
		
		$tpl_tag = $config["templateTag"];
		
		list($short_class, $namespace) = qClassShortAndNamespace($class);
		
		$selector = $config["selector"];
		
		$tpl = ($write_to_file ? "<div q-args=\"\$Item = null\"".($namespace ? " q-namespace=\"{$namespace}\"" : "").($extends ? " extends=\"\\{$extends}\"" : "").">\n" : 
			"<div>\n")."
	".get_called_class()." generated {$class}<br/>
	{$path}\n";
	
	if ($selector)
	{
		foreach ($selector as $prop_name => $sub_selector)
		{
			if ($prop_name === "*")
				continue;
			// work in progress
			$tpl .= "\t<div>{$prop_name}: {{\$Item ? \$Item->{$prop_name} : \"\"}}</div>\n";
		}
	}
	
$tpl .= "</div>";
		
		if ($write_to_file)
		{
			if (!is_dir($path))
				qmkdir($path);
			// var_dump($path.$short_class.".tpl");
			\QCodeSync::filePutContentsIfChanged($path.$short_class.($tpl_tag ? ".".$tpl_tag : "").".tpl", $tpl);
		}
		
		return $tpl;
	}
}
