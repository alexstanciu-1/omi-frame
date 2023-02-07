<?php

namespace Omi\Gens;

class Controller implements IGenerator
{
	public static $ForCollection = true;
	public static $DefaultClassParent = "Omi\View\Listing";
	
	public static function GenerateConfig($watch_folder, $batch_id, $className, $prop_name, $namespace, $prefix, $autosync, $autosyncadd, $autosyncremove, $include_properties = null)
	{
		$controller = [];
		
		$controller["generator"] = get_called_class();
		$controller["className"] = $namespace."\\Controller";
		$controller["classPath"] = $watch_folder."controllers/";
		$controller["extends"] = "\\QWebPage";
		$controller["implements"] = ["\\QIUrlController"];
		
		$controller["name"] = "Controller";
		
		$controller["prefix"] = "";
		
		if ($include_properties)
		{
			// language support
			$controller["urls"] = [];
			foreach ($include_properties as $property)
			{
				// setup an element
				$include = [];
				$include["className"] = $namespace."\\View\\".ucfirst($property);
				$include["var"] = "\$this->webPage->content";
				$controller["urls"]["url-{$property}"] = [
								"tag" => $property,
								"get" => ["translate" => $property],
								"include-{$property}" => $include
							];
			}
		}

		$controller["generatedFor"] = $prop_name ? $className."::".$prop_name : $className."";
		return [$controller["className"] => [$controller["generator"] => $controller]];
	}
	
	public static function Generate($config, $batch_config = null, $class_name = null, $urls = null, $depth = 0)
	{
		$url_str = "";
		
		$name = $config["name"];
		$className = $config["className"];
		list($short_class, $namespace) = qClassShortAndNamespace($className);
		$classPath = $config["classPath"];
		$extends = $config["extends"];
		$implements = $config["implements"];
		
		if ($depth === 0)
		{
			$prefix = $config["prefix"];
			$load = $config["load"];
			$unload = $config["unload"];

			$urls = $config["urls"];

	$url_str = 
"<urls".($extends ? " extends=\"".htmlspecialchars($extends)."\"" : "").
		($implements ? " implements=\"".htmlspecialchars(implode(",", $implements))."\"" : "").
		($namespace ? " q-namespace=\"".htmlspecialchars($namespace)."\"" : "").">
".($prefix ? "<prefix><?= ".trim(static::ConvertPrefixToString($prefix))." ?></prefix>" : "")."
";

				if ($load)
					$url_str .= "
				<?php
					{$load}
				?>\n";
		}
		
		if ($urls)
		{
			foreach ($urls as $k => $url)
			{
				if ((substr($k, 0, strlen("url-")) === "url-") || ($k === "index") || ($k === "notfound"))
				{
					$xml_tag_name = ($k === "index") ? "index" : (($k === "notfound") ? "notfound" : "url");
					
					/*
					$controller["urls"]["url-{$property}"] = [
							"tag" => $property,
							"get" => ["translate" => $property],
							"load" => 
							"include-{$property}" => $include]
						];
					 */
					$url_tag = $url["tag"];
					$url_get = $url["get"];
					$url_load = $url["load"];
					$url_unload = $url["unload"];
					
					$url_str .= "<{$xml_tag_name}".($url_tag ? " tag=\"".htmlspecialchars($url_tag)."\"" : "").">\n";
					if ($url_get)
					{
						if (is_array($url_get))
						{
							$url_get_php = $url_get["php"];
							$url_str .= "\t<get".($url_get["translate"] ? " translate=\"".htmlspecialchars($url_get["translate"])."\"" : "").
									($url_get_php ? "><?= ".$url_get_php." ?></get>" : " />");
						}
						else
							$url_str .= "\t{$url_get}\n";
					}
					
					$url_str .= self::Generate($config, $batch_config, $class_name, $url, $depth + 1);
					
					if ($url_load)
						$url_str .= "\t<load><?php {$url_load} ?></load>\n";
					else 
						// we need to set a load because if we only have includes in it (without load) the method will not be generated
						$url_str .= "\t<load><?php  ?></load>\n";
					if ($url_unload)
						$url_str .= "\t<unload><?php {$url_unload} ?></unload>\n";
					
					$url_str .= "</{$xml_tag_name}>\n";
				}
				else if (substr($k, 0, strlen("include-")) === "include-")
				{
					// we have an include
					// <include class="View\OrderCtrl" var="$this->ctrl" />
					$include_className = $url["className"];
					$include_var = $url["var"];
					$url_str .= "\n\t<include".($include_className ? " class=\"".htmlspecialchars(qClassRelativeToNamespace($include_className, $namespace))."\"" : "").
								($include_var ? " var=\"".htmlspecialchars($include_var)."\"" : "")." />\n";
				}
			}
		}
		
		if ($depth === 0)
		{
			if ($unload)
				$url_str .= "
			<?php
				{$unload}
			?>\n";
			$url_str .= "\n</urls>\n";
			
			if (!is_dir($classPath))
				qmkdir($classPath);
			\QCodeSync::filePutContentsIfChanged($classPath.$short_class.".url.php", $url_str);
		}
		
		return $url_str;
	}
	
	protected static function ConvertPrefixToString($prefix)
	{
		return $prefix."";
	}
}
