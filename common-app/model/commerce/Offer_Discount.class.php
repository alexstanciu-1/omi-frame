<?php

namespace Omi\Comm;

/**
 * @author Alex
 *
 * @storage.table Offer_Discount
 * 
 * @class.name Offer_Discount
 */
abstract class Offer_Discount_mods_model_ extends \QModel
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
	 * @storage.type enum('percent','fixed')
	 * 
	 * @var string
	 */
	protected $Discount_Type;
	/**
	 * @var float
	 */
	protected $Percent;
	/**
	 * @var float
	 */
	protected $Fixed;
}


