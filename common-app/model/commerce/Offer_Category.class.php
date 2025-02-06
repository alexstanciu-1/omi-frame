<?php

namespace Omi\Comm;

/**
 * @author Alex
 *
 * @storage.table Offer_Category
 *
 * @model.captionProperties Name
 * 
 * @class.name Offer_Category
 */
abstract class Offer_Category_mods_model_ extends \QModel
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
	
	public function getModelCaption($view_tag = null)
	{
		return $this->Name;
	}
}
