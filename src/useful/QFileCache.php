<?php

class QFileCache
{
	
	public static function From_Cache(string $cache_file, $callback, $callback_args, $max_read_time, $max_write_time, $max_wait_on_lock)
	{
		if ( ! (is_float($max_wait_on_lock) || is_int($max_wait_on_lock)))
			$max_wait_on_lock = (int)$max_wait_on_lock;
		
		# make it in microsec
		$max_wait_on_lock = $max_wait_on_lock * 1000000; # in microsec
		
		$cache_file_lock = $cache_file.".lock";
		$cache_lock = null;
		$waited = 0;

		$loop_sleep_ns = 20000;

		$ret = null;
		$got_data = false;

		try
		{
			do
			{
				if (file_exists($cache_file) && ((filemtime($cache_file) + $max_read_time) >= time()) && (($fc = file_get_contents($cache_file)) !== false))
				{
					$ret = \QModel::FromJSON($fc, "[]");
					$got_data = true;
				}

				# we cache on a 10 mins base
				if ((!$got_data) || (!file_exists($cache_file)) || ((filemtime($cache_file) + $max_write_time) < time()))
				{
					# we need to write the cache
					if ($got_data)
					{
						# we have what to return, write after the script ends
						\QApp::AddCallbackAfterResponse(function () use ($callback, $callback_args, $cache_file)
						{
							# we ask for a cache refresh
							$ret = call_user_func_array($callback, $callback_args);
							file_put_contents($cache_file, \QModel::QToJSon($ret));
						}, []);
					}
					else
					{
						# do it now , lock
						$cache_lock = \QFileLock::Lock_File($cache_file_lock, 0);
						if (!$cache_lock)
						{
							# we will re-try again the entire process
							# wait for 0.02 seconds
							usleep($loop_sleep_ns);
							$waited += $loop_sleep_ns;
							continue;
						}
						
						$ret = call_user_func_array($callback, $callback_args);
						file_put_contents($cache_file, \QModel::QToJSon($ret));
						$got_data = true;
						
						$cache_lock->unlock();
						$cache_lock = null;
					}
				}

				if ($got_data)
					return $ret;
			}
			while ($waited < $max_wait_on_lock);
		}
		finally
		{
			if ($cache_lock)
				$cache_lock->unlock();
		}
	}	
}
