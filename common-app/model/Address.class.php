<?php

namespace Omi;

/**
 * @author Alex
 *
 * @storage.table Addresses
 *
 * @model.captionProperties Street,StreetNumber,Building,BuildingPart,Organization,Caption,Premise,PostCode,Details,City.Name,County.Name,Country.Name,Latitude,Longitude
 * 
 * @class.name Address
 */
abstract class Address_mods_model_ extends \QModel
{
	/**
	 * @var int
	 */
	protected $Id;
	/**
	 * @var City
	 * 
	 * @display.controls on
	 * @storage.view_to_load PopupCities
	 * 
	 * @storage.optionsPool Cities
	 * 
	 * @validation mandatory
	 */
	protected $City;
	/**
	 * @var County
	 * 
	 * @display.controls on
	 * @storage.view_to_load PopupCounties
	 * @storage.optionsPool Counties
	 * @validation mandatory
	 */
	protected $County;
	/**
	 * @var Country
	 * @display.controls on
	 * @storage.view_to_load PopupCountries
	 * @validation mandatory
	 * 
	 * @storage.optionsPool Countries
	 */
	protected $Country;
	/**
	 * @var string
	 * 
	 * @fixValue trim
	 * 
	 */
	protected $PostCode;
	/**
	 * @var string
	 * @fixValue trim
	 */
	protected $Street;
	/**
	 * @var string
	 * @fixValue trim
	 */
	protected $StreetNumber;
	/**
	 * @var string
	 * 
	 * @fixValue trim
	 */
	protected $Details;
	/**
	 * @var string
	 * @fixValue trim
	 */
	protected $Building;
	/**
	 * @var string
	 * @fixValue trim
	 */
	protected $BuildingPart;
	/**
	 * @var string
	 * @fixValue trim
	 */
	protected $Organization;
	/**
	 * @var string
	 * @fixValue trim
	 */
	protected $Premise;
	/**
	 * @var string
	 * @fixValue trim
	 */
	protected $Caption;
	/**
	 * @var float
	 * @fixValue trim
	 */
	protected $Longitude;
	/**
	 * @var float
	 * @fixValue trim
	 */
	protected $Latitude;
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
		return $this->Caption ?: $this->getAddressCaption();
	}

	protected function getAddressCaption()
	{
		//8-32 Hawks Rd, Kingston upon Thames, Greater London KT1, UK
		$data = [];
		if ($this->StreetNumber || $this->Street)
			$data[] = $this->Street.($this->StreetNumber ? " ".$this->StreetNumber : "");
		if ($this->BuildingPart || $this->Building)
			$data[] = ($this->BuildingPart ? $this->BuildingPart." " : "").$this->Building;
		if ($this->Organization)
			$data[] = $this->Organization;
		if ($this->Premise)
			$data[] = $this->Premise;
		if ($this->Details)
			$data[] = $this->Details;
		if ($this->City && $this->City->Name)
			$data[] = $this->City->Name;
		# if ($this->County && $this->County->Name)
		#	$data[] = $this->County->Name;
		if ($this->PostCode)
			$data[] = $this->PostCode;
		if ($this->Country && $this->Country->Name)
			$data[] = $this->Country->Name;
		# if ($this->Latitude)
		#	$data[] = 'Lat. ' . $this->Latitude;
		# if ($this->Longitude)
		#	$data[] = 'Long. ' . $this->Longitude;
		
		return implode(", ", $data);
	}
	
	/**
	 * @param string $selector
	 * @param int $transform_state
	 * @param type $_bag
	 * @return type
	 */
	public function beforeSave($selector = null, $transform_state = null, &$_bag = null, $is_starting_point = true, $appProp = null)
	{
		# $this->updateCaption();
		return parent::beforeSave($selector, $transform_state, $_bag, $is_starting_point, $appProp);
	}
	
	protected function updateCaption()
	{
		if (!$this->getId())
			return;
		$this->populate("StreetNumber, PostCode, Street, BuildingPart, Building, Organization, Premise, Details, City.Name, County.Name, Country.Name");
		$this->setCaption($this->getAddressCaption());

	}

	/**
	 * Gets a default for a listing selector if none was specified
	 * 
	 * @return string
	 */
	public static function GetModelEntity($view_tag = null)
	{
		return "PostCode, "
			. "Details, "
			. "Building, "
			. "BuildingPart, "
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
			. "Latitude";
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
		return static::$ListingEntity[$class] = qParseEntity("Country.{Code, Name}, "
			. "County.{Code, Name}, "
			. "City.{Code, Name}, "
			. "Building, "
			. "BuildingPart, "
			. "Caption,"
			. "Organization, "
			. "Premise, "
			. "Street, "				
			. "StreetNumber, "
			. "Details, "
			. "PostCode, "
			. "Longitude, "
			. "Latitude");
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
				. " ??PostCode?<AND[PostCode=?]"
				. " ??Street?<AND[Street LIKE (?)]"
				. " ??City?<AND[City.Id=?]"
				. " ??CityCode?<AND[City.Code=?]"
				. " ??CityName?<AND[City.Name LIKE (?)]"
				. " ??County?<AND[County.Id=?]"
				. " ??CountyCode?<AND[County.Code=?]"
				. " ??CountyName?<AND[County.Name LIKE (?)]"
				. " ??Country?<AND[Country.Id=?]"
				. " ??CountryCode?<AND[Country.Code=?]"
				. " ??CountryName?<AND[Country.Name LIKE (?)]"
				. " ??POSTCODE_LOOKUP?<AND[(Group.Id=? OR PostCode=?)]"
				. " ??WHR_Search?<AND[(City.Name LIKE (?) OR County.Name LIKE (?) OR Country.Name LIKE (?) OR PostCode LIKE (?) "
					. "OR Street LIKE (?) OR StreetNumber LIKE (?) OR Organization LIKE (?) OR Premise LIKE (?) OR Building LIKE (?) OR BuildingPart LIKE (?))]"
			. " ORDER BY "
					. "??OBY_CountryName?<,[Country.Name ?@]"
					. "??OBY_CountryCode?<,[Country.Code ?@]"
					. "??OBY_CountyName?<,[County.Name ?@]"
					. "??OBY_CountyCode?<,[County.Code ?@]"	
					. "??OBY_CityName?<,[City.Name ?@]"
					. "??OBY_CityCode?<,[City.Code ?@]"	
			. " ??LIMIT[LIMIT ?,?]";
		return $q;
	}
	/**
	 * @api.enable
	 * 
	 * @param \Omi\Address $address
	 */
	public static function LoadCaptionData(Address $address)
	{
		if ($address->City && !$address->City->Name)
			$address->City->query("Name");

		if ($address->County && !$address->County->Name)
			$address->County->query("Name");

		if ($address->Country && !$address->Country->Code)
			$address->Country->query("Code");
	}
	/**
	 * @api.enable
	 * 
	 * @param \Omi\Address $address
	 */
	public static function AddAddressToGroup(Address $address)
	{
		if (!$address->PostCode)
			return;

		$groups = \QApi::Query("AddressesGroups", null, ["PostCode" => $address->PostCode]);
		$group = $groups ? $groups[0]->toArray("Id") : [];

		if (!$group["_id"])
			$group["PostCode"] = $address->PostCode;

		$group["Addresses"]  = [
			$address->toArray("Id")
		];

		\QApi::Merge("AddressesGroups", $group);
	}
	/**
	 * @api.enable
	 * 
	 * @param \Omi\Address $cachedAddress
	 * @param \Omi\Address $address
	 */
	public static function MergeAddress(Address $cachedAddress = null, Address $address = null)
	{
		if (!$cachedAddress)
			return;

		/*
		$addr = $cachedAddress->getClone("City.{Id, Name}, Country.{Id, Name}, County.{Id, Name}, "
			. "PostCode, Street, StreetNumber, Details, Building, Organization, Premise, Caption");
		if ($address && $address->getId())
			$addr->setId($address->getId());
		return [$addr->getModelCaption(), $addr->getId(), q_get_class($addr), $addr->toJSON(), $addr, \QApp::Data()];
		*/

		$addrData = $cachedAddress->toArray("City.Id, Country.Id, County.Id, PostCode, Street, StreetNumber, Details, Building, Organization, Premise, Caption");
		unset($addrData["_id"]);

		if ($address && $address->getId())
			$addrData["Id"] = $address->getId();

		$appData = \QApi::Merge("Addresses", $addrData);
		$addr = $appData->Addresses[0];
		return !$addr ? [null, null, null, null, null, $appData] : [$addr->getModelCaption(), $addr->getId(), q_get_class($addr), $addr->toJSON(), $addr, $appData];
	}
}