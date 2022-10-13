<?php

trait QWebControl_Methods
{
	
	/**
	 * Renders the begining of the control's response on an AJAX call
	 */
	public function renderAjaxStart()
	{
		if ($this->changed)
			ob_start();
		
		if ($this->children)
		{
			foreach ($this->children as $child)
				$child->callRender();
		}
	}
	
	/**
	 * Renders the end of the control's response on an AJAX call
	 */
	public function renderAjaxEnd()
	{
		$buff = ob_get_clean();
		QWebRequest::SetRenderAjaxResponse($this->getFullId(), $buff);
	}
	
	/**
	 * @api.enable
	 * 
	 * @param type $data
	 * @param QBacktrace $backtrace
	 * @return type
	 */
	public function callRender($data = null, QBacktrace $backtrace = null)
	{
		return $this->{$this->renderMethod}($data, $backtrace);
	}
	
	/**
	 * Calls for the execution of callbacks
	 * 
	 * @param string $filter
	 * @return mixed
	 */
	public function execQB($filter = null)
	{
		throw new \Exception('@deprecated');
		# return execQB($filter, $this);
	}
	
	/**
	 * Calls for the execution of callbacks
	 * 
	 * @param string $filter
	 * @return mixed
	 */
	public function execCallbacks($filter = null)
	{
		throw new \Exception('@deprecated');
		# return execQB($filter, $this);
	}

	/**
	 * Adds a control if it's not present
	 * 
	 * @param QWebControl $control
	 * @param string $name
	 * @return boolean
	 */
	public function addControlIfNotExists(QWebControl $control, $name)
	{
		if (!$this->children[$name])
		{
			$this->addControl($control, $name);
			return true;
		}
		else 
			return false;
	}
	
	/**
	 * Adds a control withing this one
	 * 
	 * @param QViewBase $control
	 * @param string $name
	 */
	public function addControl(QViewBase $control, $name = null)
	{
		if ($this->children === null)
			$this->children = new QModelArray();
		$this->children[$name ?: null] = $control;
		$control->name = $name;
		$control->setParent($this);
	}
	
	/**
	 * Gets a URL for the specified tag
	 * 
	 * @param string $tag
	 * @param array $url
	 * 
	 * @return string
	 */
	public function getUrlForTag($tag = '', $_arg0 = NULL, $_arg1 = NULL, $_arg2 = NULL, $_arg3 = NULL, $_arg4 = NULL, $_arg5 = NULL, $_arg6 = NULL, $_arg7 = NULL, 
		$_arg8 = NULL, $_arg9 = NULL, $_arg10 = NULL, $_arg11 = NULL, $_arg12 = NULL, $_arg13 = NULL, $_arg14 = NULL, $_arg15 = NULL)
	{
		return "";
	}
	/**
	 * Does execution logic based on a URL.
	 * On the first call the instance can be null. Later the code decides.
	 * 
	 * @param QUrl $url
	 * @param QIUrlController|string $parent
	 */
	public function loadFromUrl(QUrl $url, $parent = null)
	{
		if (!$this->children)
			return;
		
		foreach ($this->children as $child)
		{
			if (($response = $child->loadFromUrl($url, $this)))
				return $response;
		}
	}
	
	/**
	 * Adds a control to this one, then calls init & render on it
	 * You may cancel init or add via the parameters
	 * 
	 * @param QWebControl $ctrl
	 * @param boolean $init
	 * @param boolean $add_control
	 */
	public function renderControl(QWebControl $ctrl, $init = null, $add_control = null)
	{
		if ((($add_control !== false) && $ctrl->name && (!$this->children[$ctrl->name])) || ($add_control === true))
			$this->addControl($ctrl);
		if (($init !== false) || ($init === true))
			$ctrl->init();
		$ctrl->render();
	}
	
	/**
	 * Calls for the render of the view
	 * 
	 * @param mixed $data
	 * @param QBacktrace $backtrace
	 */
	public function renderInner($data = null, QBacktrace $backtrace = null)
	{
		if ($this->renderMethod && ($this->renderMethod !== "render") && ($this->renderMethod !== "renderInner"))
		{
			if (is_string($this->renderMethod))
				$this->{$this->renderMethod}();
			else if (is_callable($this->renderMethod))
			{
				$methd = $this->renderMethod;
				$methd();
			}
		}
		else if ($this->children)
		{
			$next_bk = $backtrace ? new QBacktrace($backtrace, array($this, $data)) : null;
			foreach ($this->children as $child)
				$child->render($data, $next_bk);
		}
	}

	/**
	 * Short alias for getUrlForTag
	 * 
	 * @return string
	 */
	public function url($tag = "", $_arg0 = null, $_arg1 = null, $_arg2 = null, $_arg3 = null, $_arg4 = null, $_arg5 = null, $_arg6 = null, $_arg7 = null, $_arg8 = null, $_arg9 = null, $_arg10 = null, $_arg11 = null, $_arg12 = null, $_arg13 = null, $_arg14 = null, $_arg15 = null)
	{
		return $this->getUrlForTag($tag, $_arg0, $_arg1, $_arg2, $_arg3, $_arg4, $_arg5, $_arg6, $_arg7, $_arg8, $_arg9, $_arg10, $_arg11, $_arg12, $_arg13, $_arg14, $_arg15);
	}
	
	/**
	 * Extra render on page to resolve resources 
	 * @todo Rename this method renderCallbacks -> renderResources
	 */
	public function renderCallbacks(bool $skip_minify = false)
	{
		$dev_mode = QAutoload::GetDevelopmentMode();

		
		static::AddCssLastResources();
	
		if (self::$IncludeCss)
		{
			foreach (self::$IncludeCss as $css)
			{
				if (empty($css))
					continue;

					if (is_scalar($css))
						$css = [$css];
					foreach ($css as $css_path) :
						if (empty($css_path))
							continue;
					// $use_css = ((!$dev_mode) && file_exists(substr($css, -3)."min.css")) ? substr($css, -3)."min.css" : $css;
				?>		<link type="text/css" rel="stylesheet" href="<?= static::GetPreventCacheResourceSource($css_path)  ?>" />
<?php	endforeach;
			}
		}
		
		static::AddJsLastResources();

		if (self::$IncludeJs)
		{
			$included_already = [];
			foreach (self::$IncludeJs as $js)
			{
				if (empty($js))
					continue;

					if (is_scalar($js))
						$js = [$js => $js];
					
					foreach ($js as $js_path) :
						if (empty($js_path) || isset($included_already[$js_path]))
							continue;
						$included_already[$js_path] = $js_path;
					// $use_js = ((!$dev_mode) && file_exists(substr($js, -2)."min.js")) ? substr($js, -2)."min.js" : $css;
				?>		<script type="text/javascript" src="<?= static::GetPreventCacheResourceSource($js_path) ?>"></script>
<?php endforeach;
			}
		}
		
		if (\QAutoload::In_Debug_Mode())
		{
			$azz_data = \QAutoload::Debug_Get_Data();
			if ($azz_data)
			{
				?><div id='qb-dump-panel' class=' ' style='z-index: 100000; max-width: 90%; position: fixed; top: 0px; right: 0px; display: block; overflow: scroll; height: 100%; background-color: white; border: 1px solid gray; padding: 10px; margin: 0px;' > 
					<a onclick='jQuery(this).parent().remove();' style='position: absolute; right: 5px; top: 3px; cursor: pointer; color: red;'>x</a><?php
				qvar_dump($azz_data);
				echo '</div>';
			}
		}
		
		\QWebRequest::RenderBeforeBodyEnds();
	}

	public function setRenderMethod($renderMethod)
	{
		$this->renderMethod = $renderMethod;
	}
}
