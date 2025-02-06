<?php

namespace Omi\Comm;

/**
 * @author Alex
 *
 * @storage.table Bank_Accounts
 *
 * @model.captionProperties Bank_Name,IBAN
 * 
 * @class.name Bank_Account
 */
abstract class Bank_Account_mods_model_ extends \QModel
{
	/**
	 * @var int
	 */
	protected $Id;
	/**
	 * @var string
	 */
	protected $Bank_Name;
	/**
	 * @var string
	 */
	protected $IBAN;
	/**
	 * @storage.type enum('RON','EUR','USD')
	 * 
	 * @var string
	 */
	protected $Currency;
}

