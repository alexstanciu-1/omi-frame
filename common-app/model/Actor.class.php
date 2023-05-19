<?php

namespace Omi;

/**
 * @author Alex
 *
 * @model.captionProperties Id,Name
 * @class.name Actor
 */
abstract class Actor_mods_model_ extends \QModel
{
	/**
	 * @var string
	 * 
	 * @validation mandatory
	 * @fixValue trim
	 */
	protected $Name;
	/**
	 * @var string
	 * @validation mandatory && email
	 * @fixValue trim
	 */
	protected $Email;
	/**
	 * @var string
	 * @fixValue trim
	 */
	protected $Phone;
	/**
	 * @var \Omi\Address
	 * 
	 * @storage.dependency subpart
	 */
	protected $Address;
	/**
	 * @storage.oneToMany Actor
	 * 
	 * @var \Omi\Address[]
	 */
	protected $Addresses;

	public function getModelCaption($view_tag = null)
	{
		return $this->Name;
	}
}