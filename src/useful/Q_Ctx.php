<?php

class Q_Ctx
{
	/**
	 * @var Q_Ctx[]
	 */
	protected static $Current;
	/**
	 * @var Q_Ctx[]
	 */
	protected static $All = [];
	
	/**
	 * @var int
	 */
	protected static $auto_tag_increment = 1;
	
	protected static $has_threads_support = null;
	
	/**
	 * @var Q_Ctx
	 */
	protected $tag;
	/**
	 * @var Q_Ctx
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
	/**
	 * For theads or fibers, does not support both at the same time
	 * @var int
	 */
	protected $stack_id;
	/**
	 * @var boolean
	 */
	protected $debug;
	/**
	 * @var array
	 */
	protected $debug_out = []; 

	public static function open(string $tag = null, bool $stacked = true, bool $debug = false)
	{
		if (($tag !== null) && is_numeric($tag))
			# explicit $tag must be non-numeric
			return null;
		if (empty($tag))
			$tag = static::$auto_tag_increment++;
		if (isset(static::$All[$tag]))
			return false;
		
		$c = new \Q_Ctx();
		$c->tag = $tag;
		$c->stacked = $stacked;
		$c->stack_id = $stack_id = static::get_calling_stack_id();
		$c->debug = $debug;
		
		static::$All[$tag] = $c;
		
		if ($stacked)
		{
			# $c->parent = static::$Current ?? null;
			# static::$Current = $c;
			$c->parent = static::current();
			static::$Current[$stack_id] = $c;
			# var_dump("OPEN", $c);
		}
				
		return $c;
	}
	
	public static function close(\Q_Ctx $context)
	{
		# var_dump("CLOSE @".static::get_calling_stack_id(), $context);
		
		static::cleanup($context);
		unset(static::$All[$context->tag]);

		$stack_id = static::get_calling_stack_id();
		if ($stack_id !== $context->stack_id)
			throw new \Exception("Must close on the same stack id.");
		
		# var_dump("CLOSE #2".'($context === static::$Current[$stack_id])', ($context === static::$Current[$stack_id]));
		if ($context === static::$Current[$stack_id])
		{
			static::$Current[$stack_id] = null; # reset on this stack
			if ($context->parent)
				# restore parent
				static::$Current[$context->parent->stack_id] = $context->parent;
		}
		
		# var_dump("CLOSE :: after :: current()", static::current());
		
		return static::current();
	}
	
	public function get_parent()
	{
		return $this->parent;
	}
	
	public static function cleanup(\Q_Ctx $context, array $only_indexes = null)
	{
		if ($only_indexes)
		{
			foreach ($only_indexes as $indx)
				unset($context->data[$indx]);
		}
		else
			unset($context->data);
	}
	
	public static function get_context_by_tag(string $tag)
	{
		return static::$All[$tag] ?? null;
	}
	
	public static function get($key, string $context_tag = null)
	{
		$context = (isset($context_tag) ? (static::$All[$context_tag] ?? null) : static::current());
		return $context ? $context->data[$key] : null;
	}
	
	public static function set($key, mixed $value, string $context_tag = null)
	{
		$context = (isset($context_tag) ? (static::$All[$context_tag] ?? null) : static::current());
		return $context ? ($context->data[$key] = $value) : null;
	}
	
	public function set_key($key, mixed $value)
	{
		return ($this->data[$key] = $value);
	}
	
	public function get_key($key = null)
	{
		return ($key === null) ? $this->data : ($this->data[$key] ?? null);
	}
	
	public static function debug(bool $set_value = null, string $context_tag = null)
	{
		$context = (isset($context_tag) ? (static::$All[$context_tag] ?? null) : static::current());
		if ($set_value === null)
		{
			while ($context) {
				if ($context->debug ?? null)
					return $context->debug;
				$context = $context->parent ?? null;
			}
			return null;
		}
		else
		{
			return $context ? ($context->debug = $set_value) : null;
		}
	}
	
	public static function current()
	{
		return static::$Current[static::get_calling_stack_id()] ?? (static::$Current[0] ?? null);
	}
	
	public function get_tag()
	{
		return $this->tag ?? null;
	}
	
	public static function exists()
	{
		return (static::current() ? true : false);
	}
	
	public static function closest(string $tag, bool $starts_with = false)
	{
		$ctx = static::current();
		while ($ctx)
		{
			if (($starts_with ? substr($ctx->tag, 0, strlen($tag)) : $ctx->tag) === $tag)
				return $ctx;
			$ctx = $ctx->parent;
		}
		return null;
	}
	
	public static function debug_out(string $output = null, string $context_tag = null)
	{
		if ($output === '')
			return $output;
		
		$context = (isset($context_tag) ? (static::$All[$context_tag] ?? null) : static::current());
		
		$dbg_ctxs = [];
		
		while ($context) {
			if ($context->debug ?? null)
			{
				# break;
				$dbg_ctxs[] = $context;
			}
			$context = $context->parent ?? null;
		}
		
		if ($output === null)
			return $dbg_ctxs ? $dbg_ctxs[0]->debug_out : null;
		else
		{
			foreach ($dbg_ctxs as $ctx)
				$ctx->debug_out[] = $output;
		}
	}
	
	protected static function get_calling_stack_id()
	{
		if (\Fiber::getCurrent())
			return spl_object_id(\Fiber::getCurrent());
		else if ((static::$has_threads_support ?? (static::$has_threads_support = class_exists('Thread'))) && \Thread::getCurrentThread())
			return spl_object_id(\Thread::getCurrentThread());
		else
			return 0;
	}
}
