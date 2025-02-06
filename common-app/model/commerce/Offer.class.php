<?php

namespace Omi\Comm;

/**
 * @author Alex
 *
 * @storage.table Offers
 *
 * @model.captionProperties Name
 * 
 * @class.name Offer
 */
abstract class Offer_mods_model_ extends \QModel
{
	/**
	 * @var int
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
	 * @storage.optionsPool Offer_Categories
	 * 
	 * @var Offer_Category
	 */
	protected $Category;
	/**
	 * @storage.type enum('service','product','other')
	 * 
	 * @var string
	 */
	protected $Type;
	/**
	 * @var boolean
	 */
	protected $Is_Bundle;
	/**
	 * @var Offer[]
	 */
	protected $Bundle_Items;
	/**
	 * @var Content
	 */
	protected $Content;
	/**
	 * @storage.oneToMany Offer
	 * 
	 * @var Offer_Discount[]
	 */
	protected $Discounts;
	/**
	 * @storage.oneToMany Offer
	 * 
	 * @var Price_Profile_Item[]
	 */
	protected $Price_Profile_Items;
	
	public function getModelCaption($view_tag = null)
	{
		return $this->Name;
	}
}

