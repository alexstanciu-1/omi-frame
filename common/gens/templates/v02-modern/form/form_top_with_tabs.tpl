<div xg-security="$grid_mode, $settings['model:property'], $vars_path, $data" xg-item='<?= $xg_tag ?>' class='qc-ref-ctrl-form qc-xg-item' 
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
	
	<div class="qc-inner">
		<div class="page-header">
			<a href="{{$this->url()}}" class="qc-back-btn btn-back">
				Back
			</a>
			<h2 class="page-title">
				@code
					$caption_add = !empty('<?= $addCaption ?>') ? '<?= $addCaption ?>' : null;
					$caption_edit = !empty('<?= $editCaption ?>') ? '<?= $editCaption ?>' : null;
					$caption_view_mode = !empty('<?= $viewCaption ?>') ? '<?= $viewModeCaption ?>' : null;
					$caption_delete = !empty('<?= $deleteCaption ?>') ? '<?= $deleteCaption ?>' : null;
				@endcode
				@if (($grid_mode === "add") && $caption_add)
					{{_L($caption_add)}}
				@elseif (($grid_mode === "edit") && $caption_edit)
					{{_L($caption_edit)}}
				@elseif (($grid_mode === "view") && $caption_view_mode)
					{{_L($caption_view_mode)}}
				@else
					{{_L($this->caption ?: '<?= $viewCaption ?>')}} | <span class="_tprimary">{{( $this->show_caption_action ? (($grid_mode === "add") ? " "._L("Add") : (($grid_mode === "edit") ? " "._L("Update") : (($grid_mode === "delete") ? " "._L("Delete") : " "._L("View")))) : "")}}</span>
				@endif
			</h2>
			<div class="page-header-actions">
				<!-- <a href="{{$this->url()}}" class="qc-back-btn btn btn-border btn-primary">
					&laquo; <span class="_tblack">{{_T('5a312a7b7299e', 'back')}}</span>
				</a> -->
				@if ($grid_mode !== 'view')
					<a href="javascript: void(0);" class="btn btn-info btn-border qc-submit-btn">
						{{_T('5a2fa73ca20f6', 'Save')}}
					</a>
				@endif
				<?php if (!$isPureReference) : ?>
					<div class='qc-top-actions _dinlineblock'>
						@if ($data && $data->getId() && ($show_edit || $show_view || $show_delete))
							@if ($show_edit)
								<a href='{{$this->getUrlForTag("id", "edit", $data->getId())}}' class="btn btn-info qc-edit-btn btn-border">
									{{_L('Edit')}}
								</a>
							@endif
							@if ($show_view)
								<a href='{{$this->getUrlForTag("id", "view", $data->getId())}}' class="btn btn-info qc-view-btn btn-border">
									{{_L('View')}}
								</a>
							@endif
							@if ($show_delete)
								<a href='{{$this->getUrlForTag("id", "delete", $data->getId())}}' class="btn btn-danger qc-delete-btn btn-delete btn-border">
									{{_L('Delete')}}
								</a>
							@endif
						@endif
					</div>
				<?php endif; ?>
			</div>
		</div>
		<div class="page-body {{(($grid_mode == 'edit') || ($grid_mode == 'add') || ($grid_mode == 'view') || ($grid_mode == 'delete')) ? 'page-body-margin' : ''}}">
			<div class='qc-grid-properties' data-properties='{{$this->getJsProperties()}}'></div>
			<form name="<?= $xg_tag ?>" class="xg-form" xg-form='<?= $xg_tag ?>' enctype='multipart/form-data' method='POST' autocomplete='off'>
				<input type="hidden" value="1" name="__submitted" />
				@if ($id)
					<input type="hidden" value="{{$id}}" name="{{$vars_path ? $vars_path.'[Id]' : 'Id'}}" />
				@else
					<?= $hiddens ?>
				@endif
				<div class="qc-tab-panel qc-main-tabs-panel jx-tab-panel">
					<?php if ($tabs && (count($tabs) > 0)) : ?>
						<ul class="qc-main-tabs tabs popup-hide">
							<?php
							$pos = 0;
							foreach ($tabs as $tabData) : 
								list($for, $caption, $active, $property) = $tabData;
								$pos++;
								?>
								<li class='qc-tab-itm' qc-tab-itm-property="<?= $property ?>">
									@php $current_for = "<?= $for ?>";
									<div data-controls="<?= $for ?>" qc-tab-property="<?= $property ?>" class="tab-link jx-tab<?= $active ? ' active' : '' ?>">
										{{_L('<?= $caption ?>')}}
									</div>
								</li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>
					<div class='tabs-content jx-tabs-content'>
						<?= $tabs_str ?>
					</div>
				</div>
				@php $cls = ($grid_mode == 'add') ? 'btn-success' : (($grid_mode == 'delete') ? 'btn-alert' : 'btn-warning');
				@php $caption = ($grid_mode == 'add') ? 'CREATE' : (($grid_mode == 'delete') ? 'DELETE' : 'SAVE')
				@if ($grid_mode !== 'view')
					<!-- <button class="qc-submit-btn btn m-top-2 btn-primary m-left-30" onclick="return false;">{{_L($caption)}}</button> -->
				@endif
			</form>
		</div>
	</div>
</div>