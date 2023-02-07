<div xg-item='<?= $xg_tag ?>' class='qc-xg-item' 
	 q-args="$settings = null, $data = null, $bind_params = null, $grid_mode = null, $id = null, $vars_path = '<?= $vars_post_path ?>', $_qengine_args = null">
	<div class='sidebar'>
		<div class='sidebar-content'>
			<?= $search_str ?>
		</div>
	</div>
	@code
		if (!$_qengine_args)
			$_qengine_args = [];
		$_qengine_args['mainData'] = $data;
		if (is_scalar($data))
			$data = \QApi::QueryById("#<?= addslashes(implode(";#", $src_from_types)) ?>;", $data, "<?= qImplodeEntity($selector) ?>");

		$user = \QApi::Call('\Omi\User::GetCurrentUser');
		$dataCls = \QApp::GetDataClass();
		$show_edit = ((($grid_mode !== "add") && ($grid_mode !== "edit")) && (!$dataCls::$_USE_SECURITY_FILTERS || ($user && $user->can('edit', '<?= $config['__view__'] ?>', $data))));
		$show_view = ((($grid_mode !== "add") && ($grid_mode !== "view")) && (!$dataCls::$_USE_SECURITY_FILTERS || ($user && $user->can('view', '<?= $config['__view__'] ?>', $data))));
		$show_delete = ((($grid_mode !== "add") && ($grid_mode !== "delete")) && (!$dataCls::$_USE_SECURITY_FILTERS || ($user && $user->can('delete', '<?= $config['__view__'] ?>', $data))));
	@endcode
	
	<div class='qc-inner'>
		<div class="page-header">
			<h2 class='page-title'>{{_L($this->caption ?: '<?= $viewCaption ?>') . ( $this->show_caption_action ? (($grid_mode === "add") ? " "._L("Add") : (($grid_mode === "edit") ? " "._L("Update") : (($grid_mode === "delete") ? " "._L("Delete") : " "._L("View")))) : "")}}</h2>
			<?php if (!$isPureReference) : ?>
				<div class='qc-top-actions m-bottom-1'>
					<a href="{{$this->url()}}" class="qc-back-btn btn-info btn-border qc-tooltip tooltip-bottom m-right-20">
						<i class="fa fa-arrow-left"></i>
						{{_L('Back')}}
					</a>
					@if ($data && $data->getId() && ($show_edit || $show_view || $show_delete))
						@if ($show_edit)
							<a href='{{$this->getUrlForTag("id", "edit", $data->getId())}}' class="qc-edit-btn btn btn-warning btn-border">
								{{_L('Edit')}}
							</a>
						@endif
						@if ($show_view)
							<a href='{{$this->getUrlForTag("id", "view", $data->getId())}}' class="btn btn-warning qc-view-btn btn-border">
								{{_L('View')}}
							</a>
						@endif
						@if ($show_delete)
							<a href='{{$this->getUrlForTag("id", "delete", $data->getId())}}' class="btn btn-danger qc-delete-btn btn-border">
								{{_L('Delete')}}
							</a>
						@endif
					@endif
				</div>
			<?php endif; ?>
		</div>
		<div class='page-body'>
			<div class='qc-grid-properties' data-properties='{{$this->getJsProperties()}}'></div>
			<form class="xg-form" xg-form='<?= $xg_tag ?>' enctype='multipart/form-data' method='POST' autocomplete='off'>
				<input type="hidden" value="1" name="__submitted" />
				@if ($id)
					<input type="hidden" value="{{$id}}" name="{{$vars_path ? $vars_path.'[Id]' : 'Id'}}" />
				@else
					<?= $hiddens ?>
				@endif
				<?= $tabs_str ?>
				@php $cls = (($grid_mode == 'add') || ($grid_mode == 'edit')) ? 'btn-success' : (($grid_mode == 'delete') ? 'btn-alert' : 'btn-warning');
				@php $caption = ($grid_mode == 'add') ? 'CREATE' : (($grid_mode == 'delete') ? 'DELETE' : 'SAVE')
				@if ($grid_mode !== 'view')
					<button class="qc-submit-btn btn {{$cls}}" onclick="return false;">{{_L($caption)}}</button>
				@endif
			</form>
		</div>
	</div>
</div>