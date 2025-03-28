<?php

namespace Omi;

/**
 * Description of Text
 *
 * @storage.table Texts
 *
 * @model.captionProperties Id,Tag
 * @class.name Text
 */
abstract class Text_mods_model_ extends \QModel
{
	/**
	 * @storage.type VARCHAR(512)
	 * 
	 * @storage.index
	 * @fixValue trim
	 * 
	 * @var string
	 */
	protected $Key;
	/**
	 * @storage.type VARCHAR(8192)
	 * 
	 * @var string
	 */
	protected $Value;
	/**
	 * @storage.type LONGTEXT
	 * 
	 * @var string
	 */
	protected $Text;

	public function getModelCaption($view_tag = null)
	{
		return $this->SessionId;
	}
	
	public static function Get_Text(string $key, bool $cache = false)
	{
		$ret = \QQuery('Texts.{* WHERE Key=?}', [$key])->Texts;
		return ($ret !== null) ? $ret[0] : null;
	}
	
	public static function Get_Text_Value(string $key, bool $cache = false)
	{
		$obj = static::Get_Text($key, $cache);
		return $obj->Value ?? null;
	}
	
	public static function Get_Text_Long_Value(string $key, bool $cache = false)
	{
		$obj = static::Get_Text($key, $cache);
		return $obj->Text ?? null;
	}
	/*
	public static function Set_Text(string $key, bool $cache = false)
	{
		$ret = \QQuery('Texts.{* WHERE Key=?}', [$key])->Texts;
		return ($ret !== null) ? $ret[0] : null;
	}
	
	public static function Set_Text_Value(string $key, bool $cache = false)
	{
		$obj = static::Get_Text($key, $cache);
		return $obj->Value ?? null;
	}
	
	public static function Set_Text_Long_Value(string $key, bool $cache = false)
	{
		$obj = static::Get_Text($key, $cache);
		return $obj->Text ?? null;
	}
	*/
}