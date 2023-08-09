<?php

namespace Omi;

/**
 * @storage.table $App
 *
 * @class.name App
 */
abstract class App_dk_model_ extends App_mods_model_
{
	/**
	 * @var \Omi\DK\Project[]
	 */
	protected $Projects;
	
	/**
	 * Returns the entity that will be used when listing views are generated
	 * @param string $viewTag
	 */
	public static function GetEntityForGenerateList($viewTag = null)
	{
		$ret = null;
		switch ($viewTag)
		{
			case "Projects":
			{
				$ret = qParseEntity('Name,Path,Exec_Path,Dev_URL');
                break;
			}
			// DEFAULT
			default :
			{
				$ret = parent::GetEntityForGenerateList($viewTag);
				break;
			}
		}
		
		// return properties
		return $ret;
	}
	
	/**
	 * Returns the entity that will be used when listing views are generated
	 * @param string $viewTag
	 */
	public static function GetEntityForGenerateForm($viewTag = null)
	{
		$ret = null;
		switch ($viewTag)
		{
			case "Projects":
			{
				$ret = qParseEntity('Name,Path,Exec_Path,Dev_URL');
                break;
			}
			// DEFAULT
			default :
			{
				$ret = parent::GetEntityForGenerateForm($viewTag);
				break;
			}
		}
		
		// return properties
		return $ret;
	}
}
