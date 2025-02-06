<?php

namespace Omi\Comm;

/**
 * @author Alex
 *
 * @storage.table Price_Profile_Item
 * 
 * @class.name Price_Profile_Item
 */
abstract class Price_Profile_Item_mods_model_ extends \QModel
{
	/**
	 * @var int
	 */
	protected $Id;
	/**
	 * @storage.optionsPool Price_Profiles
	 * 
	 * @var Price_Profile
	 */
	protected $Price_Profile;
	/**
	 * @storage.optionsPool Offers
	 * 
	 * @var Offer
	 */
	protected $Offer;
	/**
	 * @var float
	 */
	protected $Price;
	/**
	 * @var boolean
	 */
	protected $Active;
}

