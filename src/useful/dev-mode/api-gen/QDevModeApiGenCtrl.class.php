<?php


/**
 * @class.name QDevModeApiGenCtrl
 */
abstract class QDevModeApiGenCtrl_frame_ extends QWebControl
{
	
	
	public $showClass;
	
	/**
	 * @api.enable
	 * 
	 * @param string $class
	 * @param string $method
	 * 
	 * @return type
	 */
	public function proposeMethods($class, $method)
	{
		if (!($class && $method))
			return null;
		
		$php_tok = QPHPToken::ParsePHPFile(QAutoload::GetClassFileName($class));
		$namespace = $php_tok->getNamespace();
		
		// @todo we need the namespace here and we need to apply it
		
		if ($method === "___stdMethods___")
		{
			// standard methods: query, queryFirst, save, saveList, merge, insert, update, delete, delete by id
			// static && non static
			
			/**
			 * @api.enable
			 * 
			 * @param string $class_name some description here
			 *		  filter:	
			 *		  validator:	
			 *	
			 * @param string $class_name
			 * 
			 * @return string
			 *		  filter:	
			 *		  validator: 
			 */
			
			$methods = [
					"Api_Query" => [
							"static" => true,
							"params" => [
								"selector" => [
									"type" => "selector",
									"fixers" => "secureSelector(\$_called_class_, \$_called_class_::GetListingEntity())",
									"validators" => null
								],
								"binds" => [
									// where, group by, order by, limit ... at different levels of depth
									"type" => "array",
									"validators" => null, // "validateArray([""])" // with a lot of rules!
									"validatorsExclusive" => true // @todo ... we will see how we design this 
								]
							],
							"return" => [
								"type" => new QModelAcceptedType("QModelArray", [$class]),
								"validators" => "security(read, \$selector)",
							],
							"body" => "return \$_called_class_::QueryList(\$_called_class_::GetListingQuery(), \$binds);"
						],
				
					"Api_QueryById" => [
							"static" => true,
							"params" => [
								"id" => [
									"type" => "integer",
									"fixers" => null,
									"validators" => null
								],
								"selector" => [
									"type" => "selector",
									"fixers" => "secureSelector(\$_called_class_, \$_called_class_::GetModelEntity())",
									"validators" => null
								]
							],
							"return" => [
								"type" => $class,
								"validators" => "security(read, \$selector)",
							],
							"body" => "return \$_called_class_::QueryById(\$id, \$selector);"
						],
				
					"Api_Save" => [
							"static" => true,
							"params" => [
								"model" => [
									"type" => $class,
									"validators" => "securityApi(\$_called_class_, \"Api_Save\", \$state, \$_called_class_::GetModelEntity())", // if not defined we could use the class
								],
								"selector" => [
									"type" => "selector",
									"fixers" => "secureSelector(\$_called_class_, \$_called_class_::GetModelEntity())",
									"validators" => null
								],
								"state" => [
									"type" => "integer",
									"validators" => "in_array(QModel::TransformCreate, QModel::TransformMerge, QModel::TransformUpdate, QModel::TransformDelete)"
								]
							],
							"return" => [
								"type" => "integer",
								"fixers" => "(int)\$value"
							],
							"body" => "return \$model->save(\$selector, null, \$state);"
						]
				];
			
			// prepare doc comment for them
			foreach ($methods as $name => $data)
			{
				$comment = "/**\n";
				$comment .= " * {$name}\n";
				$comment .= " * @api.enable\n";
				if ($data["params"])
				{
					$comment .= " *\n";
					foreach ($data["params"] as $p_name => $param_data)
					{
						$comment .= " * @param ".QPHPToken::ShortNameForNamespace($param_data["type"], $namespace)." \${$p_name}\n";
						if (($fixer = $param_data["fixers"]))
							$comment .= " *			filter: {$fixer}\n";
						if (($validator = $param_data["validators"]))
							$comment .= " *			validator: {$validator}\n";
					}
				}
				if (($return_data = $data["return"]))
				{
					// var_dump($return_data["type"]);
					$comment .= " *\n";
					$comment .= " * @return ".QPHPToken::ShortNameForNamespace($return_data["type"], $namespace)."\n";
					if (($fixer = $return_data["fixers"]))
							$comment .= " *			filter: {$fixer}\n";
					if (($validator = $return_data["validators"]))
						$comment .= " *			validator: {$validator}\n";
				}
				$comment .= " */\n";
				$methods[$name]["full_comment"] = $comment;
			}
		}
		else
		{
			$methods = [];
			
			// $method => "populate data based on existing information"
			
			/**
			 * @api.enable
			 * 
			 * @param string $class_name some description here
			 *		  filter:	
			 *		  validator:	
			 *	
			 * @param string $class_name
			 * 
			 * @return string
			 *		  filter:	
			 *		  validator: 
			 */
			
		}
		
		// var_dump($methods);
		
		foreach ($methods as $name => $meth_data)
		{
			// create fixers & filters, then validators foreach param and foreach return
			$params = $meth_data["params"];
			$return = $meth_data["return"];
			$inner_body = $meth_data["body"];
			$full_comment = $meth_data["full_comment"];
			
			// $generated_start = "// Generated API Code for: {$name}\n";
			// $generated_end = "// END Generated API Code for: {$name}\n";
			
			$body = "";
			
			$use_called_class = false;
			// you may cancel the creation of the $_called_class_ variable with "defineCalledClass" => false
			if ($meth_data["static"] && ($meth_data["defineCalledClass"] !== false))
			{
				$use_called_class = "\$_called_class_ = get_called_class();\n";
				/*if (empty($body))
					$body = $generated_start;*/
				// $body .= $use_called_class = "\$_called_class_ = get_called_class();\n";
			}
			
			// type, fixers, validators
			if ($params)
			{
				// 1. filter input (fixers)
				foreach ($params as $p_name => $param_data)
				{
					if (($fixer = $param_data["fixers"]))
					{
						/*if (empty($body))
							$body = $generated_start;*/
						$body .= "\${$p_name} = ".QCodeSync::GetFixValStr($fixer, null, "\$".$p_name).";\n";
					}
				}
				// 2. validate input (validators)
				foreach ($params as $p_name => $param_data)
				{
					if (($validator = $param_data["validators"]))
					{
						/*if (empty($body))
							$body = $generated_start;*/
						$body .= "if (! (".QCodeSync::GetValidationStr($validator, null, "\$".$p_name).")\n".
										"\tthrow new Exception(\"Invalid input parameter {$p_name}\");\n";
					}
				}
			}
			
			/*if (!empty($body))
				$body .= $generated_end;*/
			
			// 3. call $inner_body
			// @todo - replace return with \$_return_ = 
			//					if non empty return
			//					here it's a bit delicate, wrap it in a lambada ?
			$args = $params ? "\$".implode(", \$", array_keys($params)) : "";
			$body .= "\$_return_ = \$_callback_({$args}".($use_called_class ? ($args ? ", " : "")."\$_called_class_" : "").");\n";// $inner_body."\n";
			if ($return)
			{
				//$body .= $generated_start;
				// 4. filter return
				if (($return_fixer = $return["fixers"]))
				{
					$body .= "\$return = ".QCodeSync::GetFixValStr($return_fixer, null, "\$_return_").";\n";
				}
				// 5. validate return (validators)
				if (($return_validator = $return["validators"]))
				{
					$body .= "if (! (".QCodeSync::GetValidationStr($return_validator, null, "\$_return_").")\n".
										"\tthrow new Exception(\"Invalid return\");\n";
				}

				$body .= "return \$_return_;\n";
				
				//$body .= rtrim($generated_end);
			}
			
			echo $full_comment."public ".($meth_data["static"] ? "static " : "")."function {$name}({$args})\n".
					"{\n".
					($use_called_class ? "\t".$use_called_class : "").
					"\treturn \$_called_class_::{$name}_wrap_({$args}, function ({$args}".($use_called_class ? (($args ? ", " : "")."\$_called_class_") : "").")\n".
					"\t{\n".
						"\t\t".$inner_body."\n".
					"\t}".($use_called_class ? ", \$_called_class_" : "").");\n".
					"}\n\n";
			
			echo "public ".($meth_data["static"] ? "static " : "")."function {$name}_wrap_({$args}".($args ? ", " : "")."callable \$_callback_".($use_called_class ? ", \$_called_class_" : "").")\n{\n";
			echo "\t".str_replace("\n", "\n\t", rtrim($body))."\n}\n\n";
		}
		
		// return [$class, $method];
	}
	
	/**
	 * @api.enable
	 * 
	 * @param type $arg_1
	 * @param type $arg_2
	 */
	function xxx($arg_1, $arg_2)
	{
		$_called_class_ = get_called_class();
		return $_called_class_::xxx_wrap_($arg_1, $arg_2, function ($arg_1, $arg_2)
		{
			// do what you have to do
		});
	}
	
	function xxx_wrap_($arg_1, $arg_2, callable $callback)
	{
		// do generated xyz
		$_return_ = $callback($arg_1, $arg_2);
		// do generated xyz
		return $_return_;
	}
}

