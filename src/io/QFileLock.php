<?php

/**
 * QFileLock can be used for locking and unlocking files. 
 * It's easier to use and has the ability to wait for a max amount of time.
 */
class QFileLock
{
	/**
	 * The path of the lock
	 * 
	 * @var string
	 */
	protected $file_path;
	/**
	 * True if it's locked
	 *
	 * @var boolean
	 */
	protected $locked = false;
	/**
	 * The handle of the lock after a fopen call
	 * 
	 * @var resource
	 */
	protected $lock_handle;
	protected $open_mode;
	
	/**
	 * The constructor for the QFileLock object
	 * 
	 * @param string $file_path
	 */
	public function __construct($file_path = null, $open_mode = "a+")
	{
		$this->file_path = $file_path;
		$this->open_mode = $open_mode;
	}
	
	public function write($string, $length = null)
	{
		if ($this->lock_handle && is_resource($this->lock_handle))
		{
			if ($length !== null)
				return fwrite($this->lock_handle, $string, $length);
			else
				return fwrite($this->lock_handle, $string);
		}
		else
			return false;
	}
	
	public function read($length)
	{
		if ($this->lock_handle && is_resource($this->lock_handle))
			return fread($this->lock_handle, $length);
		else
			return false;
	}
	
	public static function Lock_File($file_path = null, $max_wait = 10)
	{
		$lock = new static;
		$got_lock = $lock->lock($file_path, $max_wait);
		return $got_lock ? $lock : false;
	}
	
	/**
	 * Tries to aqqire a lock
	 * $max_wait WILL NOT WORK UNDER WINDOWS
	 * 
	 * @param string $file_path
	 * @param integer $max_wait
	 * @return boolean
	 * @throws Exception
	 */
	public function lock_do($file_path = null, $max_wait = 10)
	{
		$is_WINDOWS = (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN');
		
		if (isset($this) && ($this instanceof QFileLock))
		{
			if (!$file_path)
				$file_path = $this->file_path;
			else
				$this->file_path = $file_path;
			
			if ($this->locked)
				return true;

			$this->lock_handle = $f_lock = fopen($file_path, $this->open_mode);
			if (!is_resource($this->lock_handle))
				return false;

			$this->locked = false;
			$loops = 0;
			while (!$this->locked)
			{
				if ($loops > $max_wait)
					return false;
				
				$this->locked = $is_WINDOWS ? flock($f_lock, LOCK_EX) : flock($f_lock, LOCK_EX | LOCK_NB);
				
				if ($this->locked)
				{
					# fix a fastCGI issue
					ignore_user_abort(true);
					# setup a callback
					register_shutdown_function([$this, "unlock"], getcwd());
					
					return true;
				}
				else if (!$max_wait)
					return false;
				else if (!$is_WINDOWS)
				{
					// there is no point to wait on windows as it will block anyway
					sleep(1);
				}

				$loops++;
			}
			return false;
		}
		else
		{
			
		}
	}
	
	public static function Lock($file_path = null, $max_wait = 10)
	{
		$is_WINDOWS = (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN');
		
		if (!$file_path)
			throw new Exception("Please call lock with file_path when using it static");

		$lock = new QFileLock($file_path);
		$lock->lock_do($file_path, $max_wait);

		if ($lock->locked)
		{
			return $lock;
		}
		else
		{
			// release the lock and return null
			$lock->unlock();
			unset($lock);
			return false;
		}
	}
	
	/**
	 * Tries to aqqire a lock
	 * $max_wait WILL NOT WORK UNDER WINDOWS
	 * 
	 * @param string $file_path
	 * @param integer $max_wait
	 * @return \QFileLock
	 */
	public static function TryLock($file_path = null, $max_wait = 10, string $open_mode = 'a+')
	{
		$has_lock = false;
		$lock = null;
		try
		{
			$lock = new \QFileLock($file_path, $open_mode);
			$has_lock = $lock->lock_do(null, $max_wait);
			
			if ($has_lock)
				return $lock;
			else
			{
				try
				{
					$lock->unlock();
				}
				catch (Exception $ex_2) {}
				return null;
			}
		}
		catch (Exception $ex)
		{
			if ($has_lock && $lock)
			{
				try
				{
					$lock->unlock();
				}
				catch (Exception $ex_2) {}
			}
			return false;
		}
	}
	
	/**
	 * Returns the status of the lock
	 * 
	 * @return boolean
	 */
	public function isLocked()
	{
		return $this->locked;
	}

	/**
	 * Unlocks. Returns true if the instance was locked before the call.
	 * 
	 * @return boolean
	 */
	public function unlock($work_dir = null)
	{
		if ($work_dir)
			chdir($work_dir);
		
		$ret = false;
		if ($this->locked)
		{
			// unlock
			flock($this->lock_handle, LOCK_UN);
			$this->locked = false;
		}
		if ($this->lock_handle)
		{
			// close the handle
			fclose($this->lock_handle);
			$this->lock_handle = null;
		}
		
		return $ret;
	}
	
	public function put_contents(string $content)
	{
		if (!($this->locked && $this->lock_handle))
			return false;
		if (!rewind($this->lock_handle))
			return false;
		$c_len = strlen($content);
		if (fwrite($this->lock_handle, $content) !== $c_len)
			return false;
		if (!ftruncate($this->lock_handle, $c_len))
			return false;
		# $ret = fflush($this->lock_handle);
		echo "pid: {$content}\n";
		return fflush($this->lock_handle);
	}
}
