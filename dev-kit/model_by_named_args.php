<?php

class Teeeesstt
{
	public $a;
	public $b;
	public $c;
	public $d;
	public $e;
	public $f;

	public static function load(string $a = null, string $b = null, string $c = null, string $d = null, string $e = null, string $f = null)
	{
		# now ... there are a lot of ways to continue ...
		echo "<pre>";
		var_dump(func_get_args());
		echo "</pre>";
	}

}

Teeeesstt::load(c: 'cccc', f: 'fffff', a: 'aaaaaa', e: 'c: wwoo');

