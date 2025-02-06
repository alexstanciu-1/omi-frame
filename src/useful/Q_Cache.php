<?php

class Q_Cache
{
	/**
	 * @var Q_Cache
	 */
	protected static $Current;
	/**
	 * @var Q_Cache[]
	 */
	protected static $All = [];
	
	/**
	 * @var int
	 */
	protected static $auto_tag_increment = 1;
	
	/**
	 * @var string
	 */
	protected $tag;
	/**
	 * @var Q_Cache
	 */
	protected $parent;
	/**
	 * @var array
	 */
	protected $data;
	/**
	 * @var boolean
	 */
	protected $stacked;

	public static function open(string $tag = null, bool $stacked = true)
	{
		if (($tag !== null) && is_numeric($tag))
			# explicit $tag must be non-numeric
			return null;
		
		if (empty($tag))
			$tag = static::$auto_tag_increment++;
		
		$c = new \Q_Cache();
		$c->tag = $tag;
		$c->stacked = $stacked;
		
		if (isset(static::$All[$tag]))
			return false;
		static::$All[$tag] = $c;
		
		if ($stacked)
		{
			$c->parent = static::$Current ?? null;
			static::$Current = $c;
		}
		
		return $c;
	}
	
	public static function close(\Q_Cache $cache)
	{
		static::cleanup($cache);
		unset(static::$All[$cache->tag]);
		
		if ($cache === static::$Current)
			static::$Current = ($cache->parent ?? null);
		
		return static::$Current ?? null;
	}
	
	public static function cleanup(\Q_Cache $cache, array $only_indexes = null)
	{
		if ($only_indexes)
		{
			foreach ($only_indexes as $indx)
				unset($cache->data[$indx]);
		}
		else
			unset($cache->data);
	}
	
	public static function get_cache_by_tag(string $tag)
	{
		return static::$All[$tag] ?? null;
	}
	
	public static function get($key, string $cache_tag = null)
	{
		$cache = (isset($cache_tag) ? (static::$All[$cache_tag] ?? null) : (static::$Current ?? null));
		return $cache ? $cache->data[$key] : null;
	}
	
	public static function set($key, mixed $value, string $cache_tag = null)
	{
		if (($cache = (isset($cache_tag) ? (static::$All[$cache_tag] ?? null) : (static::$Current ?? null))))
			$cache->data[$key] = $value;
	}
}
