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
	
	public function run($data, array $search_data = null)
	{
		$keep_items = [];
		$possible_filters = [];
		
		foreach ($this->config['fields'] ?: [] as $field)
		{
			foreach ($data ?: [] as $item)
			{
				$field_type = $field['@type'];
				$field_pattern = $field['@type'];
			}
		}
		
		return [$keep_items, $possible_filters];
	}
	
	public static function Test()
	{
		$instance = static::Create([
			'fields' => [
				'Name' => [
					'@type' => 'text',
					'@pattern' => 'like',
				],
				'Stars' => [
					'@type' => 'enum',
					'@pattern' => 'options',
				],
				'Price' => [
					'@type' => 'float',
					'@pattern' => 'range',
				],
				'Rooms_Count' => [
					'@type' => 'int',
					'@pattern' => 'range',
				],
				'Property_Type' => [
					'@type' => 'enum',
					'@pattern' => 'options',
				],
				'Meal_Type' => [
					'@type' => 'enum',
					'@pattern' => 'options',
				],
				'Facilities' => [
					'@type' => 'xxxx',
					'@pattern' => 'options',
				],
				'Room_Facilities' => [
					'@type' => 'xxxx',
					'@pattern' => 'options',
				],
			]
		]);
		
		$results = $instance->run([
			
		]);
		
		qvar_dumpk($results);
	}
	
}

