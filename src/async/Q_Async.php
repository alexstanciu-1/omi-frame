<?php

final class Q_Async
{
	const Handle_Socket = 1;
	const Handle_UDS_Socket = 2;
	const Handle_Mysqli_Socket = 3;
	const Handle_Stream_Socket = 4;
	const Handle_File_Stream = 5;
	const Handle_Process = 6;
	const Handle_Curl_Multi = 7;
	
	const OP_Socket_Recv_From = 101;
	const OP_Socket_Send_To = 102;
	const OP_Curl_Exec = 103;
	
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
	
	/**
	 * 
	 * @var Fiber
	 */
	protected static $_Spin_Fiber;
	protected static $_Terminate_If_No_Tasks = false;
	protected static $_Microsleep = 0;
	protected static $_Sync_All = false;
	
	protected static $_register_shutdown_done = false;
	protected static $last_sleep = null;
	
	public static function check_terminated(array $fibers_list, \Fiber $c_fiber = null)
	{
		if ($c_fiber === null)
			$c_fiber = \Fiber::getCurrent();
		
		$not_terminated = [];
		$fib_terminated = [];
		foreach ($fibers_list as $fib_key => $fib)
		{
			if ($c_fiber && ($c_fiber === $fib))
				throw new \Exception('You can not call `wait_one` from a fiber that is in the list `$fibers_list`.');
			if (!$fib->isTerminated())
				$not_terminated[$fib_key] = $fib;
			else
				$fib_terminated[$fib_key] = $fib;
		}
		
		return [$fib_terminated, $not_terminated];
	}
	
	public static function wait_any(array $fibers_list, float $run_until = null)
	{
		if (empty($fibers_list))
			return [[], []];
		
		$c_fiber = \Fiber::getCurrent();
		
		$not_terminated = [];
		$fib_terminated = [];
		
		$re_check = false;
		
		do
		{
			# check as we start
			list ($fib_terminated, $not_terminated) = static::check_terminated($fibers_list, $c_fiber);
			if (!empty($fib_terminated))
				return [$fib_terminated, $not_terminated];
			else if (empty($not_terminated))
				return [[], []];

			$t0 = microtime(true);

			$tasks = [];
			$one_has_terminated = null;

			foreach ($not_terminated as $fib_key => $fib)
			{
				$re_check = true;
				# spin the fiber
				$fr_ret = $fib->resume();
				if ($fib->isTerminated())
				{
					$one_has_terminated = [$fib, $fib_key];
					break;
				}

				$tasks[(is_object($fr_ret) && isset($fr_ret->type)) ? $fr_ret->type : 0][$fib_key] = $fr_ret;
			}

			if ((!$one_has_terminated) && (($run_until === null) || (microtime(true) <= $run_until)))
			{
				static::sleep_if_need($t0, $tasks);
				$re_check = true;
			}
		}
		# we let it continue to another check , to make sure we grab other fibers that may have terminated
		while (($run_until === null) || (microtime(true) <= $run_until));
		
		if ($re_check)
		{
			list ($fib_terminated, $not_terminated) = static::check_terminated($fibers_list, $c_fiber);
		}

		return [$fib_terminated, $not_terminated];
	}
	
	protected static function sleep_if_need(float $time_since_loop, array $tasks = [])
	{
		if (isset($tasks['curl'])) # || isset($tasks['socket']) ...
		{
			$elapsed_in_ms = (microtime(true) - $time_since_loop) * 1000;
			if ($elapsed_in_ms < 5.0)
			{
				usleep((5.0 - $elapsed_in_ms) * 1000);
			}
		}
	}
	
	public static function Sync_All(int $microsleep = 1, callable $tick_callback = null)
	{
		# all fibers & tasks need to be completed
		if (!static::$_Spin_Fiber)
			return;
		
		$saved_microsleep = static::$_Microsleep;
		
		try
		{
			static::$_Sync_All = true;
			static::$_Microsleep = $microsleep;
			
			/*
			var_dump([
				'static::$_Spin_Fiber->isSuspended()' => static::$_Spin_Fiber->isSuspended(),
				'static::$_Spin_Fiber->isTerminated()' => static::$_Spin_Fiber->isTerminated(),
				# 'static::$_Spin_Fiber->isSuspended()' => static::$_Spin_Fiber->isSuspended(),
				]);
			*/
			
			while (static::Has_Tasks() && (!static::$_Spin_Fiber->isTerminated()))
			{
				# echo "Sync_All :: Has_Tasks\n";
				if (static::$_Spin_Fiber->isSuspended())
					static::$_Spin_Fiber->resume();
				if ($tick_callback)
				{
					$stop = $tick_callback();
					if ($stop)
					{
						break;
					}
				}
			}
		}
		finally
		{
			static::$_Sync_All = false;
			static::$_Microsleep = $saved_microsleep;
		}
	}
	
	protected static function On_Shutdown()
	{
		# maybe only explicit ?!
		
		/*
		echo "On_Shutdown\n";
		static::$_Microsleep = 1;
		static::$_Terminate_If_No_Tasks = true;
		# static::Sync_All();
		*/
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
		static::Ensure_Spin_Fiber();
		
		$fiber = new \Fiber($callback);
		if (static::$_Fibers === null)
			static::$_Fibers = new \SplObjectStorage();
		static::$_Fibers[$fiber] = [true]; # we will see what we need in here
		return [$fiber];
	}
	
	public static function Socket_Recv_From(\Socket $socket, int $max_wait_microsec = 1000000)
	{
		throw new \Exception('redo like curl_exec');
		return static::Process_Async_Task(static::Handle_Socket, static::OP_Socket_Recv_From, $socket, $max_wait_microsec);
	}
	
	public static function curl_exec(\CurlHandle $curl_handle, \CurlMultiHandle $multi = null, int $max_wait_microsec = null, object $async_task = null)
	{
		$fiber = \Fiber::getCurrent();
		if (!$fiber)
			throw new \Exception('Only usable on a fiber.');
		
		$url = curl_getinfo($curl_handle, CURLINFO_EFFECTIVE_URL);
		
		# echo "FIBER CURL START | ".(\DateTime::createFromFormat('U.u', microtime(true)))->format("H:i:s.u")." | {$url}\n";
		# \QApp::Log_To_File("FIBER CURL START | {$url}\n");
		
		if ($max_wait_microsec === null)
			# we need to test this !!!
			$max_wait_microsec = 1000000 * 120;
		
		$multi_created_here = false;
		if (!$multi)
		{
			$multi = curl_multi_init();
			curl_multi_add_handle($multi, $curl_handle);
			$multi_created_here = true;
		}
		
		if ($async_task === null)
			$async_task = new \stdClass();
		
		$async_task->type = 'curl';
		$async_task->t_start = microtime(true);
		$async_task->curl = $curl_handle;
		$async_task->curl_multi = $multi;
		
		# $return = static::Process_Async_Task(static::Handle_Curl_Multi, static::OP_Curl_Exec, $handle, $max_wait_microsec);
		$multi_select_called = false;
		
		# echo "curl_exec # START #".($fiber ? spl_object_id($fiber) : 'n/a')."# " . date("H:i:s") . "." . end(explode(" ", microtime())), "\n";
		
		do
		{
			$still_running = null;
			$t0 = microtime(true);
			$status = curl_multi_exec($multi, $still_running);
			
			if ($status !== CURLM_OK)
			{
				# error
				$async_task->success = false;
				$async_task->result = false; # , $info, $status, curl_multi_strerror($status)];
				break;
			}
			
			if ($still_running && (!$multi_select_called))
			{
				# do at least one multi select
				curl_multi_select($multi, 0.0001);
				$multi_select_called = true;
				
				$status = curl_multi_exec($multi, $still_running);
			}
			
			if ($still_running)
			{
				# echo "curl_exec # FIBER::suspend #".($fiber ? spl_object_id($fiber) : 'n/a')."# " . date("H:i:s") . "." . end(explode(" ", microtime())), "\n";
				# still running / nothing happend
				$fiber->suspend($async_task);
			}
			else 
			{
				/*
				while (false !== ($info = curl_multi_info_read($multi))) {
					qvar_dump('curl_multi_info_read', $info);
				}
				*/
				$async_task->t_end = microtime(true);
				
				$inf = curl_getinfo($curl_handle);
				
				if ((!curl_errno($curl_handle)) && (!($inf['http_code'] ?? null)) && \QAutoload::GetDevelopmentMode())
				{
					list(, , $curl_opts) = q_curl_find($curl_handle);
					$q_messages = null;
					$all_messag = curl_multi_info_read($multi, $q_messages);
					/*
					qvar_dump("LOOOOK!!!!", $q_messages, $all_messag, 
									$status, $still_running, 
									curl_multi_errno($multi), curl_multi_errno($multi), 
									curl_multi_getcontent($curl_handle), 
									curl_errno($curl_handle), curl_error($curl_handle), 
									$inf, $curl_opts);
					*/
					if (\QWebRequest::IsAjaxRequest())
					{
						# throw new \Exception('PLEASE LOOOOK into this !!!!');
					}
					else
					{
						# die;
					}
					
					$async_task->success = false;
					$async_task->result = false;
					
					break;
				}
				
				if ($status > 0)
				{
					# error
					$async_task->success = false;
					$async_task->result = false; # , $info, $status, curl_multi_strerror($status)];
				}
				else
				{
					# finished
					$errno = curl_errno($curl_handle);
					$rc_multi_get = curl_multi_getcontent($curl_handle);

					if (($errno == 0) && is_string($rc_multi_get))
					{
						# all good
						$async_task->success = true;
						$async_task->result = $rc_multi_get; # [$rc_multi_get, $info, $errno, curl_strerror($errno)];
					}
					else
					{
						# error
						$async_task->success = false;
						$async_task->result = false; # , $info, 0, null];
					}
				}
				
				break;
			}
		}
		while ($still_running);
		
		# echo "curl_exec # DONE #".($fiber ? spl_object_id($fiber) : 'n/a')."# " . date("H:i:s") . "." . end(explode(" ", microtime())), "\n";
		
		if ($multi_created_here)
		{
			curl_multi_remove_handle($multi, $curl_handle);
			curl_multi_close($multi);
			unset($multi);
		}
		
		# \QApp::Log_To_File("FIBER CURL DONE | {$url}\n");
		# echo "curl_exec # RETURN #".($fiber ? spl_object_id($fiber) : 'n/a')."# " . date("H:i:s") . "." . end(explode(" ", microtime())), "\n";

		return $async_task->result;
	}
	
	protected static function Process_Async_Task(int $handle_type, int $op_type, mixed $handle, int $max_wait_microsec = 1000000, ...$args)
	{
		# echo "starting Process_Async_Task\n";
		# @TODO ... we need to make sure we don't overlap the same resource type
		$fiber = \Fiber::getCurrent();
		if (!$fiber)
			throw new \Exception('Must be called inside a Fiber.');
		$async_task = new Q_Async_Task($fiber, $handle, $handle_type, $op_type, $max_wait_microsec, $args);
		
		$async_task->run();
		/*
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
		*/
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
		
		if (self::$last_sleep === null)
			self::$last_sleep = microtime(true);
		
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
				$curl_multi_list = [];

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
							case static::OP_Curl_Exec:
							{
								$curl_multi_list[] = $async_task->handle;
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
								{
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
						break;
					}
					case self::Handle_Curl_Multi:
					{
						foreach ($curl_multi_list as $curl_obj)
						{
							list($a_task_pos, $async_task) = $spl_map[$curl_obj];

							$curl_multi = $curl_obj->multi;
							$curl_handle = $curl_obj->curl;
							
							$still_running = null;
							$t0 = microtime(true);
							$status = curl_multi_exec($curl_multi, $still_running);
							$t1 = microtime(true);
							
							# echo "curl_multi_exec @ ".(($t1 - $t0) * 1000)." | ".microtime()."\n";
							
							/*
							$status_str = [
									CURLM_CALL_MULTI_PERFORM	=> 'CURLM_CALL_MULTI_PERFORM',
									CURLM_OK					=> 'CURLM_OK',
									CURLM_BAD_HANDLE			=> 'CURLM_BAD_HANDLE',
									CURLM_BAD_EASY_HANDLE		=> 'CURLM_BAD_EASY_HANDLE',
									CURLM_OUT_OF_MEMORY			=> 'CURLM_OUT_OF_MEMORY',
									CURLM_INTERNAL_ERROR		=> 'CURLM_INTERNAL_ERROR',
								][$status];
							*/
							if ($still_running)
							{
								# still running / nothing happend
							}
							else 
							{	
								$info = curl_getinfo($curl_handle);
								if ($status > 0)
								{
									# error
									$async_task->success = false;
									$async_task->result = false; # , $info, $status, curl_multi_strerror($status)];
								}
								else
								{
									# finished
									$errno = curl_errno($curl_handle);
									$rc_multi_get = curl_multi_getcontent($curl_handle);

									if (($errno == 0) && is_string($rc_multi_get))
									{
										# all good
										$async_task->success = true;
										$async_task->result = $rc_multi_get; # [$rc_multi_get, $info, $errno, curl_strerror($errno)];
									}
									else
									{
										# error
										$async_task->success = false;
										$async_task->result = false; # , $info, 0, null];
									}

									unset($socket_tasks[$a_task_pos]);
									$tasks_changed = true;

									$tasks_finished[] = $async_task;
								}
							}
						}
						break;
					}
					default:
					{
						throw new \Exception('Not implemented');
						# break;
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
			
			# if (!static::$_Sync_All)
				# we let a cycle run
			\Fiber::suspend();
			# @TODO - if on shutdown phase ... there is no point to wait
			if ((static::$_Microsleep > 0) && ($since_last_sleep = (int)((microtime(true) - self::$last_sleep)*1000000))
											&& (($to_sleep = (int)(static::$_Microsleep - $since_last_sleep)) > 0))
			{
				# echo "SLEEP FOR : {$to_sleep}\n";
				usleep($to_sleep); # avoid 100% cpu
			}
			self::$last_sleep = microtime(true);
			# @TODO - detect if there is anything else to do !!! ... we need to have the main execution on a fiber
			#				if all fibers are `on wait` and the main fiber has finished then we can sleep, if not ... resume main fiber
		}
		
		echo "EXEC DONE\n";
	}
}

