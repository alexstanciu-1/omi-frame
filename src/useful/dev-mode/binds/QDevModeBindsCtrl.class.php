<?php


/**
 * @class.name QDevModeBindsCtrl
 */
abstract class QDevModeBindsCtrl_frame_ extends QWebControl
{
	
	
	public function init($recurse = true)
	{
		parent::init($recurse);
		
		$this->autoloadData = QAutoload::GetAutoloadData();
		// var_dump($this->autoloadData);
	}

	/**
	 * @api.enable
	 * 
	 * @param string|string[] $classes
	 * @param integer $depth
	 * @param boolean $is_root
	 * @param array[] $selector
	 * @return string
	 */
	public function getBindsSelector($classes, $depth = 2, $is_root = true, &$selector = null)
	{
		if ($depth < 1)
			return;
		
		if (!is_array($classes))
			$classes = [$classes => $classes];
		
		if ($selector === null)
			$selector = [];
		
		$prop_ref_types = [];
		$props = [];
		
		foreach ($classes as $class)
		{
			$mi = QModelQuery::GetTypesCache($class);
			if (!$mi)
				continue;
			next($mi);
			next($mi);
			next($mi);

			while (($prop_data = next($mi)))
			{
				$prop_name = key($mi);
				$props[$prop_name] = $prop_name;
				
				if (($refrence_types = $prop_data["#"]))
				{
					foreach ($refrence_types as $rty)
						$prop_ref_types[$prop_name][$rty] = $rty;
				}
				if (($collection_types = $prop_data["[]"]))
				{
					if (($collection_reference_types = $collection_types["#"]))
					{
						foreach ($collection_reference_types as $crty)
							$prop_ref_types[$prop_name][$crty] = $crty;
					}
				}
			}
		}
		
		foreach ($props as $prop)
		{
			$selector[$prop] = [];
			if (($ref_ty = $prop_ref_types[$prop]))
				$this->getBindsSelector($ref_ty, $depth - 1, false, $selector[$prop]);
		}
		
		if ($is_root)
		{
			// implode selector
			$selector = qImplodeEntityFormated($selector);
		}
		
		return $selector;
	}
	
	/**
	 * @api.enable
	 * 
	 * @param string|string[] $classes
	 * @param array[] $selector
	 * @return string
	 */
	public function getGeneratedBinds($classes, $selector = null, $depth = 0, $is_root = true)
	{
		if ($depth > 24)
			throw new Exception("Max depth exceeded.");
		
		if ($selector === null)
			$selector = $this->getBindsSelector($classes);
		
		$selector = is_string($selector) ? qParseEntity($selector) : $selector;
		
		if (!is_array($classes))
			$classes = [$classes => $classes];
		
		$prop_types = [];
		$prop_ref_types = [];
		$props = [];
		$props_selector = [];
		
		foreach ($classes as $class)
		{
			$mi = QModelQuery::GetTypesCache($class);
			if (!$mi)
				continue;
			next($mi);
			next($mi);
			next($mi);

			while (($prop_data = next($mi)))
			{
				$prop_name = key($mi);
				if (!(is_array($selector[$prop_name]) || is_array($selector["*"])))
					continue;
				
				$props_selector[$prop_name] = ($selector[$prop_name] !== null) ? $selector[$prop_name] : $selector["*"];
				
				$props[$prop_name] = $prop_name;
				
				if (($scalar_types = $prop_data["\$"]))
				{					
					foreach ($scalar_types as $sty)
						$prop_types[$prop_name][$sty] = $sty;
				}
				if (($refrence_types = $prop_data["#"]))
				{
					foreach ($refrence_types as $rty)
					{
						$prop_types[$prop_name][$rty] = $rty;
						$prop_ref_types[$prop_name][$rty] = $rty;
					}
				}
				if (($collection_types = $prop_data["[]"]))
				{
					if (($collection_sclar_types = $collection_types["\$"]))
					{
						foreach ($collection_sclar_types as $csty)
							$prop_types[$prop_name][$csty."[]"] = $csty."[]";
					}
					else if (($collection_reference_types = $collection_types["#"]))
					{
						foreach ($collection_reference_types as $crty)
						{
							$prop_types[$prop_name][$crty."[]"] = $crty."[]";
							$prop_ref_types[$prop_name][$crty] = $crty;
						}
					}
				}
			}
		}
		
		$ret = "";
		if ($is_root)
		{
			$depth++;
			$ret .= "<div qb='\$variable(".reset($classes).")'>\n";
		}
		
		foreach ($props as $prop)
		{
			$prop_type = reset($prop_types[$prop]);
			$is_collection = substr($prop_type, -2, 2) === "[]";
			$prop_type = $is_collection ? substr($prop_type, 0, -2) : $prop_type;
			
			$is_scalar = (strtolower($prop_type{0}) === $prop_type{0});
			$go_deeper = $props_selector[$prop] ? true : false;
			
			if ($is_collection)
			{
				$ret .= str_pad("", $depth, "\t")."<div qb='.{$prop}[+]'>\n";
				$ret .= str_pad("", $depth + 1, "\t")."<div qb='.({$prop_type})'>\n";
			}
			else if ($is_scalar)
				$ret .= str_pad("", $depth, "\t")."<input type='text' qb='.{$prop}({$prop_type})' />\n";
			else
			{
				// we could do a lot of things
				$ret .= str_pad("", $depth, "\t")."<div qb='.{$prop}({$prop_type})'>";
				if ($go_deeper)
					$ret .= "\n";
			}
			
			if ($prop_ref_types[$prop])
			{
				$ret .= $this->getGeneratedBinds($prop_ref_types[$prop], $props_selector[$prop], $depth + ($is_collection ? 2 : 1), false);
			}
			/*$selector[$prop] = [];
			if (($ref_ty = $prop_ref_types[$prop]))
				$this->getBindsSelector($ref_ty, $depth - 1, false, $selector[$prop]);
			 */
			if ($is_collection)
			{
				$ret .= str_pad("", $depth + 1, "\t")."</div>\n";
				$ret .= str_pad("", $depth, "\t")."</div>\n";
			}
			else if ($is_scalar)
			{
				// nothing
			}
			else
			{
				if ($go_deeper)
					$ret .= str_pad("", $depth, "\t");
				$ret .= "</div>\n";
			}
		}
		
		if ($is_root)
			$ret .= "</div>\n";
		
		return $ret;
	}
}