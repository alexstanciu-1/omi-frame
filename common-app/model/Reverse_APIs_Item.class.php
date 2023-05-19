<?php

namespace Omi;

/**
 * @author Alex
 * @storage.table Reverse_APIs
 * 
 * @class.name Reverse_APIs_Item
 */
abstract class Reverse_APIs_Item_mods_model_ extends \QModel 
{
	/**
	 * @var int
	 */
	protected $Id;
	/**
	 * @var string
	 */
	protected $On_Action;
	/**
	 * @var string
	 */
	protected $URL;
	/**
	 * @storage.type TEXT
	 * 
	 * @var string
	 */
	protected $Arguments_Selector;
}
