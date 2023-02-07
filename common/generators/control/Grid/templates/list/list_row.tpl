<tr xg-item='<?= $xg_tag ?>' class="item qc-xg-item qc-ref-ctrl-form"
	q-args='$settings = null, $data = null, $bind_params = null, $grid_mode = null, $id = null, $vars_path = "<?= $vars_post_path ?>", $_qengine_args = null, $_col_widths = null'>

	@code
		if (is_scalar($data))
			$data = \QApi::QueryById("#<?= addslashes(implode(";#", $src_from_types)) ?>", $data, '<?= qImplodeEntity($selector) ?>');
		
		$user = \QApi::Call('\Omi\User::GetCurrentUser');
		$dataCls = \QApp::GetDataClass();
		$can_view = (!$dataCls::$_USE_SECURITY_FILTERS || ($user && $user->can('view', '<?= $config['__view__'] ?>', $data)));
		$can_edit = (!$dataCls::$_USE_SECURITY_FILTERS || ($user && $user->can('edit', '<?= $config['__view__'] ?>', $data)));
		$can_delete = (!$dataCls::$_USE_SECURITY_FILTERS || ($user && $user->can('delete', '<?= $config['__view__'] ?>', $data)));
		$can_provisioning_push = ((!$dataCls::$_USE_SECURITY_FILTERS || ($user && $user->can('provisioning_push', '<?= $config['__view__'] ?>', $data))) && $this->provisioning_can_push);
		$can_provisioning_pull = ((!$dataCls::$_USE_SECURITY_FILTERS || ($user && $user->can('provisioning_pull', '<?= $config['__view__'] ?>', $data))) && $this->provisioning_can_pull);

		$_pos = ($_qengine_args && isset($_qengine_args["_pos"])) ? $_qengine_args["_pos"] : null;
	@endcode

	<td class='qc-pos-cell'>
		{{$_pos ? $_pos : ""}}
		<form method='POST' class='qc-list-form' autocomplete='off'>
			<?= $hiddens ?>
		</form>
	</td>
	<?php
	
	if (false) :
		
		?>
			<td>
				<div class="qc-checkbox padding-view-ck">
					<input class="qc-form-element qc-checkbox-inp qc-not-bool" id="SelectRecord__{{$data->getId()}}" 
							name="SelectRecord__[{{$data->getId()}}]" value="{{$data->getId()}}" type="checkbox">
					<label for="SelectRecord__{{$data->getId()}}"></label>
				</div>
			</td>
		<?php
		
	endif;
	
	?>
	<?php if ($properties && (count($properties) > 0)) : 
		foreach ($properties as $propertyData): 
			list($xg_prop_tag, $str_property, $setup_view_link, $prop_inf, $apply_translate, $tmp_PROP_FLAGS) = $propertyData; ?>
			<td xg-property='<?= $xg_prop_tag ?>' class="qc-xg-property <?= $tmp_PROP_FLAGS["display.list-css-classes"] ?>">
				<?= $setup_view_link ? 
					"<?php if (\$can_view) : ?>"
					. "<a class='qc-view-link' href='{{\$this->getUrlForTag(\"id\", \"view\", \$data->getId())}}'>{$str_property}</a><?php else : ?>{$str_property}<?php endif; ?>" : $str_property ?>
			</td>
	<?php endforeach;
	endif; ?>
	<td class="qc-actions-wr stretch-to-content">
		@if ($data && ($can_edit || $can_delete || $can_provisioning_push || $can_provisioning_pull))
			<ul class='actions inline qc-actions'>
				<li>
					<a title="Edit" href="javascript: void(0);" class="fa fa-cog mdt-c nowrap qc-ref-ctrl-edit"></a>
					<ul class='dropdown bg-white'>
						@if ($can_edit)
							<li class="q-edit-action-wr">
								<a title='{{_L("Edit")}}' href='{{$this->getUrlForTag("id", "edit", $data->getId())}}' class="mdt-c nowrap qc-ref-ctrl-edit qc-tooltip tooltip-left">
									<i class="fa fa-pencil"></i>
									<span class="tooltip">{{_L('Edit')}}</span>
								</a>
							</li>
						@endif
						@if ($can_delete)
							<li class="q-delete-action-wr">
								<a title='{{_L("Delete")}}' href='{{$this->getUrlForTag("id", "delete", $data->getId())}}'
								   class="mdt-a nowrap pointer qc-ref-ctrl-delete-full qc-tooltip tooltip-left">
									<i class="fa fa-times"></i>
									<span class="tooltip">{{_L('Delete')}}</span>
								</a>
							</li>
						@endif
						@if ($can_provisioning_push)
							<li class="q-provisioning-action-wr q-provisioning-push">
								<a title='{{_L("Push")}}' data-type="push" data-record-id="{{$data->getId()}}"
								   class="mdt-a nowrap pointer qc-provisioning-sync qc-tooltip tooltip-left">
									<i class="fa fa-upload info"></i>
									<span class="tooltip">Push</span>
								</a>
							</li>
						@endif
						@if ($can_provisioning_pull)
							<li class="q-provisioning-action-wr q-provisioning-pull">
								<a title='{{_L("Pull")}}' data-type="pull" data-record-id="{{$data->getId()}}"
								   class="mdt-a nowrap pointer qc-provisioning-sync qc-tooltip tooltip-left">
									<i class="fa fa-download success"></i>
									<span class="tooltip">Pull</span>
								</a>
							</li>
						@endif
					</ul>
				</li>
			</ul>
		@endif
	</td>
</tr>
