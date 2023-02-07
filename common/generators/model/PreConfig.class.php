<?php

namespace Omi\Gens;

/**
 * @validation is_dir($this->GenerateWatchFolder.$value)
 * @class.name PreConfig
 */
abstract class PreConfig_gens_ extends \QModel
{
	
	/**
	 * @unique
	 * @key
	 * 
	 * @var string
	 */
	public $Tag;
	/**
	 * @var string
	 */
	public $Caption;
	/**
	 * @var string[]
	 */
	public $Classes;
	/**
	 * @var string[]
	 */
	public $Properties;
	/**
	 * @var string
	 */
	public $GenerateNamespace;
	/**
	 * @var string
	 */
	public $GeneratePrefix;
	/**
	 * @var string
	 */
	public $GenerateWatchFolder;
	/**
	 * @var string
	 */
	public $GenerateFolder;
	
	/**
	 * 
	 * @param string $filter
	 * @return string[]
	 */
	public function getOptionsTypes($filter = null)
	{
		
	}
	
	/**
	 * 
	 * @param string[] $types
	 * @param string $filter
	 * 
	 * @return string[]
	 */
	public function getOptionsProperties($types, $filter = null)
	{
		
	}
	
	/**
	 * @return string[]
	 */
	public function getOptionsGenerateWatchFolder()
	{
		
	}
	
	/*
	$admin->autogenConfigProperties = "Orders,News,Categories,Products,Users,Companies,Pages,PaymentMethods,ShippingMethods,Settings,NewsletterSubscribers,Newsletters,MainProducts,Slideshow";
	$admin->autogenConfigTypes = "*";
	$admin->autogenConfigPrefix = "CmsAdm";
	$admin->autogenConfigSufix = "";
	$admin->setFolder("code/~admin/");
	$admin->relPath = "~admin/";
	*/
}
