<?php


/**
 * @class.name QDevModeModelCtrl
 */
abstract class QDevModeModelCtrl_frame_ extends QWebControl
{
	
	
	public $showClass = null;
	
	/**
	 * @api.enable
	 * 
	 * @param type $max_depth
	 * @param type $class_names
	 * @param type $depth
	 * @param type $visible
	 * @param type $toggles
	 * @param array $level_classes
	 * @return type
	 */
	public function getPrintData($max_depth = 1, $class_names = null, $depth = 0, $visible = true, $toggles = [], $level_classes = null, $toggle_index = 1)
	{
		if (!$max_depth)
			return;
		
		if ($class_names === null)
		{
			// $ext_by_inf = QAutoload::GetExtendedByList();

			$class_names = $this->showClass ? [$this->showClass] : [QApp::GetDataClass()];
		}
		if (!$class_names)
			return;
		
		if ($level_classes === null)
			$level_classes = [];
		
		$model_info = [];
		$prop_ref_types = [];
		$storage_info = [];
		$storage_info_keys = ["typetab", "scalarcol", "refcol", "typecol", "colltab", "colltype", "collback", "collfwd", "collfwd_ty"];
		
		$level_classes_per_prop = [];
		
		foreach ($class_names as $class)
		{
			$mi = QModelQuery::GetTypesCache($class);
			if (!$mi)
				continue;

			ksort($mi);
			$move_mi = null;
			if (($move_mi = $mi["Id"]))
				// move it first
				unset($mi["Id"]);
			
			while (($prop_data = ($move_mi ?: next($mi))))
			{
				$prop_name = $move_mi ? "Id" : key($mi);
				if (substr($prop_name, 0, 2) === "#%")
					continue;
				
				$move_mi = null;
				
				if (!$level_classes_per_prop[$prop_name])
					$level_classes_per_prop[$prop_name] = [];
				
				if (is_string($prop_data["rc_t"]))
					$storage_info[$prop_name]["typecol"] = $prop_data["rc_t"];
				
				if (($scalar_types = $prop_data["\$"]))
				{
					foreach ($scalar_types as $sty)
						$model_info[$prop_name][$sty][$class] = $class;
					$storage_info[$prop_name]["scalarcol"] = $prop_data["vc"];
				}
				if (($refrence_types = $prop_data["#"]))
				{
					foreach ($refrence_types as $rty)
					{
						$model_info[$prop_name][$rty][$class] = $class;
						$prop_ref_types[$prop_name][$rty] = $rty;
						
						$level_classes_per_prop[$prop_name][$rty] = $rty;
					}
					$storage_info[$prop_name]["refcol"] = $prop_data["rc"];
				}
				if (($collection_types = $prop_data["[]"]))
				{
					// var_dump($prop_data);
					//  "colltab", "collback", "collfwd", "collfwd_ty"
					$storage_info[$prop_name]["colltype"] = $prop_data["o2m"] ? "one to many" : "many to many";
					$storage_info[$prop_name]["colltab"] = $prop_data["ct"];
					$storage_info[$prop_name]["collback"] = $prop_data["cb"];
					$storage_info[$prop_name]["collfwd"] = $prop_data["cf"][0];
					if (!is_array($prop_data["cf"][1]))
						$storage_info[$prop_name]["collfwd_ty"] = $prop_data["cf"][1];
					else
						$storage_info[$prop_name]["collfwd_ty"] = "n/a";
					
					if (($collection_sclar_types = $collection_types["\$"]))
					{
						foreach ($collection_sclar_types as $csty)
							$model_info[$prop_name][$csty."[]"][$class] = $class;
					}
					else if (($collection_reference_types = $collection_types["#"]))
					{
						foreach ($collection_reference_types as $crty)
						{
							$model_info[$prop_name][$crty."[]"][$class] = $class;
							$prop_ref_types[$prop_name][$crty] = $crty;
							
							$level_classes_per_prop[$prop_name][$crty] = $crty;
						}
					}
				}
			}
		}
		
		foreach ($model_info as $prop_name => $prop_data_list)
		{
			$ref_types = $prop_ref_types[$prop_name];
			$is_scalar_only = (!$ref_types);
			$prop_toggle = $is_scalar_only ? null : "t_".($toggle_index++)."_";
			
			$go_deeper = true;
			if (!$is_scalar_only)
			{
				$go_deeper = false;
				foreach ($level_classes_per_prop[$prop_name] as $cn)
				{
					if (!$level_classes[$cn])
						$go_deeper = true;
				}
			}
			
			$storage_info[$prop_name]["typetab"] = "";
			if ($ref_types)
			{
				$multiple = (count($ref_types) > 1);
				$typetab_str = "";
				$tty_pos = 0;
				foreach ($ref_types as $rt)
				{
					$rti = QModelQuery::GetTypesCache($rt);
					if ($multiple)
					{
						if ($tty_pos)
							$typetab_str .= "<br/>";
						$typetab_str .= "{$rti["#%table"]}: {$rt}";
					}
					else
						$typetab_str .= $rti["#%table"];
					$tty_pos++;
				}
				$storage_info[$prop_name]["typetab"] = $typetab_str;
			}
			
			echo "<tr".((!$visible) ? " style='display:none;' data-state='off' " : "  data-state='on' ").
						(($is_scalar_only || (!$go_deeper)) ? "" : " data-toggle='{$prop_toggle}' ").
						($toggles ? " class='".implode(" ", $toggles)."'" : "").">";
			echo "<th><h5 ".(($is_scalar_only || (!$go_deeper)) ? "" : "class='clickable' data-classeslist='".implode(",", $ref_types)."' data-depth='{$depth}' data-toggle='{$prop_toggle}' ")." style='margin: 0;'><span style='color: transparent;'>".str_pad("", $depth * 7, "-")."</span>".
						(($is_scalar_only || (!$go_deeper)) ? "<i class='fa fa-caret-right transparent'></i>" : "<i class='fa fa-caret-right'></i>")." {$prop_name}</h5></th>";
			echo "<td>";
			$types = array_keys($prop_data_list);
			$pos = 0;
			foreach ($types as $_type)
			{
				$is_collection = substr($_type, -2, 2) === "[]";
				$type = $is_collection ? substr($_type, 0, -2) : $_type;
				
				$is_ref = strtolower($type{0}) !== $type{0};
				
				$short_class = (($p = strrpos($type, "\\")) !== false) ? substr($type, $p + 1) : $type;
				$short_class = trim($short_class);
				
				if ($pos)
					echo ", ";
				if ($is_ref)
					echo "<a href='".qUrl("classitem", $type)."' title='{$type}'>{$short_class}".($is_collection ? "[]" : "")."</a>";
				else
					echo $type;
				if (count($prop_data_list[$type]) > 1)
				{
					// handle info for multiple possible types defs 
				}
					
				$pos++;
			}
			echo "</td>";
			
			$storage_info_prop = $storage_info[$prop_name];
			if ($storage_info_prop)
			{
				foreach ($storage_info_keys as $k)
				{
					echo "<td>";
					echo $storage_info_prop[$k];
					echo "</td>";
				}
			}
			
			echo "</tr>";
			if ($ref_types && $go_deeper)
			{
				// $child_toggles = $toggles;
				$child_toggles = [$prop_toggle];
				
				foreach ($level_classes as $lc)
					$level_classes_per_prop[$prop_name][$lc] = $lc;
			
				$this->getPrintData($max_depth - 1, $ref_types, $depth + 1, false, $child_toggles, $level_classes_per_prop[$prop_name]);
			}
		}
	}
}