<tr xg-security="$grid_mode, $settings['model:property'], $vars_path, $data" xg-item='<?= $xg_tag ?>' class="bg-white qc-xg-item qc-ref-ctrl-form"
    q-args='$settings = null, $data = null, $bind_params = null, $grid_mode = null, $id = null, $vars_path = "<?= $vars_post_path ?>", $_qengine_args = null, $_col_widths = null'>

    @code
	$user = \Omi\User::GetCurrentUser();
        $dataCls = \QApp::GetDataClass();
        $can_view = (!$dataCls::$_USE_SECURITY_FILTERS || ($user && $user->can('view', '<?= $config['__view__'] ?>', $data)));
        $can_edit = (!$dataCls::$_USE_SECURITY_FILTERS || ($user && $user->can('edit', '<?= $config['__view__'] ?>', $data)));
        $can_delete = (!$dataCls::$_USE_SECURITY_FILTERS || ($user && $user->can('delete', '<?= $config['__view__'] ?>', $data)));
        $can_provisioning_push = ((!$dataCls::$_USE_SECURITY_FILTERS || ($user && $user->can('provisioning_push', '<?= $config['__view__'] ?>', $data))) && $this->provisioning_can_push);
        $can_provisioning_pull = ((!$dataCls::$_USE_SECURITY_FILTERS || ($user && $user->can('provisioning_pull', '<?= $config['__view__'] ?>', $data))) && $this->provisioning_can_pull);

        if (is_scalar($data))
			$data = \QApi::QueryById("#<?= addslashes(implode(";#", $src_from_types)) ?>", $data, '<?= qImplodeEntity($selector) ?>');
        $_pos = ($_qengine_args && isset($_qengine_args["_pos"])) ? $_qengine_args["_pos"] : null;
    @endcode

    <?php if ($_TYPE_FLAGS['list_checkboxes']): 
	?><td class='qc-chk-cell'>
		<input type="hidden" class="qc-hidden-id" name='{{$this->from}}[{{$_pos}}][Id]' value="{{$data->Id}}" />
		<input type="hidden" class="qc-hidden-ty" name='{{$this->from}}[{{$_pos}}][_ty]' value="{{is_object($data) ? get_class($data) : ''}}" />
		<div class='qc-checkbox'>
			<input class='qc-chkcollitm-pick' type='checkbox' id='{{$unk = uniqid()}}' 
				{{$_qengine_args["_tsp"] ? " data-vars-path='".$_qengine_args['_tsp'][0]."'" : ""}} />
			<label for='{{$unk}}'></label>
		</div>
    </td><?php endif; ?>
    <td class='qc-pos-cell px-6 py-4 text-right whitespace-no-wrap text-sm leading-5 text-cool-gray-500 w-2'>
        {{$_pos ? $_pos : ""}}
        <form method='POST' class='qc-list-form' autocomplete='off'>
            <?= $hiddens ?>
        </form>
    </td>
        
    <?php if ($properties && (count($properties) > 0)) : 
            foreach ($properties as $propertyData): 
                list($xg_prop_tag, $str_property, $setup_view_link, $prop_inf, $apply_translate, $tmp_PROP_FLAGS) = $propertyData; ?>
                    <td xg-security="$grid_mode, $settings['model:property'], $vars_path, $data" xg-security-property='<?= $prop_inf ?>' xg-property='<?= $xg_prop_tag ?>' class="qc-xg-property px-6 py-4 whitespace-no-wrap text-sm leading-5 text-cool-gray-500 <?= $tmp_PROP_FLAGS["display.list-css-classes"] ?>">
				<?php $listing_link = $tmp_PROP_FLAGS['listing.link'] ?? null; ?>
				<?php $str_property = $tmp_PROP_FLAGS['listing.caption'] ?? $str_property; ?>
				<?= 
					(($setup_view_link && ($listing_link !== false)) || $listing_link) ? 
					"<?php if (\$can_view) : ?>"
					. "<a class=\"flex qc-view-link text-blue-700 font-normal underline\" href='{{".($listing_link ? "\$data->{$prop_inf} ? \"".$listing_link."\".((!is_string(\$data->{$prop_inf})) ? \$data->{$prop_inf}->getId() : \$data->getId()) . \"\" : 'javascript: //'" : "\$this->getUrlForTag(\"id\", \"view\", \$data->getId())")."}}'>{$str_property}<svg class='w-3 h-3 ml-2' fill='none' viewBox='0 0 24 24' stroke='currentColor'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1' /></svg></a><?php else : ?>{$str_property}<?php endif; ?>" : $str_property ?>
			</td>
        	<?php endforeach;
    endif; ?>

    <td class="qc-actions-wr px-6 py-4 text-right whitespace-no-wrap text-sm leading-5 text-cool-gray-500">
		@if ($data && ($can_edit || $can_delete || $can_provisioning_push || $can_provisioning_pull))
            <div class="relative flex justify-end items-center js-listActionsDropDown-Container">
                @if ($can_edit)
                    <a data-tippy-content="{{_L('Edit')}}" href='{{$this->getUrlForTag("id", "edit", $data->getId())}}' class="group flex items-center p-2 text-sm text-gray-400 tpl-listItemEditButton css-list-item-edit rounded-md">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                        </svg>
                    </a>
                @endif

                @if ($can_delete)
                    <a data-tippy-content="{{_L('Delete')}}" href="javascript: void(0);" data-id="{{$data->getId()}}" data-model="{{get_class($data)}}" class="group flex items-center p-2 text-sm text-gray-400 js-delete-item css-list-item-delete rounded-md">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </a>
                @endif
            </div>
        
			<!-- <div class="relative flex justify-end items-center js-listActionsDropDown-Container q-hide-on-click-away">
				<a href="javascript: void(0);" class="js-listActionsDropDown-Trigger w-8 h-8 inline-flex items-center justify-center text-gray-400 rounded-full bg-transparent hover:text-gray-500 focus:outline-none focus:text-gray-500 focus:bg-gray-100 transition ease-in-out duration-150">
					<svg class="w-5 h-5" x-description="Heroicon name: dots-vertical" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
						<path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"></path>
					</svg>
				</a>
				<div class="q-hide-on-click-away-container fixed right-0 mr-12 z-10 w-48 rounded-md shadow-lg hidden js-listActionsDropDown">
					<div class="z-10 rounded-md bg-white shadow-xs">
						<div class="py-1">
							@if ($can_edit)
								<a href='{{$this->getUrlForTag("id", "edit", $data->getId())}}' class="group flex items-center px-4 py-2 text-sm leading-5 hover:bg-gray-100 text-indigo-600 font-medium hover:text-indigo-900 tpl-listItemEditButton">{{_L('Edit')}}</a>
							@endif
							
							@if ($can_view && false)
								<a href='{{$this->getUrlForTag("id", "view", $data->getId())}}' class="group flex items-center px-4 py-2 text-sm leading-5 hover:bg-gray-100 font-medium tpl-listItemViewButton">{{_L('View')}}</a>
							@endif
							
							@if ($can_delete)
								<a href="javascript: void(0);" data-id="{{$data->getId()}}" data-model="{{get_class($data)}}" class="group flex items-center px-4 py-2 text-sm leading-5 hover:bg-gray-100 text-red-500 font-medium hover:text-red-700 js-delete-item">{{_L('Delete')}}</a>
							@endif
						</div>
					</div>
				</div>
			</div> -->
        @endif
    </td>
</tr>
