<?php

namespace Omi;

/**
 * @storage.table Counties
 * 
 * @model.captionProperties Name,Country.Name
 * 
 * @class.name County
 */
abstract class County_mods_model_ extends \QModel
{
	/**
	 * @var string
	 */
	protected $Id;
	/**
	 * @var string
	 */
	protected $Code;
	/**
	 * @var string
	 */
	protected $Name;
	/**
	 * @storage.optionsPool Countries
	 * 
	 * @var Country
	 */
	protected $Country;
	/**
	 * @var string
	 */
	protected $Place_Id;
	/**
	 * @var datetime
	 */
	protected $Place_Mtime;
	
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
				. static::GetListingQueryFilters()
				. " ??Name?<AND[Name=?]"
				. " ??QINSEARCH_Name?<AND[Name LIKE (?)]"
				. " ??Code?<AND[Code=?]"
				. " ??Country?<AND[Country.Id=?]"
				. " ??WHR_Search?<AND[(Name LIKE (?) OR Code LIKE(?))]"
			. " ORDER BY "
					. "??OBY_Name?<,[Name ?@]"
			. " ??LIMIT[LIMIT ?,?]";
		return $q;
	}

	public function getModelCaption($view_tag = null)
	{
        if (isset($this->_Overwrite_Name))
		{
			return $this->_Overwrite_Name;
		}
        else
            return $this->Name.(isset($this->Country->Name) ? ", ".$this->Country->Name : "");
	}
}
