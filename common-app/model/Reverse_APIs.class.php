<?php

namespace Omi;

/**
 * @author Alex
 * @storage.table Reverse_APIs
 * 
 * @class.name Reverse_APIs
 */
abstract class Reverse_APIs_mods_model_ extends \QModel 
{
	/**
	 * @var int
	 */
	protected $Id;
	/**
	 * @var Reverse_APIs_Item[]
	 */
	protected $Items;
}
