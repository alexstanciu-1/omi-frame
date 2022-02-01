<?php

final class QFiltersComp
{
	/**
	 * @var array
	 */
	protected $config;
	
	public static function Create(array $config)
	{
		$inst = new static;
		$inst->config = $config;
		
		return $inst;
	}
	
	public function run($data, $search_data = null)
	{
		$keep_items = [];
		$possible_filters = [];
		$all_options = [];
		
		foreach ($data ?: [] as $i_key => $item)
		{
			if ($search_data === null)
				$keep_items[$i_key] = $item;
			else
			{
				$is_valid = true;
				foreach ($this->config['fields'] ?: [] as $f_name => $field)
				{
					$ok = static::respects_filter($item, $f_name, $field, $search_data);					
					if (!$ok)
					{
						$is_valid = false;
						break;
					}
				}
				if ($is_valid)
					$keep_items[$i_key] = $item;
			}
		}

		foreach ($this->config['fields'] ?: [] as $f_name => $field)
		{
			foreach ($data ?: [] as $i_key => $item)
			{
				$is_valid = true;
				# obtain $possible_filters
				foreach ($this->config['fields'] ?: [] as $fs_k => $field_sub)
				{					
					if ($fs_k === $f_name)
						continue;
					
					$ok = static::respects_filter($item, $fs_k, $field_sub, $search_data);
				}
				
				$value = static::extract_value($item, $f_name, $field);
				
				if ($is_valid)
				{
					# @todo
					if ($field['@pattern'] === 'like')
					{
						# nothing to do
					}
					else if ($field['@pattern'] === 'options')
					{
						$possible_filters[$f_name][$value] = $value;
					}
					else if ($field['@pattern'] === 'range')
					{
						if ((!isset($possible_filters[$f_name]['min'])) || ($possible_filters[$f_name]['min'] > $value))
							$possible_filters[$f_name]['min'] = $value;
						if ((!isset($possible_filters[$f_name]['max'])) || ($possible_filters[$f_name]['max'] < $value))
							$possible_filters[$f_name]['max'] = $value;
					}
					else
					{
						qvar_dumpk('@todo - this one', $field);
						die;
					}
				}
				
				if ($field['@pattern'] === 'like')
				{
					# nothing to do
				}
				else if ($field['@pattern'] === 'options')
				{
					$all_options[$f_name][$value] = $value;
				}
				else if ($field['@pattern'] === 'range')
				{
					if ((!isset($all_options[$f_name]['min'])) || ($all_options[$f_name]['min'] > $value))
						$all_options[$f_name]['min'] = $value;
					if ((!isset($all_options[$f_name]['max'])) || ($all_options[$f_name]['max'] < $value))
						$all_options[$f_name]['max'] = $value;
				}
			}
		}
		
		# qvar_dumpk('$possible_filters', $possible_filters, $all_options);
		# die("zzzqrwer");
		
		return [$keep_items, $possible_filters, $all_options];
	}
	
	protected static function extract_value($item, string $field_name, array $field_config)
	{
		if ($item === null)
			return null;
		else if (is_array($item))
			return $item[$field_name];
		else if (is_object($item))
			return $item->$field_name;
		else 
			throw new \Exception('Bad data.');
	}
	
	protected static function respects_filter($item, string $field_name, array $field_config, $search_data)
	{
		switch ($field_config['@pattern'])
		{
			case 'like':
			case 'options':
			{
				$search_field = $field_config['@search-name'] ?? $field_name;
		
				$search_requirement = (is_object($search_data) ? (isset($search_data->$search_field) ? $search_data->$search_field : null) : 
						(isset($search_data[$search_field]) ? $search_data[$search_field] : null));
				if ($search_requirement === null)
					return true;
				break;
			}
		}
				
		$value = static::extract_value($item, $field_name, $field_config);
		
		if ($field_config['@pattern'] === 'like')
		{
			# $reg_ex = "/". preg_replace(["/[^\\w]/uis"], $field_config, $field_name)."/uis";
			if (empty($search_requirement) || (!trim($search_requirement)))
				return true;
			else if ($value === null)
				return false;
			else if (strpos(strtolower($value), trim(strtolower($search_requirement))) === false)
				return false;
			else
				return true;
		}
		else if ($field_config['@pattern'] === 'options')
		{
			if (is_object($search_data))
				$search_data_arr = $search_data->toArray();
			
			return in_array($value, $search_data_arr[$search_field]);
		}
		else if ($field_config['@pattern'] === 'range')
		{
			$search_field_min = $field_config['@search-min'];
			$search_field_max = $field_config['@search-max'];
			
			$search_requirement_min = (is_object($search_data) ? (isset($search_data->$search_field_min) ? $search_data->$search_field_min : null) : 
						(isset($search_data[$search_field_min]) ? $search_data[$search_field_min] : null));
			$search_requirement_max = (is_object($search_data) ? (isset($search_data->$search_field_max) ? $search_data->$search_field_max : null) : 
						(isset($search_data[$search_field_max]) ? $search_data[$search_field_max] : null));
			
			if (empty($search_requirement_min) && empty($search_requirement_max))
				return true;
			else if ($value === null)
				return false;
			else if (($value >= $search_requirement_min) && ($value <= $search_requirement_max))
				return true;
			else
				return false;
		}
	}
	
	public static function Test()
	{
		$instance = \QFiltersComp::Create([
				'fields' => [
					'Property_Name' => [
						# '@type' => 'text',
						'@pattern' => 'like',
						'@search-name' => 'Name',
					],
					
					'Property_Stars' => [
						'@type' => 'enum',
						'@options' => $property_stars_options,
						'@pattern' => 'options',
					],
					
					'Total_Price' => [
						'@type' => 'float',
						'@pattern' => 'range',
						'@search-min' => 'Price_From',
						'@search-max' => 'Price_To',
					],
					
					'Property_Building_Info_Total_Rooms' => [
						'@type' => 'int',
						'@pattern' => 'range',
					],
					
					'Property_Type' => [
						'@type' => 'enum',
						'@options' => $property_type_options,
						'@pattern' => 'options',
					],
					
					'Meal_Type' => [
						'@type' => 'enum',
						'@options' => $meal_options,
						'@pattern' => 'options',
					],
					/*
					'Facilities' => [
						'@type' => 'xxxx',
						'@pattern' => 'options',
					],
					'Room_Facilities' => [
						'@type' => function ($data_item) {  } ,
						'@pattern' => 'options',
					],
					*/
				]
			]);
			
			list($results_items, $possible_filters, $all_filters) = $instance->run($property_offers, $search_data);
	}
	
}

