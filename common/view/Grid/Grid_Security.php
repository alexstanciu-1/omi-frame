<?php

namespace Omi\View;

trait Grid_Security
{
	
	public function allowAdd($data = null, $property = null, $vars_path = null)
	{
		
		return true;
	}
	
	public function allowView($data = null, $property = null, $vars_path = null)
	{
		return true;
	}
	
	public function allowEdit($data = null, $property = null, $vars_path = null)
	{
		/*if (($property === 'SerialNumber')
				|| ($property === 'IP')
			)
			return false;*/
		return true;
	}
	
	public function allowDelete($data = null, $property = null, $vars_path = null)
	{
		return true;
	}
	
	/**
	 * Calls for the init
	 * 
	 * @param boolean $recursive
	 */
	public function init($recursive = true)
	{
        if (file_exists(Q_FRAME_BPATH . "gens/control/Grid/css/skin.css"))
			$this->addCss(\QApp::GetWebPath( Q_FRAME_BPATH . "gens/control/Grid/css/skin.css" ));
		if (file_exists(QGEN_ConfigDirBase."/css/skin.css"))
		{
			$this->addCss(Q_APP_REL . QGEN_ConfigDirBase . "/css/skin.css");
		}
		
		if (file_exists(Q_FRAME_BPATH."gens/control/Grid/css/trumbowyg/ui/trumbowyg.min.css"))
			$this->addCss(\QApp::GetWebPath(Q_FRAME_BPATH."gens/control/Grid/css/trumbowyg/ui/trumbowyg.min.css"));
		if (file_exists(Q_FRAME_BPATH."gens/control/Grid/css/trumbowyg/trumbowyg.min.js"))
			$this->addJs(\QApp::GetWebPath(Q_FRAME_BPATH."gens/control/Grid/css/trumbowyg/trumbowyg.min.js"));
		
		return parent::init($recursive);
	}
	
	public function renderReferenceControlActions($ctrl = null, $selectedItm = null, $show_edit = true, $show_new = true, $show_view = false)
	{
		if (!class_exists($ctrl))
			return;

		
		// $label = ($ctrl == "Omi\VF\View\Customers") ? _L("New") : _L("Enter");
		$label = _L("New");
		?>
		<ul class="buttons-group t-tooltip qc-dropdown-actions css-dropdown-actions">
			<li>
				<a class="mdt-c"><i class="fa fa-cog"></i></a>
				<ul class="dropdown no-border">
					<?php if ($show_edit): ?>
					<li class='qc-ref-edit-wr'<?= $selectedItm ? '' : ' style="display: none;"' ?>>
						<a data-ctrl='<?= $ctrl ?>' class="qc-ref-ctrl-edit-full btn btn-warning btn-border address-controls" title="Edit"><?= _L('Edit') ?></a>
					</li>
					<?php endif; ?>
					<?php if ($show_view): ?>
					<li class='qc-ref-view-wr'<?= $selectedItm ? '' : ' style="display: none;"' ?>>
						<a data-ctrl='<?= $ctrl ?>' class="qc-ref-ctrl-view-full btn btn-warning btn-border address-controls" title="View"><?= _L('View') ?></a>
					</li>
					<?php endif; ?>
					<?php if ($show_new): ?>
					<li class='qc-ref-add-wr'>
						<a data-ctrl='<?= $ctrl ?>' class="btn btn-info btn-border qc-ref-ctrl-new-full address-controls"><?= $label ?></a>
					</li>
					<?php endif; ?>
				</ul>
			</li>
		</ul>
		<?php
	}
	
	
	/**
	 * @api.enable
	 * 
	 * @param string $grid_mode
	 * @param int|null $id
	 * @param array $params
	 */
	public static function RenderPopupForm($grid_mode, $id = null, $params = [])
	{
		$gridCls = get_called_class();
		$grid = new $gridCls();
		
		$grid->grid_mode = $grid_mode;
		$grid->grid_id = $id;
		$grid->grid_params = $params;
		$grid->setupGrid($grid->grid_mode, $grid->grid_id, $grid->grid_params);
		
		if (isset($params["full_data"]))
		{
			// we overwrite ... 
			if (($grid->grid_mode === 'add') || ($grid->grid_mode === 'edit') || ($grid->grid_mode === 'view'))
			{
				$grid->data = $params["full_data"];
				if ($grid->data && ($grid->data instanceof \QIModel) && $grid->data->Id) # ugly fix !
					$grid->data->populate($grid->data::GetModelEntity());
			}
			# ugly fix: the `setupGrid` can not find the object attached to app, and will switch to list mode
			else if ($id && (!$grid->grid_id)) // can be edit/view/delete || # after setup grid
			{
				# we need to fix it back !
				$grid->grid_mode = $grid_mode;
				$grid->grid_id = $id;
				if ((!$grid->data) || qis_array($grid->data)) # ugly fix !
				{
					$grid->data = $params["full_data"];
					if ($grid->data && ($grid->data instanceof \QIModel) && $grid->data->Id) # ugly fix !
						$grid->data->populate($grid->data::GetModelEntity());
				}
				else
				{
					// @TODO merge the data deeper (in the future ?!)
					foreach ($params["full_data"] ?: [] as $k => $v)
						$grid->data->{$k} = $v;
				}
			}
		}
		else if ($id && (!$grid->grid_id) && ($grid->grid_mode !== $grid_mode) && ($grid->grid_mode === 'list'))
		{
			// @TODO - get it directly by id !
		}
		
		if (isset($params['dd_binds']["do_populate"]) && isset($grid->data->Id) && isset($params['from']))
		{
			$populate_sel = qJoinSelectors(\Omi\App::GetEntityForGenerateForm($params['from']), \Omi\App::GetFormEntity($params['from']));
			$grid->data->populate(qImplodeEntity($populate_sel));
		}
        
        $grid->_is_popup_ = true;
        $grid->settings['_is_popup_'] = true;
		
		$grid->setArguments([$grid->settings, $grid->data, $grid->grid_params, $grid_mode, $id], "renderForm");
			
		if (($grid_mode === 'edit') || ($grid_mode === 'add'))
			$grid->setRenderMethod("renderForm");
		else if ($grid_mode === 'list')
			$grid->setRenderMethod("renderList");
		
		$grid->render();
	}
	
	/**
	 * @api.enable
	 * @param mixed $data
	 * @param array $grid_data
	 */
	public static function FormSubmit($data, $grid_data)
	{
		try
		{
			if (!$data || (count($data) === 0))
				throw new \Exception("No data submitted!");
			static::BeforeProcessData($data);
			static::BeforeProcessFiles($_FILES);

			$grid = self::GetLoadedGrid($grid_data);
			
			$grid->prepareSubmit($data, $_FILES);
			$ret = $grid->doSubmitData($grid->submitData, $grid->grid_mode, $grid->grid_id);
			$from = $grid->from;
			$model = $ret->{$from} ? q_reset($ret->{$from}) : null;
			
			$no_action = null;
			$new_url = null;
			$stay_on_page = static::stay_on_page_after_save($grid, $data, $grid_data, $model);
			if (is_string($stay_on_page))
			{
				$new_url = $stay_on_page;
				$stay_on_page = null;
			}
			else if ($stay_on_page === static::stay_on_page_after_save_NO_ACTION)
				$no_action = true;
			
			return [$model, ($new_url ? true : null), $new_url ?: null, $stay_on_page, $no_action];
		}
		catch (\Exception $ex)
		{
			throw $ex;
		}
	}
	
	public static function FormSubmit_extract_misc_json(array &$data)
	{
		$has_json = false;
		foreach ($data ?: [] as $k => $v)
		{
			if ($k === '_json_')
				$has_json = true;
			else if (is_array($v))
				static::FormSubmit_extract_misc_json($data[$k]);
		}
		if ($has_json)
		{
			$v = $data['_json_'];
			if (Q_USE_XSS_INPUT_PROTECTION)
				$v = q_xss_decode($v);
			
			$json_data = is_string($v) ? json_decode($v, true) : $v;
			if (is_string($json_data))
			{
				try {
					$n_json = json_decode($json_data, true);
					if (($n_json !== false) && ($n_json !== null))
						$json_data = $n_json;
				} catch (\Exception $ex) {}
			}
			
			if (is_array($json_data))
				static::FormSubmit_extract_misc_json_merge($data, $json_data);
			unset($data['_json_']);
		}
	}
	
	public static function FormSubmit_extract_misc_json_merge(array &$data, array $json_data)
	{
		foreach ($json_data ?: [] as $k => $v)
		{
			$dk = $data[$k];
			if ($dk === null)
				$data[$k] = $v;
			else if (is_array($dk) && is_array($v))
				static::FormSubmit_extract_misc_json_merge($data[$k], $v);
		}
	}
	
	/**
	 * Redirect after save
	 * 
	 * @param \Omi\View\Grid $grid
	 * @param type $data
	 * @param type $grid_data
	 * 
	 * @return boolean
	 */
	public static function stay_on_page_after_save(\Omi\View\Grid $grid = null, $data = null, $grid_data = null, $model = null)
	{
		# can return a url (as string) to redirect to
		return false;
	}
}

