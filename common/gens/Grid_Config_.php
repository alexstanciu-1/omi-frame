<?php

namespace Omi\Gens;

trait Grid_Config_
{
	public static function Config_Property_Get_Defs()
	{
		return [
			// "test.displayXYZ" => [],
			
			"label.display" => [],
			"input.type" => [],
			"input.max" => [],
			"input.min" => [],
			"listing.link" => [],
			"listing.caption" => [],
			"listing.mobile_hide_column" => [],
			
			"checkbox.extraLabel" => [],
			"view.style" => [],
			"dropdown.action_args" => [],
			"misc_elem" => [],
			"misc_elem_logo" => [],
			"hidden.if" => [],
			"hidden.btn_view" => [],
			"collection.popup.edit" => [],
			"collection.hide.add" => [],
			"collection.hide.delete" => [],
		];
	}
	
	public static function Config_View_Get_Defs()
	{
		return [
			"misc_elem" => [],
			"steps" => [],
			"test_prop" => [],
			"popup_width" => [],
			"list_checkboxes" => [],
			"show_bulk_button" => [],
			];
	}
	
	public static function Get_Search_Interval_Info(string $tag)
	{
		switch ($tag)
		{
			
		}
		# list ($sm_interval_min, $sm_interval_max, $sm_interval_tag) = static::Get_Search_Interval_Info($sm_tag);
	}
	
	
	public static function Get_Search_2_Input_Type(string $search_type, string $search_data_type, array $sm_data)
	{
		switch ($search_data_type)
		{
			case 'int':
			case 'float':
			case 'string':
				return 'text';
			case 'time':
				return 'time';
			case 'date':
				return 'date';
			case 'datetime':
				return 'datetime-local';
			default:
				return 'text';
		}
	}
	
	
	# $sm_input_type = static::Get_Search_2_Input_Type($search_type, $search_data_type, $sm_data);
}

