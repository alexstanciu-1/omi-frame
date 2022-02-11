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
		$possible_count = [];
		
		# unset($this->config['fields']['Cancellation_Policy']['_ty']);
		# qvar_dumpk($search_data, $this->config['fields']['Cancellation_Policy_Text']);
		# die;
		
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
					
					/*
					if (!$ok)
					{
						if ($f_name === 'Cancellation_Policy')
						{
							qvar_dumpk('FAIL ON : '.$f_name, $item, $field, $search_data);
							die;
						}
					}
					*/
					
					if (!$ok)
					{
						$is_valid = false;
						break;
					}
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
						
						if (!isset($possible_count[$f_name][$value]))
							$possible_count[$f_name][$value] = 0;
						$possible_count[$f_name][$value]++;
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
		
		# qvar_dumpk('$possible_filters', $possible_filters, $all_options, $possible_count);
		# die;
		# die("zzzqrwer");
		
		return [$keep_items, $possible_filters, $all_options, $possible_count];
	}
	
	protected static function extract_value($item, string $field_name, array $field_config, bool $for_search = false)
	{
		if ($item === null)
			return null;
		else if ($field_config['@getter'])
			return $field_config['@getter']($item, $field_name, $for_search);
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
				$search_requirement = static::extract_value($search_data, $field_name, $field_config, true);
				
				if ($search_requirement === null)
					return true;
				
				if (!is_array($search_requirement))
					$search_requirement = [$search_requirement];

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
			$ret = in_array($value, $search_requirement);
			return $ret;
		}
		else if ($field_config['@pattern'] === 'range')
		{
			$search_field_min = $field_config['@search-min'];
			$search_field_max = $field_config['@search-max'];
			
			$search_requirement_min =  isset($search_data[$search_field_min]) ? $search_data[$search_field_min] : null;
			$search_requirement_max = isset($search_data[$search_field_max]) ? $search_data[$search_field_max] : null;
			
			if (empty($search_requirement_min) && empty($search_requirement_max))
				return true;
			else if ($value === null)
				return false;
			else if ($search_requirement_min || $search_requirement_max)
			{
				if (($value >= $search_requirement_min) && ($value <= $search_requirement_max))
					return true;
				else if (isset($search_requirement_min) && ($value >= $search_requirement_min))
					return true;
				else if (isset($search_requirement_max) && ($value >= $search_requirement_max))
					return true;
			}
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
					
					'Available_Rooms' => [
						'@getter' => function ($data) { 
								return ($data->All_Rooms_Count < 10) ?  '1-9' : ($data->All_Rooms_Count < 20 ? '10-19' : '>19'); },
						'@options' => ['1-9', '10-19', '>19'],
						'@pattern' => 'options',
					]
					
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

