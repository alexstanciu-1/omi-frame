<?php

namespace Omi\Comm;

/**
 * @author Alex
 *
 * @storage.table Companies
 * 
 * @model.captionProperties Name,Address.City.Name
 * 
 * @class.name Company
 */
abstract class Company_mods_model_ extends \QModel
{
	/**
	 * @var int
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
	 * Registration number
	 * 
	 * @var string
	 */
	protected $Reg_No;
	/**
	 * @var string
	 */
	protected $VAT_No;
	/**
	 * @var boolean
	 */
	protected $VAT_Payer;
	/**
	 * @storage.filter ["@CALL" => "Omi\\Util\\AddressSearch::Search_Address_For_DropDown"]
	 * @storage.optionsPool Addresses
	 * @display.controls on
	 * 
	 * @var \Omi\Address
	 */
	protected $Address;	
	/**
	 * @var \Omi\Comm\Bank_Account[]
	 */
	protected $Bank_Accounts;
	/**
	 * @var string[]
	 */
	protected $Emails_List;
	/**
	 * @var string[]
	 */
	protected $Phones_List;
	/**
	 * @var \Omi\Person[]
	 */
	protected $Contacts;
	/**
	 * @storage.optionsPool Companies
	 * 
	 * @var Company[]
	 */
	protected $Accessible_By;
	/**
	 * @storage.optionsPool Companies
	 * 
	 * @storage.collection Companies_Accessible_By,Accessible_By,Companies
	 * 
	 * @var Company[]
	 */
	protected $Has_Access_To;
	/**
	 * @storage.oneToMany Owner
	 * 
	 * @var \Omi\User[]
	 */
	protected $Users;
	/**
	 * @storage.oneToMany Owner
	 * 
	 * @var \Omi\Mail_Sender[]
	 */
	protected $Mail_Senders;
	
	/**
	 * @var \Omi\TFH\Contact_Information[]
	 */
	protected $Contact_Emails_List;
	/**
	 * @var \Omi\TFH\Contact_Information[]
	 */
	protected $Contact_Phones_List;
	
	/**
	 * @var boolean
	 */
	protected $Terms_Accepted;
	/**
	 * @storage.type VARCHAR(32)
	 * 
	 * @var string
	 */
	protected $Terms_Accepted_IP;
	/**
	 * @var datetime
	 */
	protected $Terms_Accepted_Date;
	/**
	 * @storage.oneToMany Owner
	 * 
	 * @var \Omi\Mail_Sender
	 */
	protected $Mail_Sender;
	/**
	 * @var file
	 */
	protected $Logo;

	/**
	 * Model caption
	 * 
	 * @param string $tag
	 * 
	 * @return type
	 */
	public function getModelCaption($tag = null)
	{
		return $this->Name.(isset($this->Address->City->Name) ? ", ".$this->Address->City->Name : "");
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
				. "??Name?<AND[Name LIKE (?)]"
				. "??QINSEARCH_Name?<AND[Name LIKE (?)]"
				. "??WHR_Search?<AND[Name LIKE (?)]"
				. "??WHR_Is_Property_Owner?<AND[(Is_Property_Owner)]"
				. "??WHR_Is_Channel_Owner?<AND[(Is_Channel_Owner)]"
				. " ??WHR_Owner_Id?<AND[ Id=? ] "
			. " GROUP BY Id "
			. " ORDER BY "
				. "??OBY_Id?<,[Id ?@]"
				. "??OBY_Name?<,[Name ?@]"
			. " ??LIMIT[LIMIT ?,?]";
        
		return $q;
	}
}

