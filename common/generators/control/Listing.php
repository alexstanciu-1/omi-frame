<?php

namespace Omi\Gens;

class Listing implements IGenerator
{
	public static $ForCollection = true;
	public static $DefaultClassParent = "Omi\View\Listing";
	
	public static function GenerateConfig($watch_folder, $batch_id, $className, $prop_name, $namespace, $prefix, $autosync, $autosyncadd, $autosyncremove, $selector = null)
	{
		$types = [];
		$property = null;
		$property_class = null;
		
		// here we need to prepare the heading & misc
		$class_short = "";
		if ($prop_name)
		{
			$property_class = \QModel::GetTypeByName($className);
			$property = $property_class->properties[$prop_name];
			$types = is_array($property->types) ? $property->types : [$property->types];
			
			$class_short = $prop_name;
		}
		else
		{
			$types = is_array($className) ? $className : [$className];
			
			$class_short = reset($types);
			list($class_short, $class_namespace) = qClassShortAndNamespace($class_short);
		}
		
		$class_short = str_replace(" ", "", ucwords(preg_replace("/[^\\w0-9\\_]/us", " ", $class_short)));
		
		$config = [];
		$config["generator"] = "Omi\\Gens\\Listing";
		$config["classPath"] = $watch_folder."view/{$class_short}/";
		$config["className"] = $namespace."\\View\\".$class_short;
		$config["name"] = $config["className"];
		$config["extends"] = "Omi\\View\\Listing";
		
		$collection = Collection::GenerateConfig($watch_folder, $batch_id, $config["className"], null, $class_namespace, $prefix, $autosync, $autosyncadd, $autosyncremove);
		$collection = reset(reset($collection));
		
		$collection["templateTag"] = "list";
		
		if ($selector === null)
		{
			$listed_class = reset($types);
			if ($listed_class instanceof \QModelAcceptedType)
				$listed_class = reset($listed_class->options);
			
			$selector_str = $listed_class::GetListingEntity();
			$selector = is_string($selector) ? $selector : qParseEntity($selector_str);
			
			$config["selector"] = $selector;
			
			$analyze_res = \QQueryAnalyzer::Analyze($listed_class::GetListingQuery(), $listed_class);
			
			if (($lb = $analyze_res["_q"]["LIMIT"]["binds"]))
			{
				foreach ($lb as $bd)
				{
					if ($bd["before_comma"])
						$config["LIMIT_offset_index"] = $bd["path"];
				}
			}
		}
		list (, $class_namespace) = qClassShortAndNamespace($config["className"]);
		
		$collection["selector"] = $selector;
		
		// var_dump($config);
		
		$controller = Controller::GenerateConfig($watch_folder, $batch_id, $config["className"], null, $class_namespace, $prefix, $autosync, $autosyncadd, $autosyncremove);
		$controller = reset(reset($controller));
		
		
		$controller["urls"]["index"] = 
				["load" => "
							\$q_binds = [0];
							\$this->setArguments([\\{$listed_class}::QueryAll(null, \$q_binds)], \"render\");
							return true;"];
		
		$controller["className"] = $config["className"];
		$controller["classPath"] = $config["classPath"];
		$controller["extends"] = $config["extends"];
		$controller["name"] = $config["name"];
		/*
		$controller["load"] = "";
		$controller["unload"] = "";
		unset($controller["index"]);l,notfound
		*/

		// then set: load,unload,index,notfound
		// var_dump($controller);
		
		foreach ($types as $_type)
		{
			/*
			// test if is collection ?!
			if ($_type instanceof \QModelAcceptedType)
			{
				foreach ($_type->options as $type)
					$config[$type] = static::GenerateTypeConfig($watch_folder, $batch_id, $type, $namespace, $prefix, $autosync, $autosyncadd, $autosyncremove, $selector, $className, $prop_name);
			}
			else 
			{
				$type = (substr($_type, -2, 2) === "[]") ? substr($_type, 0, -2) : $_type;
				$config[$type] = static::GenerateTypeConfig($watch_folder, $batch_id, $type, $namespace, $prefix, $autosync, $autosyncadd, $autosyncremove, $selector, $className, $prop_name);
			}
			 */
		}
	
		// we do it for this type's
		
		
		// we will need to extract listing selector + listing query
		
		// then for one element ... bla bla
		
		/*
		if ($prop_name)
		{
			$m_type = \QModel::GetTypeByName($className);
			$property = $m_type->properties[$prop_name];
			if (($acc_type = $property->getCollectionType()))
				return static::GenerateTypeConfig($watch_folder, $batch_id, $acc_type->getReferenceTypes(), $namespace, $prefix, $autosync, $autosyncadd, $autosyncremove, $className, $prop_name);
			else
				return static::GenerateTypeConfig($watch_folder, $batch_id, $property->getReferenceTypes(), $namespace, $prefix, $autosync, $autosyncadd, $autosyncremove, $className, $prop_name);
			// static::GeneratePropertyConfig($watch_folder, $batch_id, $className, $prop_name, $namespace, $prefix, $autosync, $autosyncadd, $autosyncremove);
		}
		else
			return static::GenerateTypeConfig($watch_folder, $batch_id, $className, $namespace, $prefix, $autosync, $autosyncadd, $autosyncremove);
		 */
		
		//var_dump($collection);
		
		$config["collection"] = $collection;
		$config["generatedFor"] = $prop_name ? $className."::".$prop_name : $className."";
		$controller["generatedFor"] = $prop_name ? $className."::".$prop_name : $className."";
		
		$return = [];
		$return[$config["className"]][$config["generator"]] = $config;
		$return[$controller["className"]][$controller["generator"]] = $controller;
		
		return $return;
	}
	
	public static function GeneratePropertyConfig($watch_folder, $batch_id, $className, $prop_name, $namespace, $prefix, $autosync, $autosyncadd, $autosyncremove)
	{
		// var_dump("Listing::GeneratePropertyConfig: $className, $prop_name");
		$m_type = \QModel::GetTypeByName($className);
		$property = $m_type->properties[$prop_name];
		return static::GenerateTypeConfig($watch_folder, $batch_id, $property->getReferenceTypes(), $namespace, $prefix, $autosync, $autosyncadd, $autosyncremove, $className, $prop_name);
	}
	
	public static function GenerateTypeConfig($watch_folder, $batch_id, $type, $namespace, $prefix, $autosync, $autosyncadd, $autosyncremove, $selector = null, $parent_class = null, $parent_property = null)
	{
		//var_dump("Listing::GenerateTypeConfig: $types");
		// if we don't have a property, we assume we need a collection for the specified type(s)
		// $className may also be a collection
		
		// let's propose a config
		$conf = [];
		
		$first_type = $type;
		
		/*foreach ($types as $type)
		{
			$selector = $type::GetListingEntity();
			// @todo: intersect selectors
			break;
		}*/
		
		$conf["name"] = ($parent_property ? $parent_property : str_replace(["\\", "[]"], ["", ""], $first_type));
		$short_className = $conf["name"]."List";
		$conf["className"] = ($namespace ? rtrim($namespace, "\\")."\\" : "").trim($prefix).$short_className;
		$conf["classPath"] = "_generated/view/{$short_className}/";
		$conf["classParent"] = static::$DefaultClassParent;
		$conf["editable"] = false;
		$conf["selector"] = $first_type::GetListingEntity();
		if (is_string($conf["selector"]))
			$conf["selector"] = qParseEntity($conf["selector"]);
		$conf["query"] = $first_type::GetListingQuery();
		$conf["query.context"] = ["type" => $first_type];
		$conf["query.params"] = []; // from API also 
		
		$conf["heading"] = $conf["name"];
		$conf["checkbox.per.item"] = true;
		
		$conf["controller"] = "url";
		// $conf["controller"] = "ajax";
		// $conf["controller"] = "json";
		
		$conf["paginator.mode"] = "url";
		// $conf["paginator.mode"] = "more/ajax";
		// $conf["paginator.mode"] = "more/json";
		
		// actions : @todo
		$actions = ["add", "edit", "view", "delete"];
		foreach ($actions as $action)
		{
			// we will need to call \Omi\Gens\Type foreach action (we need to organize a bit better (this is redundant)
			// group add/edit ; view/delete (under the same view)
		}
		// add,edit,view,delete
		
		if ($conf["selector"])
		{
			// foreach selector ... generate config for property
			foreach ($conf["selector"] as $prop => $sub_selector)
			{
				// for collections & references ... ways to integrate them in
				
			}
		}
		
		// next loop selector : Properties
				
		return $conf;
		
		/*
			name

			classPath
			className
			classParent
			editable
			selector

			* query
				context (for selector)
				params
				query

			heading

			* actions
				- name
				- *child view/generator
				- what to do -> controller action / 
						switch to another view / 
						trigger some actual action

			Properties
				- caption
				- group
				- image_preview
				- visibility (view/edit/none)
				- input type : htmlMode 

				dropdown 
					- FromProperty
					- Properties
					- renderByModelCaption (boolean)


		 */
	}
	
	public static function Generate($config)
	{
		// this we handle here
		$path = $config["classPath"];
		$class = $config["className"];
		$extends = $config["extends"];
		
		list($short_class, $namespace) = qClassShortAndNamespace($class);
		
		$selector = $config["selector"];
		$LIMIT_offset_index = $config["LIMIT_offset_index"];
		
		$tpl = 
"<div q-args=\"\$Data = null\"".($namespace ? " q-namespace=\"{$namespace}\"" : "").($extends ? " extends=\"\\{$extends}\"" : "").">
	".get_called_class()." generated {$class}<br/>
	{$path}<br/>\n";
	
	if ($selector)
	{
		foreach ($selector as $prop_name => $sub_selector)
		{
			// work in progress
			$tpl .= "\t<div>{$prop_name}</div>\n";
		}
	}
	
	if ($config["collection"])
		$tpl .= "\t<?php \$this->render".(ucfirst($config["collection"]["templateTag"]))."(\$Data); ?>\n";
	
$tpl .= "</div>";

		// we also need a controller, with some kind of defatult tag

		// setup a config, then call Controller::Generate
		if ($config["controller"])
			Controller::Generate($config["controller"]);
		if ($config["collection"])
			Collection::Generate($config["collection"]);
		
		if (!is_dir($path))
			qmkdir($path);
		//var_dump($path.$short_class.".tpl");
		\QCodeSync::filePutContentsIfChanged($path.$short_class.".tpl", $tpl);
	}
}
