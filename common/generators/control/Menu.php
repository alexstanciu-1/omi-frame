<?php

namespace Omi\Gens;

class Menu implements IGenerator
{
	public static $ForCollection = true;
	public static $DefaultClassParent = "Omi\View\Listing";
	
	public static function GenerateConfig($watch_folder, $batch_id, $className, $prop_name, $namespace, $prefix, $autosync, $autosyncadd, $autosyncremove, $include_properties)
	{
		$menu = [];
		$menu["className"] = $namespace."\\View\\Menu";
		$menu["classPath"] = $watch_folder."view/Menu/";
		$menu["extends"] = "Omi\\View\\Menu";
		
		$menu["generator"] = get_called_class();
		$menu["name"] = "Menu";
		
		$menu["items"] = [];
		// fixed url for home
		$menu["items"][] = ["url" => "", "caption" => "Home"];
		
		foreach ($include_properties as $property)
		{
			// setup an element
			$include_className = $namespace."\\View\\".ucfirst($property);
		
			// we need a default ... getUrl ... without tag
			$menu["items"][] = ["class" => $include_className, "tag"=> $property, "caption" => $property];
		}
		
		$menu["generatedFor"] = $prop_name ? $className."::".$prop_name : $className."";
		
		return [$menu["classPath"] => [$menu["generator"] => $menu]];
	}
	
	public static function Generate($config)
	{
		$className = $config["className"];
		$menu_path = $config["classPath"];
		$name = $config["name"];
		$items = $config["items"];
		$extends = $config["extends"];
		
		$controllerClass = $config["controllerClass"];
		
		list($menu_short_class, $menu_namespace) = qClassShortAndNamespace($className);
		
		/*
			$items=>	'url' => string '' (length=0) | 
						[or]: class & tag
				'caption' => string 'Home' (length=4)
		 */
		
		$tpl_str = 
"<nav".($menu_namespace ? " q-namespace=\"".htmlspecialchars($menu_namespace)."\"" : "").($extends ? " extends=\"\\{$extends}\"" : "").">
	<ul>\n";
	
		if ($items)
		{
			foreach ($items as $item)
			{
				$mi_caption = $item["caption"];
				$mi_url = $item["url"];
				$mi_class = $item["class"];
				$mi_tag = $item["tag"];
				$str_url = ($mi_url !== null) ? "\"".qaddslashes($mi_url)."\"" : qClassRelativeToNamespace($controllerClass ?: $mi_class, $menu_namespace)."::GetUrl(\"".qaddslashes($mi_tag)."\")";
				
				if ($mi_url !== null)
				{
					$tpl_str .= 
"		<li>
			@if (\$this->isSelected(\"".qaddslashes($mi_url)."\"))
				{$mi_caption}
			@else
				<a href=\"".qaddslashes($mi_url)."\">{$mi_caption}</a>
			@endif
		</li>
";
				}
				else
				{
					$tpl_str .= 
"		<li>
			@var \$url = {$str_url};
			@if (\$this->isSelected(\$url))
				{$mi_caption}
			@else
				<a href=\"{{\$url}}\">{$mi_caption}</a>
			@endif
		</li>
";
				}
			}
		}
		
		$tpl_str .= 
"	</ul>
</nav>";
		
		if (!is_dir($menu_path))
			qmkdir($menu_path);
		\QCodeSync::filePutContentsIfChanged($menu_path.$menu_short_class.".tpl", $tpl_str);
	}

}
