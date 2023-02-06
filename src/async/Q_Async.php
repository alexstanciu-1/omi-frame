<?php

final class Q_Async
{
	const Handle_Socket = 1;
	const Handle_UDS_Socket = 2;
	const Handle_Mysqli_Socket = 3;
	const Handle_Stream_Socket = 4;
	const Handle_File_Stream = 5;
	const Handle_Process = 6;
	
	const OP_Socket_Recv_From = 101;
	const OP_Socket_Send_To = 102;
	
	/**
	 * @var \Fiber[]
	 */
	protected static $_Fibers;
	protected static $_Tasks = [
				0 => null,
				self::Handle_Socket => [],
				self::Handle_UDS_Socket => [],
				self::Handle_Mysqli_Socket => [],
				self::Handle_Stream_Socket => [],
				self::Handle_File_Stream => [],
				self::Handle_Process => [],
			];
	protected static $_Spin_Fiber;
	protected static $_Terminate_If_No_Tasks = false;
	protected static $_Microsleep = 0;
	protected static $_Sync_All = false;
	
	protected static $_register_shutdown_done = false;
	
	public static function Sync_All()
	{
		# all fibers & tasks need to be completed
		if (!static::$_Spin_Fiber)
			return;
		
		try
		{
			static::$_Sync_All = true;
			
			/*
			var_dump([
				'static::$_Spin_Fiber->isSuspended()' => static::$_Spin_Fiber->isSuspended(),
				'static::$_Spin_Fiber->isTerminated()' => static::$_Spin_Fiber->isTerminated(),
				# 'static::$_Spin_Fiber->isSuspended()' => static::$_Spin_Fiber->isSuspended(),
				]);
			*/
			while (static::Has_Tasks() && (!static::$_Spin_Fiber->isTerminated()))
			{
				static::$_Microsleep = 1;
				if (static::$_Spin_Fiber->isSuspended())
					static::$_Spin_Fiber->resume();
			}
			
			echo "Sync_All :: FINISHED\n";
		}
		finally
		{
			static::$_Sync_All = false;
		}
	}
	
	protected static function On_Shutdown()
	{
		echo "On_Shutdown\n";
		static::$_Microsleep = 1;
		static::$_Terminate_If_No_Tasks = true;
		static::Sync_All();
	}
	
	/**
	 * Starts a virtual thread of work (implemented via fibers atm)
	 * 
	 * @param callable $callback
	 * @param mixed $args
	 * @return array
	 */
	public static function Run_Sync(callable $callback, mixed ...$args)
	{
		list ($fiber, $start_ret) = static::Run($callback, ...$args);
		$rc = static::Sync([$fiber]);
		return [$rc, $fiber, $start_ret];
	}
	
	/**
	 * Starts a virtual thread of work (implemented via fibers atm)
	 * 
	 * @param callable $callback
	 * @param mixed $args
	 * @return array
	 */
	public static function Run(callable $callback, mixed ...$args)
	{
		list ($fiber) = static::Run_no_start($callback, ...$args);
		$start_ret = $fiber->start(...$args);
		return [$fiber, $start_ret];
	}
		
	/**
	 * Inits a virtual thread of work, without starting it (implemented via fibers atm)
	 * 
	 * @param callable $callback
	 * @return array
	 */
	public static function Run_no_start(callable $callback)
	{
		if (!static::$_register_shutdown_done)
		{
			register_shutdown_function(function () { static::On_Shutdown(); });
			static::$_register_shutdown_done = true;
		}
		
		static::Ensure_Spin_Fiber();
		
		$fiber = new \Fiber($callback);
		if (static::$_Fibers === null)
			static::$_Fibers = new \SplObjectStorage();
		static::$_Fibers[$fiber] = [true]; # we will see what we need in here
		return [$fiber];
	}
	
	public static function Socket_Recv_From(Socket $socket, int $max_wait_microsec = 1000000)
	{
		return static::Process_Async_Task(static::Handle_Socket, static::OP_Socket_Recv_From, $socket, $max_wait_microsec);
	}
	
	protected static function Process_Async_Task(int $handle_type, int $op_type, mixed $handle, int $max_wait_microsec = 1000000, ...$args)
	{
		# echo "starting Process_Async_Task\n";
		# @TODO ... we need to make sure we don't overlap the same resource type
		$fiber = \Fiber::getCurrent();
		if (!$fiber)
			throw new \Exception('Must be called inside a Fiber.');
		$async_task = new Q_Async_Task($fiber, $handle, $handle_type, $op_type, $max_wait_microsec, $args);
		
		static::$_Tasks[$handle_type][] = $async_task;
		
		if (static::$_Spin_Fiber->isTerminated())
			die("NOT GOOD!!\n");
		
		if (static::$_Spin_Fiber->isSuspended())
			static::$_Spin_Fiber->resume();
		
		# we wait until we get something (even an error)
		while ($async_task->success === null)
		{
			# echo "suspend :: Process_Async_Task\n";
			\Fiber::suspend();
		}
		
		return $async_task;
	}
	
	protected static function Ensure_Spin_Fiber()
	{
		if ((static::$_Spin_Fiber === null) || static::$_Spin_Fiber->isTerminated())
		{
			static::$_Spin_Fiber = new \Fiber([__CLASS__, 'Spin_Worker']);
			static::$_Spin_Fiber->start();
		}
		else if (static::$_Spin_Fiber->isSuspended())
			static::$_Spin_Fiber->resume();
	}
	
	protected static function Has_Tasks()
	{
		foreach (static::$_Tasks as $tasks)
		{
			if (!empty($tasks))
				return true;
		}
		return false;
	}
	
	protected static function Spin_Worker()
	{
		echo "Spin_Worker TOP\n";
		
		$c_fiber = \Fiber::getCurrent();
		if (!$c_fiber)
			throw new \Exception('Must be on a fiber.');
		
		while (true)
		{
			# echo "Spin_Worker --- loop\n";
			if (!static::Has_Tasks())
			{
				if (static::$_Terminate_If_No_Tasks)
				{
					echo "\$_Terminate_If_No_Tasks\n";
					return;
				}
				else
				{
					# if there is no task we suspend
					\Fiber::suspend();
					continue;
				}
			}
			
			# echo "\$t_count : {$t_count}\n";
			
			# best to be explicit !!!
			$c_time = (int)(microtime(true) * 1000000);
			
			$tasks_finished = [];
			
			foreach (static::$_Tasks as $object_type => $socket_tasks)
			{
				if (empty($socket_tasks))
					continue;
				
				$socket_read = [];
				$socket_write = [];

				$spl_map = new \SplObjectStorage();

				$time_outs = [];

				foreach ($socket_tasks as $a_task_pos => $async_task)
				{
					# test for timeout
					if (($c_time - $async_task->start_time) > $async_task->max_wait_microsec)
					{
						# timeout
						$time_outs[$a_task_pos] = $async_task;
					}
					else
					{
						if ($spl_map->contains($async_task->handle))
							# we can not use the same handle for two operations at the same time
							continue;
							
						$spl_map[$async_task->handle] = [$a_task_pos, $async_task];
						switch ($async_task->op_type)
						{
							case static::OP_Socket_Recv_From:
							{
								$socket_read[] = $async_task->handle;
								break;
							}
							case static::OP_Socket_Send_To:
							{
								$socket_write[] = $async_task->handle;
								break;
							}
							default:
								throw new \Exception('Unknown task.');
						}
					}
				}
				
				$tasks_changed = false;

				if ($time_outs)
				{
					foreach ($time_outs as $k => $async_task)
					{
						$async_task->success = false;
						$async_task->timeout = true;
						unset($socket_tasks[$k]);
						$tasks_changed = true;
						$tasks_finished[] = $async_task;
					}
				}
				
				switch ($object_type)
				{
					case self::Handle_Socket:
					case self::Handle_UDS_Socket:
					{
						if ($socket_read || $socket_write)
						{
							$except = [];
							$changed_count = socket_select($socket_read, $socket_write, $except, 0);
							
							# @TODO - handle $except
							
							if ($changed_count === false)
							{
								throw new \Exception('@TODO');
							}
							else if ($changed_count === 0)
							{
								# nothing happend
							}
							else if ($changed_count && ($changed_count > 0))
							{
							  foreach ([$socket_read, $socket_write] as $sock_list)
								foreach ($sock_list as $sock)
								{
									list($a_task_pos, $async_task) = $spl_map[$sock];
									
									$data = null;
									$max_len = 1024 * 16;
									$address = "";
									$port = 0;
									$bytes = null;
									
									switch ($async_task->op_type)
									{
										case static::OP_Socket_Recv_From:
										{
											$bytes = socket_recvfrom($sock, $data, $max_len, MSG_DONTWAIT, $address, $port);
											break;
										}
										case static::OP_Socket_Send_To:
										{
											# @TODO - we need to send params
											throw new \Exception('@todo');
											$bytes = socket_sendto($sock, $data, $max_len, MSG_DONTWAIT, $address, $port);
											break;
										}
										default:
											throw new \Exception('Not expected.');
									}
									
									if ($bytes === 0)
									{
										throw new \Exception('What does this mean ?! Error ?!');
									}
									else
									{
										$async_task->success = ($bytes === false) ? false : true;
										$async_task->result = ($bytes === false) ? null : [$bytes, $data, $max_len, $address, $port];
										
										unset($socket_tasks[$a_task_pos]);
										$tasks_changed = true;
										
										$tasks_finished[] = $async_task;
									}
								}
							}
						}
					}
				}
			
				if ($tasks_changed)
				{
					static::$_Tasks[$object_type] = array_values($socket_tasks);
					# echo "static::\$_Tasks[{$object_type}] : " . count(static::$_Tasks[$object_type]) . " | ".microtime(true)."\n";
				}
			}
			
			foreach ($tasks_finished as $async_task)
			{
				# var_dump($async_task->fiber, \Fiber::getCurrent(), $async_task->fiber->isStarted(), "susp", $async_task->fiber->isSuspended(), $async_task->fiber->isTerminated(), $async_task->fiber->isRunning());
				if ($async_task->fiber->isSuspended())
				{
					# echo "\$async_task->fiber->resume\n";
					$async_task->fiber->resume();
				}
			}

			if (!static::$_Sync_All)
				# we let a cycle run
				\Fiber::suspend();
			# @TODO - if on shutdown phase ... there is no point to wait
			if (static::$_Microsleep > 0)
				usleep(static::$_Microsleep); # avoid 100% cpu
			# @TODO - detect if there is anything else to do !!! ... we need to have the main execution on a fiber
			#				if all fibers are `on wait` and the main fiber has finished then we can sleep, if not ... resume main fiber
		}
		
		echo "EXEC DONE\n";
	}
}

