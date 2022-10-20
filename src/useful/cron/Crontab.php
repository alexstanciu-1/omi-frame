<?php

namespace Omi\Linux;

class Crontab
{
	public $content;
	protected $data;
	protected static $CommandIdentifier = "\$command_idf:";
	
	public function __construct($content = null)
	{
		$this->content = $content;
		if ($this->content !== null)
			$this->doParse();
	}
	
	protected function doParse()
	{
		$this->content = static::CleanContents($this->content);
		// $this->content
		$chunks = preg_split('/$\R?^/m', $this->content);
		
		$this->data = [];
		$pos = 0;
		$num_pos = 0;
		
		foreach ($chunks as $chunk)
		{
			if ($chunk[0] === "#")
			{
				if (($c_pos = strpos($chunk, static::$CommandIdentifier)) !== false)
				{
					$pos = substr($chunk, $c_pos + strlen(static::$CommandIdentifier));
				}
				
				$this->data[$pos][] = $chunk;
			}
			else if (substr($chunk, 0, strlen("MAILTO=")) === "MAILTO=")
			{
				$this->data["MAILTO="][] = $chunk;
				// increment position
				$num_pos++;
				$pos = $num_pos;
			}
			else 
			{
				$this->data[$pos][] = $chunk;
				$trimmed = trim($chunk);
				
				// a backslash at the end of a command means that the command will continue on the next line 
				if ($trimmed && (substr($trimmed, -1, 1) !== "\\"))
				{
					// we have a command
					// increment position
					$num_pos++;
					$pos = $num_pos;
				}
			}
		}
	}
	
	public function replaceFromContent($crons_content, $filter = null)
	{
		$crons = new Crontab($crons_content);
		return $this->replaceFrom($crons, $filter);
	}
	
	public function replaceFrom(Crontab $crons, $filter = null)
	{
		if (isset($crons->data["MAILTO="]))
		{
			// ensure, overwrite MAILTO
			$this->data["MAILTO="] = $crons->data["MAILTO="];
		}
		
		$ticked = [];
		
		foreach ($crons->data as $crons_k => $crons_lines)
		{
			$ticked[$crons_k] = $crons_k;
			if (is_int($crons_k) || ($filter && (stripos($crons_k, $filter) === false)))
				continue;
			
			$this->data[$crons_k] = $crons_lines;
		}
		
		$delete_entries = [];
		
		foreach ($this->data as $data_k => $data_lines)
		{
			if (is_int($data_k) || isset($ticked[$data_k]) || ($filter && (stripos($data_k, $filter) === false)))
				continue;
			
			$delete_entries[$data_k] = $data_k;
		}
		
		foreach ($delete_entries as $delete_k)
			unset($this->data[$delete_k]);
	}
	
	public function getContent()
	{
		if (!is_array($this->data))
			return false;
		
		$content = "";
		foreach ($this->data as $data)
		{
			foreach ($data as $line)
				$content .= $line.PHP_EOL;
		}
		return rtrim($content).PHP_EOL;
	}
	
	public function save()
	{
		$content = $this->getContent();
		$fname = getcwd().'/tmp_crontab_'.uniqid().'.txt';
		file_put_contents($fname, $content);
		
		$arr = [];
		$return_code = null;
		exec('crontab '.$fname, $arr, $return_code);
		unlink($fname);

		return [$arr, $return_code];
	}
	
	public static function ReadAll()
	{
		return shell_exec('crontab -l');
	}
	
	public static function WriteAll($from_file)
	{
		if (file_exists($from_file))
		{
			$arr = [];
			$return_code = null;
			exec('crontab '.$from_file, $arr, $return_code);
			return [$arr, $return_code];
		}
		else
			return false;
	}
	
	public static function CleanContents($content)
	{
		return preg_replace("/\\n\\s+|\\n+/us", "\n", $content).PHP_EOL;
	}
	
	public static function ParseContent($content)
	{
		return new Crontab($content);
	}
	
	public static function Parse()
	{
		$content = static::ReadAll();
		return static::ParseContent($content);
	}
	
	public static function ExecuteCronRequest($argv, $get)
	{
		$arguments = null;
		if ($get)
		{
			// web mode
			$arguments = $get;
		}
		else if ($argv && isset($argv[1]))
		{
			// cli mode
			parse_str($argv[1], $arguments);
		}
		else
			return false;
		
		$class = $arguments["class"];
		$method = $arguments["method"];
		$params = $arguments["params"];
		if (!is_array($params))
			$params = [];

		if (!class_exists($class))
		{
			if (\QAutoload::GetDevelopmentMode())
				throw new \Exception("Class {$class} does not exist.");
			return false;
		}
		if (!method_exists($class, $method))
		{
			if (\QAutoload::GetDevelopmentMode())
				throw new \Exception("Method {$method} does not exist on class {$class}.");
			return false;
		}
		
		$result = call_user_func_array([$class, $method], $params);
		return ["result" => $result];
	}
	
	public static function EncodeEntry($schedule, $class, $method, $params = null, $tag = null, $comment = null, $filter = null, $exec = null, $script_rel_path = null, $script_full_path = null)
	{
		if (!$script_rel_path)
			$script_rel_path = "crons.php";

		$exec_web = null;
		$exec_php = null;
		
		if (!$exec)
		{
			
			$cron_params = ["class" => $class, "method" => $method, "params" => $params];
			
			$exec_php = "php " . ($script_full_path ? $script_full_path : rtrim(getcwd(), "/").($script_rel_path ? "/".$script_rel_path : "")) . 
				($cron_params ? " \"".http_build_query($cron_params)."\"" : "")." &> /dev/null";

			// wget -qO- "http://alex.softdev.ro/travel-index/crons.php?provider=Eurosite&method=xyz&param0=&param1=" &> /dev/null

			if ($script_full_path)
			{
				$exec_php = null;
				$get = "";
				if ($cron_params && (count($cron_params) > 0))
				{
					$pos = 0;
					// to be rewritten - if we use http_build_query and we have namespaces crontab cannot be executed
					foreach ($cron_params as $key => $value)
					{
						if (is_array($value))
						{
							if (!empty($value))
							{
								foreach ($value as $k => $_v)
								{
									$get .= (($pos > 0) ? "&" : "").$key."[{$k}]=".$_v;
									$pos++;
								}
							}
						}
						else
							$get .= (($pos > 0) ? "&" : "").$key."=".$value;
						$pos++;
					}
				}
				$exec_web = "wget -qO- \"".$script_full_path.((strlen($get) > 0) ? "?". $get : "")."\" &> /dev/null";
				$exec = $exec_web;
			}
			else if (isset($_SERVER["HTTP_HOST"]))
			{
				// 'DOCUMENT_ROOT' => string '/var/www/~users/alex/'
				$sub_path = trim(dirname($_SERVER["PHP_SELF"]), "/");
				$exec_web = "wget -qO- \"".$_SERVER['REQUEST_SCHEME']."://".$_SERVER["HTTP_HOST"].($sub_path ? "/".$sub_path : "").($script_rel_path ? "/".$script_rel_path : "").
						($cron_params ? "?".http_build_query($cron_params) : "").
							"\" &> /dev/null";
				$exec = $exec_web;
			}
			// php /var/www/~users/alex/travel-index/crons.php "provider=Eurosite&method=xyz&params[0]=alex&params[1]=bubu" &> /dev/null
			else
			{
				$exec = $exec_php;
			}
		}

		if (!$tag)
			$tag = $class."::".$method;

		if (!$filter)
		{
			$filter = ($script_full_path ? $script_full_path : getcwd().($script_rel_path ? "/".$script_rel_path : ""))." - ".$tag;
		}

		$comment_txt = $comment ? "# ".preg_replace("/\\n/us", "\n# ", trim($comment)) : null;

		$cront_text = "# ".static::$CommandIdentifier.$filter.PHP_EOL.($comment_txt ? $comment_txt.PHP_EOL : "").
					(($exec_web && $exec_php) ? "# optional via cli mode: ".preg_replace("/\\n/us", "\n# ", trim($exec_php)).PHP_EOL : "").
				$schedule." ".$exec.PHP_EOL;
		
		return $cront_text;
	}
}