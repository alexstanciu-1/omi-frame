<?php

class Q_Async_Thread
{
	/**
	 * @var \Fiber
	 */
	protected $_fiber;
	/**
	 * @var boolean
	 */
	protected $_terminate = false;
	/**
	 * @var callable
	 */
	protected $_run_cbk;
	
	/**
	 * @TODO - expose fiber methods
	 */
	
	/**
	 * Starts the thread
	 * 
	 * @return array
	 * @throws \Exception
	 */
	public final function start()
	{
		list ($this->_fiber) = \Q_Async::Run_no_start([$this, 'run_internal']);
		if ( !($this->_fiber instanceof \Fiber) )
			throw new \Exception('Fiber init failed.');
		
		$fiber_start_ret = $this->_fiber->start();
		return [$fiber_start_ret];
	}
	
	protected function set_terminate(bool $termintate = true)
	{
		$this->_terminate = $termintate;
		if ($termintate && $this->_fiber && (!$this->_fiber->isTerminated()))
		{
			if ($this->_fiber === \Fiber::getCurrent())
				throw new \Exception('Abort requested.');
			else
				$this->_fiber->throw(new \Exception('Abort requested.'));
		}
	}
	
	protected final function run_internal()
	{
		try
		{
			$this->exec();
		}
		catch (\Exception $ex)
		{
			throw $ex;
		}
		finally
		{
			# we could restart on error , based on a flag ... or more
		}
	}
	
	protected function exec()
	{
		if (is_callable($callback = $this->_run_cbk))
			$callback();
	}
	
	public static function Run(callable $callback = null)
	{
		$thread = new static;
		$thread->run_cbk = $callback;
		$start_ret = $thread->start();
		return [$thread, $start_ret];
	}
}

