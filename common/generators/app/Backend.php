<?php

namespace Omi\Gens;

class Backend implements IGenerator
{
	public static $ForCollection = true;
	public static $DefaultClassParent = "Omi\View\Listing";
	
	public static function GenerateConfig($watch_folder, $batch_id, $className, $prop_name, $namespace, $prefix, $autosync, $autosyncadd, $autosyncremove, $include_properties)
	{
		$controller = Controller::GenerateConfig($watch_folder, $batch_id, $className, $prop_name, $namespace, $prefix, $autosync, $autosyncadd, $autosyncremove, $include_properties);
		$controller = reset(reset($controller));
		
		$controller["className"] = $namespace."\\Backend";
		$controller["classPath"] = $watch_folder."backend/";
		
		
		$menu = Menu::GenerateConfig($watch_folder, $batch_id, $className, $prop_name, $namespace, $prefix, $autosync, $autosyncadd, $autosyncremove, $include_properties);
		$menu = reset(reset($menu));
		
		$menu["className"] = $namespace."\\Backend\\View\\Menu";
		$menu["classPath"] = $watch_folder."backend/view/Menu/";
		
		$page = [];
		$page["className"] = $namespace."\\Backend\\View\\Backend";
		$page["classPath"] = $watch_folder."backend/view/Backend/";
		
		
		$controller["generatedFor"] = $prop_name ? $className."::".$prop_name : $className."";
		$menu["generatedFor"] = $prop_name ? $className."::".$prop_name : $className."";
		$page["generatedFor"] = $prop_name ? $className."::".$prop_name : $className."";
		
		$return = [];
		$return[$controller["className"]][$controller["generator"]] = $controller;
		$return[$menu["className"]][$menu["generator"]] = $menu;
		
		// the menu has options based on the controller
		return $return;
		// return ["name" => "Backend", "generator" => get_called_class(),  "controller" => $controller, "page" => $page, "menu" => $menu];
	}

	public static function Generate($config)
	{
		
	}
}