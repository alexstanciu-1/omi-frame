<?php

/**
 * Just the basics for now
 *		If inside an attribute - make sure it's escaped  | error if it fails for any reason
 *		If in a Inner TEXT/HTML - sanitize | error if fails
 * 
 * IF Code inside a SCRIPT tag ... throw error ... not allowed
 * 
 */

final class Q_Ob_Ctx
{
	private static $flush_ctx;
	private static $started = false;
	
	private $parent;
	
	public static function Code_Starts(string $attr = null, string $inside_tag = null)
	{
		if (static::$flush_ctx === null)
		{
			# start a new one
			static::$flush_ctx = new static;
			# ob_implicit_flush(???);
		}
		
		if (!static::$started)
		{
			static::$started = true;
			ob_start([static::$flush_ctx, 'ob_callback']);
		}
		# file_put_contents("/home/alex/ob_log_starts.txt", "---- START ----\n", FILE_APPEND);
	}
	
	public static function Code_Ends(string $attr = null, string $inside_tag = null)
	{
		#if (static::$flush_ctx === null)
		#	throw new \Exception('Code_Ends called outside output capture.');
		
		if (static::$started)
		{
			ob_end_flush();
			static::$started = false;
		}
	}
	
	public function ob_callback(string $buffer, int $phase = null)
	{
		if ((strlen($buffer) > 0))
		{
			# we need our internal parser
			
			# file_put_contents("/home/alex/ob_log_xml.txt", "from: ". json_encode($buffer, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_LINE_TERMINATORS)
			#			. "\n---------------------------\n" .json_encode($sxe)."\n====================\n", FILE_APPEND);
			
			file_put_contents("/home/alex/ob_log.txt", "from: ". json_encode($buffer, JSON_PRETTY_PRINT | 
							JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_LINE_TERMINATORS)
						. "\n---------------------------\n" , FILE_APPEND);
			
			# we need to make sure that it is not cleaned by anyone else
			# unsafe output ... needs cleanup
			# file_put_contents("/home/alex/ob_log.txt", json_encode($buffer)."\n", FILE_APPEND);
		}
		
		return $buffer;
	}
	
	const OB_PHASES = [
			PHP_OUTPUT_HANDLER_START => 'PHP_OUTPUT_HANDLER_START',
			PHP_OUTPUT_HANDLER_WRITE => 'PHP_OUTPUT_HANDLER_WRITE',
			PHP_OUTPUT_HANDLER_FLUSH => 'PHP_OUTPUT_HANDLER_FLUSH',
			PHP_OUTPUT_HANDLER_CLEAN => 'PHP_OUTPUT_HANDLER_CLEAN',
			PHP_OUTPUT_HANDLER_FINAL => 'PHP_OUTPUT_HANDLER_FINAL',
		];
	
	private function test_ob_func()
	{
		echo "<pre>";
	
		$ob_phases = [
			PHP_OUTPUT_HANDLER_START => 'PHP_OUTPUT_HANDLER_START',
			PHP_OUTPUT_HANDLER_WRITE => 'PHP_OUTPUT_HANDLER_WRITE',
			PHP_OUTPUT_HANDLER_FLUSH => 'PHP_OUTPUT_HANDLER_FLUSH',
			PHP_OUTPUT_HANDLER_CLEAN => 'PHP_OUTPUT_HANDLER_CLEAN',
			PHP_OUTPUT_HANDLER_FINAL => 'PHP_OUTPUT_HANDLER_FINAL',
		];

		$log_data = [];

		var_dump($ob_phases);

		ob_start(function (string $buffer, int $phase = null) use ($ob_phases, &$log_data) {

			# we don't return any data while we are under control, we throw an error if END is requested
			# outside our call

			$log_data[] = $out = $buffer . " | {$phase} | " . $ob_phases[$phase];

			$out = json_encode($out);

			if ($phase & PHP_OUTPUT_HANDLER_CLEAN)
				return "i refuse to clean | {$out}\n";
			else if ($phase & PHP_OUTPUT_HANDLER_FLUSH)
				return "i refuse to flush | {$out}\n";
			else if ($phase & PHP_OUTPUT_HANDLER_END)
			{
				# @TODO - throw exception if not our handler
				return "i refuse to end | {$out}\n";
			}
			else if ($phase & PHP_OUTPUT_HANDLER_WRITE)
				return "i refuse to write | {$out}\n";
			else
				return $out . "\n";
		});

		echo "ok\n";

		ob_flush();

		echo "nested OB\n";

		/*
		PHP_OUTPUT_HANDLER_CLEANABLE 	ob_clean(), ob_end_clean(), and ob_get_clean().
		PHP_OUTPUT_HANDLER_FLUSHABLE 	ob_end_flush(), ob_flush(), and ob_get_flush().
		PHP_OUTPUT_HANDLER_REMOVABLE 	ob_end_clean(), ob_end_flush(), and ob_get_flush(). 
		 */

		ob_end_clean();

		echo "there is more\n";

		var_dump($log_data);

		exit;
	}
}