<?php
/**
 * Generates and patches platform standards
 */
trait QCodeSync2_Security
{
	protected function secure_template(\QPHPToken $xml_tokens, array $header_inf, \QPHPToken $parent = null, int $parent_pos = 0, int $depth = 0)
	{
		# @TODO -  IF Code inside a SCRIPT tag ... throw error ... not allowed | warn atm !
		if (!isset($xml_tokens->children))
			return [$xml_tokens];
		
		if ($depth === 0)
		{
			# we need to flag an end to the code by default
			array_unshift($xml_tokens->children, "<?php \Q_Ob_Ctx::Code_Ends(); ?>");
		}
		
		$last_open_tag_with_echo = false;
		
		foreach ($xml_tokens->children as $pos => &$tok)
		{
			$is_arr = is_array($tok);
			if ($is_arr)
			{
				if ($tok[0] === T_OPEN_TAG_WITH_ECHO)
				{
					$last_open_tag_with_echo = true;
				}
				else if ($tok[0] === T_OPEN_TAG)
				{
					$last_open_tag_with_echo = false;
					
					if (($pos === 0) && ($xml_tokens instanceof \QPHPTokenCode))
					{
						$tok[1] = $tok[1] . ' \Q_Ob_Ctx::Code_Starts(); ';
					}
					else
					{
						# @TODO - we need to know when we are inside tags or outside ... so we know how we secure
						$tok[1] = $tok[1] . ' \Q_Ob_Ctx::Code_Starts(); ';
					}
				}
				else if ($tok[0] === T_CLOSE_TAG)
				{
					if ($last_open_tag_with_echo)
					{
						# ok atm
					}
					else
					{
						$tok[1] = ' ; \Q_Ob_Ctx::Code_Ends(); ' . $tok[1];
					}
					
					$last_open_tag_with_echo = false;
				}
			}
			else if (is_object($tok))
			{
				$this->secure_template($tok, $header_inf, $xml_tokens, $pos, $depth + 1);
			}
		}
		
		if (false && ($depth === 0))
		{
			$xml_tokens_str = $xml_tokens->toString(false, true);
			echo "<pre>";
			echo htmlentities($xml_tokens_str);
			# qvar_dump('$header_inf, $xml_tokens, $gen_info', $header_inf, $xml_tokens, $gen_info);
			die;
		}
		
		return [$xml_tokens];
	}
	
	/*
define("T_QXML_MIN",				999000); // <!-- ... -->
define("T_QXML_MAX",				999999); // <!-- ... -->

define("T_QXML_COMMENT",			999001); // <!-- ... -->
define("T_QXML_TAG_OPEN",			999002); // ex: <
define("T_QXML_TAG_NAME",			999022); // ex: div
define("T_QXML_TAG_SHORT_CLOSE",	999023); // ex: div
define("T_QXML_TAG_CLOSE",			999003); // 
define("T_QXML_TAG_END",			999004); // </div>
define("T_QXML_TEXT",				999005); // text inside XML element
define("T_QXML_ATTR_NAME",			999006);
define("T_QXML_ATTR_VALUE",			999007);
define("T_QXML_ATTR_SPACE",			999011);
define("T_QXML_ATTR_EQUAL",			999012);

define("T_QXML_DOCTYPE",			999090); // <!-- ... -->
	 */
	
}
