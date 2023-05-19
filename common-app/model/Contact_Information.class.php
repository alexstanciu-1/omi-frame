<?php 

namespace Omi\TFH;

/**
 *
 * @storage.table Contact_Information
 *
 * @model.captionProperties Name
 * 
 * @class.name Contact_Information
 */
abstract class Contact_Information_mods_model_ extends \QModel 
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
	protected $Department;
	/**
	 * @var boolean
	 */
	protected $IsDefault;
}
	