<?php

namespace Omi\Comm;

/**
 * @author Alex
 *
 * @storage.table Price_Profile
 *
 * @model.captionProperties Name
 * 
 * @class.name Price_Profile
 */
abstract class Price_Profile_mods_model_ extends \QModel
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
	 * @storage.oneToMany Price_Profile
	 * @storage.collection_type checkbox
	 * @storage.checkbox_coll_custq \Omi\Comm\Price_Profile::Get_Collection_Items($_qengine_args['mainData'], $_qengine_args, $this)
	 * 
	 * @var Price_Profile_Item[]
	 */
	protected $Items;
	/**
	 * @storage.type enum('RON','EUR','USD')
	 * 
	 * @var string
	 */
	protected $Currency;
	
	/**
	 * @api.enable
	 */
	public static function Get_Collection_Items(Price_Profile $price_profile = null, $main_data = null, bool $with_rate_plan = true)
	{
		
	}
	
	public function getModelCaption($view_tag = null)
	{
		return $this->Name ?: parent::getModelCaption($view_tag);
	}
}
