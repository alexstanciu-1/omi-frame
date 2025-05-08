<?php

namespace Omi\Comm;

/**
 * @author Alex
 *
 * @storage.table Orders
 *
 * @model.captionProperties Reference
 * 
 * @class.name Order
 */
abstract class Order_mods_model_ extends \QModel
{
	/**
	 * @var int
	 */
	protected $Id;
	/**
	 * @var string
	 */
	protected $Reference;
	/**
	 * @storage.index
	 * 
	 * @var datetime
	 */
	protected $Date;
	/**
	 * @storage.index
	 * 
	 * @var datetime
	 */
	protected $Last_Modified_Date;
	/**
	 * @var \Omi\Person
	 */
	protected $Buyer;
	/**
	 * @var \Omi\Comm\Company
	 */
	protected $Buyer_Company;
	
	/**
	 * @storage.oneToMany Order
	 * 
	 * @var Order_Item[]
	 */
	protected $Items;
	/**
	 * Do an enum ?
	 * @storage.type enum('Proposal','Submitted','Confirmed','Cancelled','Error')
	 * 
	 * @var string
	 */
	protected $Status;
	/**
	 * @var datetime 
	 */
	protected $Status_Change_Date;
	# LastStatusChanged ?
	
	/**
	 * Total price including VAT
	 * 
	 * @var float
	 */
	protected $Total_Price;
	/**
	 * @var string
	 */
	protected $Currency_Code;
	/**
	 * @storage.type TEXT
	 * 
	 * @var string
	 */
	protected $Notes;
	/**
	 * @var \Omi\User
	 */
	protected $Created_By;

	/**
	 * @var \Omi\Request_Log[]
	 */
	protected $Reverse_Api_Log;
}

