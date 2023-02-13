<?php

final class Q_Code_Review
{
	public static function Review(callable $callback, array $extensions = ['php' => true, 'tpl' => true], bool $skip_backend = true)
	{
		list($files) = \QAutoload::Get_Files_State();
		
		$skip_layers = [];
		if ($skip_backend && defined('Q_GENERATED_VIEW_FOLDER_TAG') && Q_GENERATED_VIEW_FOLDER_TAG && 
				($skip = ($wf = \QAutoload::GetWatchFoldersByTags()) ? $wf[Q_GENERATED_VIEW_FOLDER_TAG] : null))
		{
			$skip_layers[$skip] = true;
		}
		
		foreach ($files ?: [] as $layer => $file_list)
		{
			if (isset($skip_layers[$layer]))
				continue;
			
			foreach ($file_list ?: [] as $rel_file => $mtime)
			{
				$ext = (($p = strrpos($rel_file, '.')) !== false) ? substr($rel_file, $p + 1) : null;
				if (empty($extensions) || isset($extensions[$ext]))
					$callback($layer . $rel_file, $layer, $ext, $mtime);
			}
		}
	}
	
	public static function Review_SQL_Injection(string $write_path)
	{
		throw new \Exception('Not finished.');
		
		echo "<pre>";
		
		if (!is_dir($write_path))
			qmkdir($write_path);
		if (!is_dir($write_path))
		{
			echo "Unable to create write dir.";
			return;
		}
		$write_path = realpath($write_path)."/";
		if (!defined('Q_CODE_DIR'))
		{
			echo "We need Q_CODE_DIR defined.";
			return;
		}
		
		static::Review(function (string $full_path, string $layer, string $ext, int $mtime) use ($write_path) {
			
			# /home/alex/voip-fuse/omi-frame/src/
			# skip frame atm
			if (substr($full_path, 0, strlen('/home/alex/voip-fuse/omi-frame/src/')) === '/home/alex/voip-fuse/omi-frame/src/')
				return;

			$c = file_get_contents($full_path);
			if ($c === false)
			{
				echo "Failed to read: {$full_path}\n";
				return;
			}
			
			$matches_count = preg_match_all("/\\b\\\\?QQuery\\s*\\(/uis", $c);
			if (!$matches_count)
			{
				# there is no need, no reset inside
				return;
			}
			
			$found_toks_count = 0;
			$all_found = [];
			$toks = token_get_all($c);
			$toks_count = count($toks);
			
			foreach ($toks as $t)
			{
				if (is_array($t) && ($t[0] === T_INLINE_HTML))
				{
					$matches = null;
					$rc_m = preg_match_all("/\\b\\\\?QQuery\\s*\\(/uis", $t[1], $matches);
					if ($rc_m > 0)
					{
						echo "Inline QQuery @{$full_path} - line{$t[2]}\n";
					}
				}
			}
			
			ob_start();
			static::Find_Token_Pattern($toks, [
					['mandatory', T_STRING, 'QQuery', true],
					# ['optional', T_WHITESPACE],
					['mandatory', null, '('],
				], function (array $tokens_found, array &$tokens, int $first_match_pos) use ($toks_count, &$found_toks_count, &$all_found)
				{
					# extract first argument || while inside first argument ... 
					static::Test_SQL_Injection($tokens, $first_match_pos, $first_match_pos + count($tokens_found), $toks_count);
					
					# qvar_dumpk($tokens_found);
					# $all_found[] = $tokens_found;
					# $tokens[$first_match_pos] = [T_STRING, 'q_reset', $tokens_found[0][2]];
					
					$found_toks_count++;
				});
			
			$out = ob_get_clean();
			if (strlen($out) > 0)
			{
				echo "@{$full_path}\n";
				echo $out;
				exit;
			}
			else
				echo "@{$full_path} | OK.\n";
			
		});
		
		echo "</pre>";
	}
	
	public static function Test_SQL_Injection(array $tokens, int $first_match_pos, int $arg_start, int $toks_count)
	{
		throw new \Exception('Not finished.');
		
		# $arg_start
		$bracket_index = 0;
		$bag = [];
		$has_issues = false;
		for ($i = $arg_start; $i < $toks_count; $i++)
		{
			$c_tok = $tokens[$i];
			if (is_array($c_tok))
			{
				if (($c_tok[0] === T_CONSTANT_ENCAPSED_STRING) || ($c_tok[0] === T_WHITESPACE) || ($c_tok[0] === T_COMMENT) || ($c_tok[0] === T_DOC_COMMENT))
				{
					# we are ok, this is what it should be
				}
				else
				{
					# echo token_name($c_tok[0]) . ' - ' . $c_tok[1], "\n";
					$has_issues = true;
				}
			}
			else
			{
				if ((($c_tok === ',') || ($c_tok === ')')) && ($bracket_index === 0))
					break; # we are done
				
				if ($c_tok === '(')
				{
					$bracket_index++;
				}
				else if ($c_tok === ')')
				{
					$bracket_index--;
				}
				else if ($c_tok === '.')
				{
					# ok, no danger yet
				}
				else
				{
					$has_issues = true;
				}
			}
			
			$bag[] = $c_tok;
		}
		
		if ($has_issues)
		{
			var_dump($bag);
		}
	}
	
	public static function Review_Array_Reset(string $write_path)
	{
		echo "<pre>";
		
		qvar_dump(Q_CODE_MULTI_INSTANCE_FOLDER);
		die;
		
		if (!is_dir($write_path))
			qmkdir($write_path);
		if (!is_dir($write_path))
		{
			echo "Unable to create write dir.";
			return;
		}
		$write_path = realpath($write_path)."/";
		if (!defined('Q_CODE_DIR'))
		{
			echo "We need Q_CODE_DIR defined.";
			return;
		}
		
		static::Review(function (string $full_path, string $layer, string $ext, int $mtime) use ($write_path) {
			
			$c = file_get_contents($full_path);
			if ($c === false)
			{
				echo "Failed to read: {$full_path}\n";
				return;
			}
			
			$matches_count = preg_match_all("/\\breset\\s*\\(/uis", $c);
			if (!$matches_count)
			{
				# there is no need, no reset inside
				return;
			}
			
			$found_toks_count = 0;
			$all_found = [];
			$toks = token_get_all($c);
			
			foreach ($toks as $t)
			{
				if (is_array($t) && ($t[0] === T_INLINE_HTML))
				{
					$matches = null;
					$rc_m = preg_match_all("/\\breset\\s*\\(/uis", $t[1], $matches);
					if ($rc_m > 0)
					{
						echo "Inline reset @{$full_path} - line{$t[2]}\n";
					}
				}
			}
			
			static::Find_Token_Pattern($toks, [
					['mandatory', T_STRING, 'reset', true],
					# ['optional', T_WHITESPACE],
					['mandatory', null, '('],
				], function (array $tokens_found, array &$tokens, int $first_match_pos) use (&$found_toks_count, &$all_found)
				{
					# qvar_dumpk($tokens_found);
					# $all_found[] = $tokens_found;
					$tokens[$first_match_pos] = [T_STRING, 'q_reset', $tokens_found[0][2]];

					$found_toks_count++;
				});
			
			if ($found_toks_count > 0)
			{
				if (substr($full_path, 0, strlen(Q_CODE_DIR)) !== Q_CODE_DIR)
				{
					echo "File not in Q_CODE_DIR: {$full_path} | ".Q_CODE_DIR, "\n";
					return;
				}
				$write_at = $write_path . substr($full_path, strlen(Q_CODE_DIR));
				$write_at_dir = dirname($write_at);
				if (!is_dir($write_at_dir))
				{
					$rc = qmkdir($write_at_dir);
					if (!$rc)
					{
						echo "Unable to create dir: {$write_at_dir}\n";
						return;
					}
				}
				file_put_contents($write_at, static::Tokens_To_String($toks));
				# echo "We write to: {$write_at}\n";
			}
			
			# if ($found_toks_count !== $matches_count)
			{
				# qvar_dump($found_toks_count, $matches_count, $all_found);
				# echo htmlentities(static::Tokens_To_String($toks));
				# echo htmlentities($c);
			}
		});
		
		echo "</pre>";
	}
	
	public static function Tokens_To_String(array $tokens)
	{
		$str = "";
		foreach ($tokens as $tok)
			$str .= is_string($tok) ? $tok : $tok[1];
		return $str;
	}
	
	public static function Find_Token_Pattern(array &$tokens, array $pattern, callable $callback, bool $ignore_whitespace = true)
	{
		# @TODO - directive not implemented atm as we would need a multiple match system
		# @TODO - improve in the future to be able to find multiple matches on same data (multiple matches lookup)
		#		this means ... as we search we can start a new match set without ending the previous one
		#		right now it will only do it linear
		
		$pattern_pos = 0;
		$count_patt = count($pattern);
		$first_match_pos = null;
		
		foreach ($tokens as $pos => $tok)
		{
			if ($ignore_whitespace && is_array($tok) && ($tok[0] === T_WHITESPACE))
				continue;
			
			list ($directive, $type, $str, $ignore_case) = $pattern[$pattern_pos];
			$match = is_string($tok) ? 
						(($type === null) && (($str === null) || ($ignore_case ? (strtolower($str) === strtolower($tok)) : ($str === $tok)))) : 
						(($type === $tok[0]) && (($str === null) || ($ignore_case ? (strtolower($str) === strtolower($tok[1])) : ($str === $tok[1]))));
			
			if ($match)
			{
				$pattern_pos++;
				if ($pattern_pos === $count_patt)
				{
					# we have a match
					$tokens_found = array_slice($tokens, $first_match_pos ?? $pos, $pattern_pos, true); # true to preserve keys
					$callback($tokens_found, $tokens, $first_match_pos);
					
					# reset
					$first_match_pos = null;
					$pattern_pos = 0;
				}
				else if ($first_match_pos === null)
					$first_match_pos = $pos;
			}
		}
	}
}

