<?php

namespace Omi\Gens;

class Collection implements IGenerator
{
	public static $ForCollection = true;
	
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
		$config["generator"] = "Omi\\Gens\\Collection";
		$config["classPath"] = $watch_folder."view/{$class_short}/";
		$config["className"] = $namespace."\\View\\".$class_short;
		$config["name"] = $config["className"];
		$config["extends"] = "Omi\\View\\Collection";
		
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
		
		// @todo : @include(\Omi\Cms\View\Menu::item, args)
		//			=> \Omi\Cms\View\Menu::RenderS("item", args)
		
		$tpl = 
"<div q-args=\"\$List = null\"".($namespace ? " q-namespace=\"{$namespace}\"" : "").($extends ? " extends=\"\\{$extends}\"" : "").">
	".get_called_class()." generated {$class}<br/>
	{$path}<br/>
	@if (\$List)
	<div>
		@each (\$List as \$List_Item)
		<div>
		<?php \$this->renderView(\$List_Item); ?>
		</div>
		@endeach
	</div>
	@endif
</div>";
		
		if ($write_to_file)
		{
			if (!is_dir($path))
				qmkdir($path);
			// var_dump($path.$short_class.".tpl");
			\QCodeSync::filePutContentsIfChanged($path.$short_class.($tpl_tag ? ".".$tpl_tag : "").".tpl", $tpl);
		}
		
		return ["tpl" => ($tpl_tag ? [$tpl_tag => $tpl] : $tpl)];
	}
}
