<?php

namespace Omi;

/**
 * @author Alex
 * 
 * @storage.table Languages
 *
 * @model.captionProperties Name
 * 
 * @class.name Language
 */
abstract class Language_mods_model_ extends \QModel implements \Omi\Owner_Interface
{
	use \Omi\Owner_Trait;
	
	/**
	 * @var string
	 * 
	 * @validation mandatory
	 * @fixValue trim
	 */
	protected $Code;
	/**
	 * @var string
	 * 
	 * @validation mandatory
	 * @fixValue trim
	 */
	protected $Name;
	
	public function getModelCaption($view_tag = null)
	{
		return $this->Name;
	}
	
	/**
	 * Gets a default for a listing selector if none was specified
	 * 
	 * @return string
	 */
	public static function GetListingQuery($selector = null)
	{
		$selector = $selector ?: static::GetListingEntity();
		
		$q = (is_array($selector) ? qImplodeEntity($selector) : $selector)." "
				. "WHERE 1 "
				. "??Id?<AND[Id=?]"
			. " GROUP BY Id "
			. " ORDER BY "
				. "??OBY_Id?<,[Id ?@]"
			. " ??LIMIT[LIMIT ?,?]";
        
		return $q;
	}
}
