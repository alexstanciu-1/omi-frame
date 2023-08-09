<?php

namespace Omi\DK;

class App_Proxy
{
	public static function Run(string $request = null, bool $skip_first = false)
	{
		qvar_dump("START App_Proxy::Run: " . round(microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'], 4));
		
		$request = $request ?? ($_GET['__or__'] ?? '');
		$url = new \QUrl(trim($request, " /"));
		if ($skip_first)
			$url->next();
		
		$c_url = $url->current();
		if (($c_url === null) || ($c_url === false))
		{
			$redirect_to_projects = false;
			
			$req_uri_parts = (new \QUrl(trim($_SERVER['REQUEST_URI'], " \\/")))->getParts();
			
			# show options
			if ($redirect_to_projects)
			{
				if ($skip_first)
					array_pop($req_uri_parts);
				$req_uri_parts[] = 'Projects';

				header("Location: " . "/" . implode("/", $req_uri_parts));
				exit;
			}
			else
			{
				$req_url = "/" . implode("/", $req_uri_parts) . "/";
				
				$prjs = \QQuery("Projects.{* ORDER BY Name}")->Projects;
				
				if (empty($prjs) || (!count($prjs)))
					echo "No projects defined.";
				else
				{
					foreach ($prjs as $prj)
					{
						$possible_entries = static::Get_Exec_Paths($prj);
						
						if (empty($possible_entries))
						{
							echo "No exec path determined for project [id:{$prj->Id}]: {$prj->Name}";
						}
						else if (is_string($possible_entries))
						{
							$url = rtrim($req_url, "/") . "/" . ltrim($prj->Dev_URL, "/");
							echo "<a href='".htmlentities($url)."'>" . htmlspecialchars($prj->Name) . "</a>";
						}
						else # array
						{
							foreach ($possible_entries as $pe_caption => $pe)
							{
								$url = rtrim($req_url, "/") . "/" . trim($prj->Dev_URL, "/") . "/" . $pe_caption;
								echo "<a href='".htmlentities($url)."'>" . htmlspecialchars($prj->Name . " - " . $pe_caption) . "</a>";
								echo "<br/>\n";
							}
						}
						
						echo "<br/>\n";
					}
				}
			}
		}
		else if (is_string($url->current()))
		{
			$prjs = \QQuery("Projects.{* WHERE TRIM('/' FROM Dev_URL)=?}", [$url->current()])->Projects;
			if (isset($prjs[0]))
			{
				$url->next();
				static::Run_Project($prjs[0], $url);
			}
			else
			{
				echo "Project not found by url: " . $url->current();
			}
		}
		else
			throw new \Exception('Bad URL parsing.');
	}
	
	public static function Run_Project(Project $project, \QUrl $url)
	{
		if (!($project->Id ?? null))
			throw new \Exception('Missing project ID');
		if (!($project->Path ?? null))
			throw new \Exception('Missing project Path');
		if (!is_dir($project->Path))
				throw new \Exception('Provided project path is not a directory (or does not exists).');
		
		{
			# make some changes
			# /mnt/d/Work_2020/MY-DEV/omi-2023/VF_2.0/voip-fuse/code/model/App.class.php
			$test_touch = $project->Path . "/voip-fuse/code/model/App.class.php";
			touch($test_touch);
		}

		{
			$exec_path = static::Get_Exec_Paths($project);
			$exec = null;
			if (is_array($exec_path))
			{
				if (($url->current() === null) || ($url->current() === false))
					throw new \Exception('Missing url path.');
				$exec = $exec_path[$url->current()] ?? null;
				if (!$exec)
					throw new \Exception('Unable to find exec.');

				$url->next();
			}
			else if (is_string($exec_path))
			{
				$exec = $exec_path;
			}

			if (!$exec)
				throw new \Exception('Unable to determine exec.');
			if (!file_exists($exec))
				throw new \Exception('Determined exec does not exists.');
		}
		
		
		
		# qvar_dump($exec, $url->current());
		# determine URL consumed / remaning ... to setup a good env
		
		# 1. do a compile
		# CLI mode ?! ... with arguments 
		{
			# make some changes
			# /mnt/d/Work_2020/MY-DEV/omi-2023/VF_2.0/voip-fuse/code/model/App.class.php
			$test_touch = $project->Path . "/voip-fuse/code/model/App.class.php";
			
			touch($test_touch);
			
			qvar_dump("BEFORE Sync_Source_To_Dev_Kit: " . round(microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'], 4));
			
			# @TODO - we only need to do this on WINDOWS !!!
			list ($ok, $local_path , $new_or_changed, $to_delete, $new_meta) = static::Sync_Source_To_Dev_Kit($project);
			
			qvar_dump("AFTER Sync_Source_To_Dev_Kit: " . round(microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'], 4));
			
			# setup files sync watching system !
			static::Setup_Sync_Watching($project, $exec, $exec_path);
			
			die("Setup_Sync_Watching READY.");
		
			# @TODO - we need to check/wait that we are in sync | maybe some progress/status would be nice
			
			# qvar_dump('$ok, $local_path , $new_or_changed, $to_delete, $new_meta', 
			#		$ok, $local_path , $new_or_changed, $to_delete, $new_meta);
			# 
			$exec_rel = substr($exec, strlen($project->Path));
			$local_exec = $local_path . $exec_rel;
			
			# inotifywait --monitor --timefmt '%F %T' --format '%T %w%f %e' --recursive /home/alex/public_html/omi-frame/dev-kit/temp/prj_sync/1/
			/*
			use & to run it on background
			inotifywait -m /dir &
			*/
			
			# qvar_dump($local_path, $exec_rel, $project);
			# die;
			$full_resync = false;
			if ($full_resync)
			{
				$cmd = "php " . escapeshellarg($local_exec) . " " . escapeshellarg("do-compile") . " " . escapeshellarg("full-resync");
			}
			else if ($new_or_changed || $to_delete)
			{
				# @TODO - full sync
				# full -> add arg : full-resync
				$cmd = "php " . escapeshellarg($local_exec) . " " . escapeshellarg("do-compile");
			}
			else
			{
				$cmd = "php " . escapeshellarg($local_exec) . " " . escapeshellarg("do-compile") . " " . escapeshellarg("info");
			}
			
			$t0 = microtime(true);
			list($rc, $out) = static::Exec($cmd, dirname($local_exec));
			$t1 = microtime(true);
			
			qvar_dump($t1 - $t0, "AFTER Exec | compile: " . round(microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'], 4), $out, $cmd, dirname($local_exec));
			die;
			
			$dec = json_decode( implode("\n", $out) , true);
			
			qvar_dump($dec , json_decode(file_get_contents($dec['temp-folder'] . "modified_gen.json"), true) );
			
			$exec_rel = substr($exec, strlen($project->Path));
			$local_exec = $local_path . $exec_rel;
			
			qvar_dump('$local_exec', $local_exec);
			
			die;
			# this can be done async, after completion ... and we're not too woried about the delay
			# but the same files needs to be pushed remote !
			$t0 = microtime(true);
			$cmd = 'find '.escapeshellarg($local_path).' '.
						'-type f -path \'*/\~gens/*\' -mmin -10 '.
						'-printf \'%s %T@ %p\n\' ';		
			list($rc, $out) = static::Exec($cmd);
			$t1 = microtime(true);

			qvar_dump($t1 - $t0, '$cmd, $rc, $out', $cmd, $rc, $out);
			die;
			
			# compute diffs, new, changed, diffs
			
			# take actions
			
			# inotify ... with exclude and everything else to keep the code in sync
			# run in the BG 
			
			# 1. rsync to the ubuntu WM
			# 2. compile
			
			# Path[string(29)][protected][set]: "/mnt/d/Work_2020/VOIP/VF_2.0/"
			
			
			
			# qvar_dump($cmd, $local_path . $exec_rel, file_exists($local_exec));
			# die;
			
			# from here we need a list with everything that was generated/changed on disk			
			# then a thread would push to remote (including code files changes)
			#  and a thread would push compiled data to source
			
			qvar_dump("sss", $cmd, $rc, $new_or_changed, $to_delete);
			
			die;
			#if ($out)
			#	echo implode("\n", $out);
			# exec($cmd);
		}
		
		# 2. push to remote & adapt paths !
		# rsync or explicit | could use curl ?!
		
		# 3. proxy run
		# 
	}
	
	public static function Exec(string $command, string $run_dir = null, bool $output_as_lines = true)
	{
		try
		{
			$saved_dir = null;
			if ($run_dir && (($rp = realpath($run_dir)) !== ($cwd = getcwd())))
			{
				$saved_dir = $cwd;
				chdir($rp);
			}
			
			$rc = null;
			$t0 = microtime(true);
			$out = shell_exec($command);
			$t1 = microtime(true);
			
			if (is_string($out))
			{
				$rc = 0;
				if ($output_as_lines)
					$out = preg_split("/(\\r?\\n)/uis", $out, -1, PREG_SPLIT_NO_EMPTY);
			}
			else
			{
				$rc = -1;
			}
			
			return [$rc, $out];
		}
		finally
		{
			if ($saved_dir !== null)
				chdir($saved_dir);
		}
	}
	
	public static function Get_Exec_Paths(Project $prj)
	{
		$m = null;
		preg_match_all("/\\*/uis", $prj->Exec_Path ?? '', $m);
		$stars_count = isset($m[0]) ? count($m[0]) : 0;
		if ($stars_count > 1)
			throw new \Exception('Wrong `Exec_Path`, only one wildcard character is allowed.');
		
		$exec_path = trim($prj->Exec_Path ?? 'index.php');
		$scan_path = rtrim($prj->Path, "/")."/".ltrim($exec_path, "/");
		$possible_entries = glob($scan_path);
		
		if (empty($possible_entries))
			return $possible_entries;
		else
		{
			if ($stars_count === 0)
			{
				if (count($possible_entries) === 1)
					return reset($possible_entries);
				else
					throw new \Exception('More than one possible executable.');
			}
			else
			{
				$ret = [];
				$m = null;
				preg_match("/^[^\\*]+/uis", $scan_path, $m);

				foreach ($possible_entries as $pe)
				{
					if (isset($m[0]))
						$pe_caption = substr($pe, strlen($m[0]));
					# $pe_caption = substr($pe, strlen($prj->Path));
					if (substr($pe_caption, -strlen('/index.php')) === '/index.php')
						$pe_caption = substr($pe_caption, 0, -strlen('/index.php'));

					$ret[$pe_caption] = $pe;
				}

				return $ret;
			}
		}
	}
	
	public static function Sync_Source_To_Dev_Kit(Project $project)
	{
		$t00 = microtime(true);
		$cmd = 'find '.escapeshellarg($project->Path).' '.
						'-type f -not -path \'*/\.git/*\' -not -path \'*/\~gens/*\' -not -path \'*/temp/*\' -not -path \'*/\~backend/*\' -not -path \'*/nbproject/*\' '.
						'-printf \'%s %T@ %p\n\' ';
		list ($rc, $out) = static::Exec($cmd);
		
		if ((string)$rc !== '0')
			throw new \Exception('Find command to scan files has failed.');
		if (!is_array($out))
			throw new \Exception('Find command to scan files has returned a bad output.');
		
		$project_path_len = strlen(realpath($project->Path)) + 1;
		$local_path = getcwd()."/temp/prj_sync/{$project->Id}/";
		$local_meta = getcwd()."/temp/prj_sync/{$project->Id}_state.json";

		$local_data = [];
		$full_sync = false;

		if (file_exists($local_meta))
			$local_data = json_decode(file_get_contents($local_meta), true);
		else
			$full_sync = true;
		
		$new_or_changed = [];
		$to_delete = $local_data;
		$dirs = [];

		$data = [];
		$new_meta = [];

		foreach ($out as $out_line)
		{
			list ($size, $m_time, $path) = preg_split("/(\\s+)/uis", $out_line, 3);
			$m_time = (int)((float)$m_time);
			$size = (int)$size;
			$data[$path] = [$size, $m_time];

			$l_path = $local_path . substr($path, $project_path_len);
			$local = $full_sync ? null : ($local_data[$path] ?? null);
			if (!$full_sync)
				unset($to_delete[$path]);

			if ($full_sync || ($local[0] !== $size) || ($local[1] !== $m_time))
				$new_or_changed[$path] = [$size, $m_time, $l_path, $local[0] ?? null, $local[1] ?? null];

			$dirs[dirname($l_path)] = true;
			$new_meta[$path] = [$size, $m_time, $l_path];
		}
		
		if ($full_sync || $new_or_changed || $to_delete)
		{
			# start
			# 1. unlink ... so in case it breaks ... there will be a full sync
			if (file_exists($local_meta))
				unlink($local_meta);
			if ($full_sync && is_dir($local_path))
			{
				# @TODO delete everything in $local_path
				exec("rm -R -f " . escapeshellarg($local_path));
			}

			if (!is_dir($local_path))
			{
				$ok = qmkdir($local_path);
				if (!$ok)
					throw new \Exception('Unable to create dir ' . $local_path);
			}

			foreach ($new_or_changed as $src_path => $misc_data)
			{
				list ($size, $m_time, $sync_path) = $misc_data;
				# $t1 = microtime(true);
				if (!is_dir(dirname($sync_path)))
					qmkdir(dirname($sync_path));
				$ok = copy($src_path, $sync_path);
				# $ok = file_put_contents($sync_path, file_get_contents($src_path));
				# $t2 = microtime(true);
				
				touch($sync_path, $m_time);
				if (!$ok)
					throw new \Exception('Failed to copy file `'.$src_path.'` TO `' . $sync_path . '`');
			}
			
			if ((!$full_sync) && $to_delete)
			{
				foreach ($to_delete as $src_path => $misc_data)
				{
					list ($size, $m_time, $sync_path) = $misc_data;
					if (file_exists($sync_path))
					{
						$ok = unlink($sync_path);
						if (!$ok)
							throw new \Exception('Failed to remove file `' . $sync_path . '`');
					}
				}
			}

			# if we are here ... all should be ok
			$json_enc = json_encode($new_meta, JSON_PRETTY_PRINT | JSON_UNESCAPED_LINE_TERMINATORS | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
			if ($json_enc === false)
				throw new \Exception('Unable to encode files state info.');
			$ok = file_put_contents($local_meta, $json_enc);
			if (!$ok)
				throw new \Exception('Unable to write files state info.');
		}

		$t01 = microtime(true);

		# qvar_dump($t01 - $t00, $dirs, $new_or_changed, $to_delete);
		# die;
		return [true, $local_path, $new_or_changed, $to_delete, $new_meta];
	}
	
	protected static function Setup_Sync_Watching(Project $project, $exec, $exec_path)
	{
		if (! ($project->Id ?? null))
			throw new \Exception('Invalid project id.');
		
		echo "<pre>";
		$current_dir = getcwd();
		
		$local_path = $current_dir."/temp/prj_sync/{$project->Id}/";
		$exec_rel = substr($exec, strlen($project->Path));
		$local_exec = $local_path . $exec_rel;
		
		
		$watch_mask = IN_CLOSE_WRITE | IN_ATTRIB | IN_CREATE | IN_DELETE | IN_DELETE_SELF;
		$t0 = microtime(true);
		$inotify = inotify_init();
		
		# $q = static::inotify_add_watch_recursive($inotify, realpath($current_dir), $watch_mask);
		$q = static::inotify_add_watch_recursive($inotify, "/mnt/d/Work_2020/MY-DEV/omi-2023/omi-frame", $watch_mask);
		# var_dump($q);
		$t1 = microtime(true);
		
		qvar_dump($q, ($t1 - $t0) * 1000);
		
		$fd = $inotify;
		{
			// generate an event
			touch("/mnt/d/Work_2020/MY-DEV/omi-2023/omi-frame/README.md");
			
			// The following methods allows to use inotify functions without blocking on inotify_read():
			// - Using stream_select() on $fd:
			$read = array($fd);
			$write = null;
			$except = null;
			$ss_rc = stream_select($read,$write,$except,0);
			
			qvar_dump('$ss_rc', $ss_rc);

			// - Using stream_set_blocking() on $fd
			$ssb_ok = stream_set_blocking($fd, 0);
			qvar_dump('$ssb_ok', $ssb_ok);
			$ino_read = inotify_read($fd); // Does no block, and return false if no events are pending
			qvar_dump('$ino_read', $ino_read, $q[$ino_read[0]['wd']]);

			// - Using inotify_queue_len() to check if event queue is not empty
			$queue_len = inotify_queue_len($fd); // If > 0, inotify_read() will not block
			
			qvar_dump($queue_len);

			// Stop watching __FILE__ for metadata changes
			# inotify_rm_watch($fd, $watch_descriptor);

			// Close the inotify instance
			// This may have closed all watches if this was not already done
			fclose($fd);
		}

		
		die;
		
		$procs_to_check = [
			[
				# start the process required to sync files
				$current_dir."/temp/prj_sync/{$project->Id}_sync_watch.lock",
				"php ". escapeshellarg( getcwd()."/index.php") . " setup_sync_watching {$project->Id}",
				$current_dir,
			],
			
			[
				$current_dir."/temp/prj_sync/{$project->Id}_compiler_watch.lock",
				"php ". escapeshellarg($local_exec) . " " . escapeshellarg("watch-compile") .  " " . escapeshellarg($local_path),
				dirname($local_exec),
			],
			
		];
				
		# qvar_dump('$procs_to_check', $procs_to_check);
		# die;
		
		foreach ($procs_to_check as $ptc)
		{
			list ($lock_path, $cmd, $exec_in_dir) = $ptc;
			
			$lock = null;
			try
			{
				$lock = \QFileLock::Lock($lock_path, 0);
				if ($lock)
				{
					$lock->unlock();
					$lock = null;
					
					# nohup {$cmd}  >/dev/null 2>&1 &
					if ($exec_in_dir ?? null)
						chdir($exec_in_dir);
					
					$out = shell_exec($cmd);
					# echo "dir: ", getcwd(), "\n", "shell_exec({$cmd})\n";
					# echo $out, "\n\n";
				}
			}
			finally
			{
				if ($lock)
					$lock->unlock();
				chdir($current_dir);
			}
		}
		
		# qvar_dump($lock_path);
		# die;
	}
	
	public static function Run_Sync_Watching(int $project_id)
	{
		# installing inotify
		{
			# 1. install php8.1-dev && php-pear
			# 2. pecl channel-update pecl.php.net
			# 3. pecl install inotify
		}
		ob_start();
		
		$prjs = \QQuery("Projects.{* WHERE Id=? ORDER BY Name}", [$project_id])->Projects;
		$project = $prjs[0] ?? null;
		# file_put_contents("test_2.txt", $prjs->Id . "\n" . date("Y-m-d H:i:s") . "\n");
		$lock_path = getcwd()."/temp/prj_sync/{$project->Id}_sync_watch.lock";
		$lock = null;
		
		$watch_path = getcwd()."/temp/prj_sync/{$project->Id}/";
		
		# /home/alex/public_html/omi-frame/dev-kit
		
		$watch_mask = IN_MODIFY | IN_ATTRIB | IN_CREATE | IN_DELETE | IN_DELETE_SELF;
		
		# if no changes for 60+ mins terminate
		# if more than 8 hours terminate
		$inotify = inotify_init();
		$q = static::inotify_add_watch_recursive($inotify, $watch_path, $watch_mask);
		var_dump($q);
		
		$out = ob_get_clean();
		file_put_contents("/mnt/d/Work_2020/MY-DEV/omi-2023/omi-frame/watcher_test.txt", date("H:i:s.u").": Run_Sync_Watching | ".getcwd()." | ". json_encode(function_exists('inotify_init'))."\n", FILE_APPEND);
		file_put_contents("/mnt/d/Work_2020/MY-DEV/omi-2023/omi-frame/watcher_test.txt", "OUT: {$out}\n\n", FILE_APPEND);
	}
	
	protected static function inotify_add_watch_recursive($inotify, string $path, int $mask, callable $filter = null, array &$ret = null)
	{
		if ($ret === null)
			$ret = [];

		$rc = inotify_add_watch($inotify, $path, $mask);
		$ret[$rc] = $path;
		$q = glob($path . '/*', GLOB_ONLYDIR);
		
		foreach ($q as $subdir)
		{
			$bn = basename($subdir);
			if (($bn === '~gens') /* || ($bn === 'temp')*/ || ($bn === '.git'))
				continue;
			
			static::inotify_add_watch_recursive($inotify, $subdir, $mask, $filter, $ret);
		}
		
		# qvar_dump($q);
		# die;
		# $t1 = microtime(true);
		# echo "inotify_add_watch_recursive: " , ($t1 - $t0) , "\n\n";
		
		return $ret;
		/*
		return $q;
		$ids = [inotify_add_watch($inotify, $path, $mask)];
		if (is_dir($path)) {
			foreach (glob($path . '/*', GLOB_ONLYDIR) as $subdir) {
				$ids[] = inotify_add_watch($inotify, $subdir, $mask);
			}
		}
		return $ids;
		*/
	}
}

