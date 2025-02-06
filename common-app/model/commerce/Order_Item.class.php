<?php

namespace Omi\Comm;

/**
 * @author Alex
 *
 * @storage.table Orders_Items
 *
 * @model.captionProperties Order.Reference,Item.Name
 * 
 * @class.name Order_Item
 */
abstract class Order_Item_mods_model_ extends \QModel
{
	/**
	 * @var int
	 */
	protected $Id;
	/**
	 * @var int
	 */
	protected $Index;
	/**
	 * @var string
	 */
	protected $Caption;
	/**
	 * @var Order_Item
	 */
	protected $Parent;
	/**
	 * @var int
	 */
	protected $Parent_Index;
	/**
	 * @var Order
	 */
	protected $Order;
	/**
	 * @var Offer
	 */
	protected $Offer;
	/**
	 * @var float
	 */
	protected $Quantity;
	/**
	 * The price for one unit
	 * 
	 * @var float
	 */
	protected $Unit_Price;
	/**
	 * Total price including VAT
	 * 
	 * @var float
	 */
	protected $Total_Price;
	/**
	 * Total price without VAT
	 * 
	 * @var float
	 */
	protected $Total_No_VAT;
	/**
	 * The percent of VAT
	 * 
	 * @var float
	 */
	protected $VAT_Percent;
	/**
	 * Value of VAT
	 * 
	 * @var float
	 */
	protected $Total_VAT;
	/**
	 * @storage.type TEXT
	 * 
	 * @var string
	 */
	protected $Notes;
	
	/**
	 * 
	 * @var Order_Item_Config
	 */
	protected $Config;
}

