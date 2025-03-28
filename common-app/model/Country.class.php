<?php

namespace Omi;

/**
 * @storage.table Countries
 * 
 * @class.name Country
 * 
 * @model.captionProperties Name
 * 
 */
abstract class Country_mods_model_ extends \QModel
{
	/**
	 * @var string
	 */
	protected $Id;
	/**
	 * @var string
	 */
	protected $Name;
	/**
	 * @var string
	 */
	protected $Code;
	/**
	 * @var string
	 */
	protected $Place_Id;
	/**
	 * @var datetime
	 */
	protected $Place_Mtime;

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
				. " ??Id?<AND[Id=?] "
				. static::GetListingQueryFilters()
				. " ??Name?<AND[Name=?]"
				. " ??Code?<AND[Code=?]"
				. " ??WHR_Search?<AND[(Name LIKE (?) OR Code LIKE(?))]"
			. " ORDER BY "
					. "??OBY_Name?<,[Name ?@]"
			. " ??LIMIT[LIMIT ?,?]";
		return $q;
	}
}
