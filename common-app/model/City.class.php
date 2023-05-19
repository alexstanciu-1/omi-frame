<?php

namespace Omi;

/**
 * @storage.table Cities
 * 
 * @model.captionProperties Name,County.Name,Country.Name
 * 
 * @class.name City
 */
abstract class City_mods_model_ extends \QModel
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
	 * @storage.optionsPool Counties
	 * 
	 * @var County
	 */
	protected $County;
	/**
	 * @storage.optionsPool Countries
	 * 
	 * @var Country
	 */
	protected $Country;
	/**
	 * @storage.oneToMany City
	 * 
	 * @var Address[]
	 */
	protected $Addresses;
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
		if (isset($this->_Overwrite_Name))
		{
			return $this->_Overwrite_Name;
		}
		else if ($view_tag === 'Search_List_Cities')
		{
			# throw new \Exception('eeeex');
			return $this->Name;
		}
		# else if (\Omi\User)
		else
			return $this->Name.($this->Id ? " [{$this->Id}]" : "").
				(isset($this->County->Name) ? ", ".$this->County->Name : "").
					(isset($this->County->Id) ? " [{$this->County->Id}]" : "").
					(isset($this->Country->Name) ? ", ".$this->Country->Name : "").
						(isset($this->Country->Id) ? " [{$this->Country->Id}]" : "");
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
				. static::GetListingQueryFilters()
				. " ??Name?<AND[Name=?]"
				. " ??Code?<AND[Code=?]"
				. " ??Country?<AND[Country.Id=?]"
				. " ??County?<AND[County.Id=?]"
				. " ??WHR_Search?<AND[(Name LIKE (?) OR Code LIKE(?))]"
			. " ORDER BY "
					. "??OBY_Name?<,[Name ?@]"
			. " ??LIMIT[LIMIT ?,?]";
		return $q;
	}
}
