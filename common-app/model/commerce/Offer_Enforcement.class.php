<?php

namespace Omi\Comm;

/**
 * @storage.table Offer_Enforcements
 * 
 * @class.name Offer_Enforcement
 */
abstract class Offer_Enforcement_mods_model_ extends \QModel
{
	/**
	 * @var int
	 */
	protected $Id;
	/**
	 * @var Offer
	 */
	protected $Offer;
	
	/**
	 * @var int
	 */
	protected $Order_By;
	/**
	 * @var boolean
	 */
	protected $Is_Default;
	/**
	 * @var Offer_Enforcement_Item[]
	 */
	protected $Offer_Enforcement_Items;
}

