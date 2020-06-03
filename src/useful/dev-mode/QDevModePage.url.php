<urls>
	<prefix><?= "~dev" ?></prefix>
	<?php
	
		if (!QAutoload::GetDevelopmentMode())
			throw new Exception("The development panel only works in development mode");
		
		if ($url && ($url->current() === "~dev"))
			$url->next();
	
	?>
	<index><load><?php

		$this->addControl(($this->content = new QDevModeClassesCtrl()), "content");
		return true;

	?></load></index>
	<url tag="classes">
		<get translate="classes" />
		<load><?php
			$this->addControl(($this->content = new QDevModeClassesCtrl()), "content");
		?></load>
		<url tag="classitem">
			<get param.0="class"><?= urlencode($class) ?></get>
			<test><?= $url->current() ?></test>
			<load><?php
				// var_dump($testResult);
				$this->content->showClass = $testResult;
			
			?></load>
		</url>
		<unload><?php
			return true;
		?></unload>
	</url>
	<url tag="data-model">
		<get translate="data-model" />
		<load><?php
			$this->addControl(($this->content = new QDevModeModelCtrl()), "content");
		?></load>
		<url tag="modelitem">
			<get param.0="class"><?= urlencode($class) ?></get>
			<test><?= $url->current() ?></test>
			<load><?php
				// var_dump($testResult);
				$this->content->showClass = $testResult;
			
			?></load>
		</url>
		<unload><?php
			return true;
		?></unload>
	</url>
	<url tag="admin">
		<get translate="admin" />
		<load><?php
			$this->addControl(($this->content = new QDevModeAdmin()), "content");
		?></load>
		<url tag="adminitem">
			<get param.0="class"><?= urlencode($class) ?></get>
			<test><?= $url->current() ?></test>
			<load><?php
				
				$property_name = $testResult;
				$this->content->showProperty = $property_name;
				$this->content->initProperty($property_name);
				
				$prop_grid_class = QDevModeAdmin::GetClassNameForProperty($property_name);
				if (class_exists($prop_grid_class))
				{
					$this->content->content = new $prop_grid_class();
					$this->content->addControl($this->content->content);
					$url->next();
					$this->content->content->loadFromUrl($url, $this);
				}
				
				return $this;
			
			?></load>
		</url>
		<unload><?php
			return true;
		?></unload>
	</url>
	<url tag="generate-binds">
		<get translate="generate-binds" />
		<load><?php
			$this->addControl(($this->content = new QDevModeBindsCtrl()), "content");
		?></load>
		<url tag="binditem">
			<get param.0="class"><?= urlencode($class) ?></get>
			<test><?= $url->current() ?></test>
			<load><?php
				// var_dump($testResult);
				$this->content->showClass = $testResult;
			
			?></load>
		</url>
		<unload><?php
			return true;
		?></unload>
	</url>
	<url tag="generated">
		<get translate="generated" />
		<load><?php
			$this->addControl(($this->content = new QDevModeGeneratedCtrl()), "content");
		?></load>
		<url tag="generateditem">
			<get param.0="folder"><?= "?folder=".urlencode($folder) ?></get>
			<test><?= (($folder = $_GET["folder"]) && is_dir($folder)) ? $folder : false ?></test>
			<load><?php
				$this->content->watchFolder = $testResult;
			
			?></load>
		</url>
		<unload><?php
			return true;
		?></unload>
	</url>
	<url tag="api-gen">
		<get translate="api-gen" />
		<load><?php
			$this->addControl(($this->content = new QDevModeApiGenCtrl()), "content");
		?></load>
		<url tag="apigenitem">
			<get param.0="class"><?= urlencode($class) ?></get>
			<test><?= $url->current() ?></test>
			<load><?php
				// var_dump($testResult);
				$this->content->showClass = $testResult;
			
			?></load>
		</url>
		<unload><?php
			return true;
		?></unload>
	</url>
	<url tag="security_model">
		<get translate="security_model" />
		<load><?php
			$this->addControl(($this->content = new QDevModeSecurity_Model_Ctrl()), "content");
		?></load>
		<url tag="security_model_item">
			<get param.0="class"><?= urlencode($class) ?></get>
			<test><?= $url->current() ?></test>
			<load><?php
				// var_dump($testResult);
				$this->content->showClass = $testResult;
			
			?></load>
		</url>
		<unload><?php
			return true;
		?></unload>
	</url>
	<url tag="security_type">
		<get translate="security_type" />
		<load><?php
			$this->addControl(($this->content = new QDevModeSecurity_Type_Ctrl()), "content");
		?></load>
		<url tag="security_type_item">
			<get param.0="class"><?= urlencode($class) ?></get>
			<test><?= $url->current() ?></test>
			<load><?php
				// var_dump($testResult);
				$this->content->showClass = $testResult;
			
			?></load>
		</url>
		<unload><?php
			return true;
		?></unload>
	</url>
	<url tag="url-controller">
		<get translate="url-controller" />
		<load><?php
			return true;
		?></load>
	</url>
	
	<?php
	
		$this->init(true);
		$this->render();
	?>
</urls>