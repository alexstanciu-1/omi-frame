<?php

namespace Omi\Comm;

/**
 * @storage.table Offer_Enforcement_Items
 * 
 * @class.name Offer_Enforcement_Item
 */
abstract class Offer_Enforcement_Item_mods_model_ extends \QModel
{
	/**
	 * @var int
	 */
	protected $Id;
	
	/**
	 * @var string
	 */
	protected $Condition;
	/**
	 * @var string
	 */
	protected $Formula;
	/**
	 * @var Offer_Enforcement
	 */
	protected $Offer_Enforcement;
	/**
	 * @var Offer
	 */
	protected $Offer;
	/**
	 * @var int
	 */
	protected $Quantity;
	/**
	 * @var Offer_Discount
	 */
	protected $Discount;
	/**
	 * @storage.type enum('mandatory','incompatible','discount','set_price')
	 * 
	 * @var string
	 */
	protected $Action;
}

