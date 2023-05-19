<?php

namespace Omi;

/**
 * @author Alex
 *
 * @storage.table Persons
 *
 * @model.captionProperties Name,Firstname
 *
 * @class.name Person
 */
abstract class Person_mods_model_ extends Actor
{
	const Male = 1;

	const Female = 2;
	
	/**
	 * @storage.type enum('Male','Female')
	 *
	 * @var string
	 */
	protected $Gender;
	/**
	 * @var int
	 * 
	 * @fixValue trim
	 */
	protected $Age;
	/**
	 * @var string
	 * @storage.mandatory {"ShipToSite" : true, "Users" : true}
	 * @storage.captions {"Customers" : "First Name", "Partners" : "First Name", "Holder" : "First Name", "Sites" : "First Name", "QuickQuoteSite": "First Name", "ShipToSite": "First Name", "Users": "First Name", "OrdersCustom" : "First Name", "QuickQuote" : "First Name", "Nuvia_Users" : "First Name"}
	 * @fixValue trim
	 */
	protected $Firstname;
	/**
	 * @var date
	 * 
	 * @fixValue trim
	 */
	protected $BirthDate;
	/**
	 * The person unique identifier - equivalent to romanian CNP or USA Social Ensurence Number
	 *
	 * @var string
	 * 
	 * @fixValue trim
	 */
	protected $UniqueIdentifier;
	/**
	 * @var string
	 * 
	 * @fixValue trim
	 */
	protected $IdentityCardSeries;
	/**
	 * @var string
	 * 
	 * @fixValue trim
	 */
	protected $IdentityCardNumber;
	/**
	 * @var string
	 * 
	 * @fixValue trim
	 */
	protected $PassportSeries;
	/**
	 * @var date
	 * 
	 * @fixValue trim
	 */
	protected $PassportExpireDate;
	/**
	 * @storage.type VARCHAR(32)
	 * @var string
	 */
	protected $Mobile;
	/**
	 * @storage.type VARCHAR(32)
	 * @var string
	 */
	protected $HomeNumber;
	/**
	 * @storage.type VARCHAR(32)
	 * @var string
	 */
	protected $FaxNumber;
	/**
	 * @storage.type VARCHAR(16)
	 * @storage.type enum('Mr.','Mrs.','Ms.')
	 * 
	 * @var string
	 */
	protected $Title;
	
	
	/**
	 * @var boolean
	 */
	protected $CreateLogin;
	/**
	 * @var string
	 * @storage.mandatory {"ShipToSite" : true, "Users" : true, "Contacts" : true}
	 * @storage.captions {"Customers" : "Last Name", "Partners" : "Last Name", "Holder" : "Last Name", "Sites" : "Last Name", "QuickQuoteSite": "Last Name", "ShipToSite": "Last Name", "Users": "Last Name", "OrdersCustom" : "Last Name", "QuickQuote" : "Last Name", "Nuvia_Users" : "Last Name"}
	 * @fixValue trim
	 */
	protected $Name;
	/**
	 * @var string
	 * @validation email
	 * @fixValue trim
	 */
	protected $Email;
	/**
	 * @storage.mandatory {"ShipToSite" : true}
	 * @var string
	 * @fixValue trim
	 */
	protected $Phone;
	/**
	 * @var string
	 */
	protected $Role;
	/**
	 * @var boolean
	 */
	protected $IsDefault;
	
	
	/**
	 * Returns person full name
	 * 
	 * @return string
	 */
	public function getFullName()
	{
		// return $this->Name." ".$this->Firstname;
		return $this->Firstname." ".$this->Name;
	}

	/**
	 * @return string
	 */
	public function getModelCaption($view_tag = null)
	{
		return $this->getFullName();
	}

	public function getAge()
	{
		if ($this->Age)
			return $this->Age;

		if (!$this->BirthDate)
			return null;

		$currentDate = new \DateTime();
		$birthDate = new \DateTime($this->BirthDate);
		$this->Age = intval($currentDate->diff($birthDate)->format("%y"));
	}
	/**
	 * Gets a default for a listing selector if none was specified
	 * 
	 * @return string
	 */
	public static function vp_fuse__GetModelEntity($view_tag = null)
	{
		return "Name, Title, "
		. "Email, "
		. "Phone, "
		. "Gender, "
		. "Age, "
		. "Firstname, "
		. "BirthDate, "
		. "UniqueIdentifier, "
		. "IdentityCardSeries, "
		. "IdentityCardNumber, "
		. "PassportSeries, "
		. "PassportExpireDate, "
		. "Company.Name, "
		. "Mobile, "
		. "HomeNumber, "
		. "FaxNumber, "
		. "Address.{"
			. "PostCode, "
			. "Details, "
			. "Building, "
			. "SubBuilding, "
			. "Caption,"
			. "Organization, "
			. "Premise, "
			. "Country.{"
				. "Code, "
				. "Name"
			. "}, "
			. "County.{"
				. "Code, "
				. "Name"
			. "}, "
			. "City.{"
				. "Code, "
				. "Name"
			. "}, "
			. "Street, "
			. "StreetNumber,"
			. "Longitude, "
			. "Latitude"
		. "}, "
		. "Addresses.{"
			. "PostCode, "
			. "Details, "
			. "Building, "
			. "SubBuilding, "
			. "Caption,"
			. "Organization, "
			. "Premise, "
			. "Country.{"
				. "Code, "
				. "Name"
			. "}, "
			. "County.{"
				. "Code, "
				. "Name"
			. "}, "
			. "City.{"
				. "Code, "
				. "Name"
			. "}, "
			. "Street, "
			. "StreetNumber,"
			. "Longitude, "
			. "Latitude"
		. "}";
	}
	/**
	 * Gets a default for a listing selector if none was specified
	 * 
	 * @return string
	 */
	public static function vp_fuse__GetListingEntity()
	{
		$class = get_called_class();
		$le = static::$ListingEntity[$class];
		if ($le !== null)
			return $le;
		return static::$ListingEntity[$class] = qParseEntity(
			"Name, "
			. "Email, "
			. "Phone, "
			. "Gender, "
			. "Age, "
			. "Firstname, "
			. "BirthDate, "
			. "UniqueIdentifier, "
			. "IdentityCardSeries, "
			. "IdentityCardNumber, "
			. "PassportSeries, "
			. "PassportExpireDate, "
			. "Mobile, "
			. "HomeNumber, "
			. "FaxNumber, "
			. "Address.{"
				. "PostCode, "
				. "Details, "
				. "Building, "
				. "SubBuilding, "
				. "Caption,"
				. "Organization, "
				. "Premise, "
				. "Country.{"
					. "Code, "
					. "Name"
				. "}, "
				. "County.{"
					. "Code, "
					. "Name"
				. "}, "
				. "City.{"
					. "Code, "
					. "Name"
				. "}, "
				. "Street, "
				. "StreetNumber,"
				. "Longitude, "
				. "Latitude"
			. "}");
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
				. "??NOT?<AND[Id<>?]"
				. static::GetListingQueryFilters()
				. "??Name?<AND[Name LIKE (?)]"
				. "??Firstname?<AND[Firstname LIKE (?)]"
				. "??Gender?<AND[Gender=?]"
				. "??Email?<AND[Email LIKE (?)]"
				. "??Phone?<AND[Phone LIKE (?)]"
				. "??BirthDate?<AND[BirthDate LIKE (?)]"
				. "??UniqueIdentifier?<AND[UniqueIdentifier=?]"
				. "??IdentityCardSeries?<AND[UniqueIdentifier=?]"
				. "??IdentityCardNumber?<AND[IdentityCardNumber=?]"
				. "??PassportSeries?<AND[PassportSeries=?]"
				. "??PassportExpireDate?<AND[PassportExpireDate=?]"
				. "??WHR_Search?<AND[(Name LIKE (?) OR Firstname LIKE (?))]"
				. "??WHR_Search?<AND[(Name LIKE (?) OR Firstname LIKE (?))]"
				. "??WHW_MainCompany_Id?<AND[(MainCompany.Id=? OR MainCompany.Id IS NULL)]"
				
				. "??QINSEARCH_Title?<AND[Title LIKE (?)]"
				. "??QINSEARCH_Name?<AND[Name LIKE (?)]"
				. "??QINSEARCH_Firstname?<AND[Firstname LIKE (?)]"
				. "??QINSEARCH_Email?<AND[Email LIKE (?)]"
				. "??QINSEARCH_Phone?<AND[Phone LIKE (?)]"
				. "??QINSEARCH_MainCompany?<AND[MainCompany.Name LIKE (?)]"
				
			. " ORDER BY "
				. "??OBY_Name?<,[Name ?@]"
				. "??OBY_Firstname?<,[Firstname ?@]"
				. "??OBY_Gender?<,[Gender ?@]"
				. "??OBY_BirthDate?<,[BirthDate ?@]"
				. "??OBY_UniqueIdentifier?<,[UniqueIdentifier ?@]"
				. "??OBY_IdentityCardSeries?<,[IdentityCardSeries ?@]"
				. "??OBY_IdentityCardNumber?<,[IdentityCardNumber ?@]"
				. "??OBY_PassportSeries?<,[PassportSeries ?@]"
				. "??OBY_PassportExpireDate?<,[PassportExpireDate ?@]"
			. " ??LIMIT[LIMIT ?,?]";
		return $q;
	}

	/**
	 * Gets a default for a listing selector if none was specified
	 * 
	 * @return string
	 */
	public static function GetModelEntity($view_tag = null)
	{
		return static::vp_fuse__GetModelEntity($view_tag) . ",Sf_Roles,MainCompany.{Name,Code}";
	}

	/**
	 * Gets a default for a listing selector if none was specified
	 * 
	 * @return string
	 */
	public static function GetListingEntity($view_tag = null)
	{
		$class = get_called_class();
		$le = static::$ListingEntity[$class];
		if ($le !== null)
			return $le;
		return static::$ListingEntity[$class] = qJoinSelectors(static::vp_fuse__GetListingEntity(), ["Sf_Roles" => [], 'MainCompany' => ['Name' => [], 'Code' => []]]);
	}
}