<?php

final class Q_Async_Task
{
	public $fiber;
	public $handle;
	public $handle_type;
	public $op_type;
	public $args;

	public $success = null;
	public $timeout = null;
	public $result = null;
	public $error = null;
	
	public $max_wait_microsec;
	public $start_time;

	public function __construct(\Fiber $fiber, mixed $handle, int $handle_type, int $op_type, int $max_wait_microsec, array $args = [])
	{
		$this->fiber = $fiber;
		$this->handle = $handle;
		$this->handle_type = $handle_type;
		$this->op_type = $op_type;
		$this->max_wait_microsec = $max_wait_microsec;
		$this->start_time = (int)(microtime(true) * 1000000);
		$this->args = $args;
	}
}

