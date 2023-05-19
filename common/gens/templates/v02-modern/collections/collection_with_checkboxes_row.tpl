<tr xg-item='<?= $xg_tag ?>' class="item qc-xg-item qc-coll-chk-itm qc-coll-itm"
	q-args='$settings = null, $data = null, $bind_params = null, $grid_mode = null, $id = null, $vars_path = "<?= $vars_post_path ?>", $_qengine_args = null, $_col_widths = null'>
	
	@if (is_scalar($data))
		@php $data = \QApi::QueryById("#<?= addslashes(implode(";#", $src_from_types)) ?>", $data, '<?= qImplodeEntity($selector) ?>');
	@endif
	
	<?php if (!$read_only) : ?>		
		<!-- <td class='qc-order-handle-wrapper'>
			 <div class='qc-handle'>
				<ul class='actions'>
					<li>
						<a class='fa fa-th-large js-handle' href='javascript: void(0);'></a>
						<ul class='dropdown bg-white shadow-1'>
							<li>
								<a class='mdt-c' href='javascript: void(0);'>{{_L('First')}}</a>
							</li>
							<li>
								<a class='mdt-c' href='javascript: void(0);'>{{_L('Up')}}</a>
							</li>
							<li>
								<a class='mdt-c' href='javascript: void(0);'>{{_L('Down')}}</a>
							</li>
							<li>
								<a class='mdt-c' href='javascript: void(0);'>{{_L('Last')}}</a>
							</li>
						</ul>
					</li>
				</ul>
			</div>
		</td> -->
	<?php endif ?>
		
	<td class="qc-chk-cell px-6 py-4 text-center">
		@php $selectedData = ($_qengine_args && $_qengine_args['_collItm']) ? $_qengine_args['_collItm'] : null;
		<?= $hiddens ?>
		<div class="qc-checkbox">
			<label for="{{$unk = uniqid()}}" class="cursor-pointer">
				<input{{$selectedData ? ' checked' : ''}} class="qc-chkcollitm-pick form-checkbox h-4 w-4 text-indigo-600 transition duration-150 ease-in-out" type='checkbox' id='{{$unk}}' 
					{{$_qengine_args["_tsp"] ? " data-vars-path='".$_qengine_args['_tsp'][<?= $isOneToMany ? '1' : '0' ?>]."'" : ""}} />
			</label>
		</div>
	</td>
	
	<?php if ($properties && (count($properties) > 0)) : 
		foreach ($properties as $propertyData): 
			list($xg_prop_tag, $str_property) = $propertyData; ?>
			<td xg-property='<?= $xg_prop_tag ?>' class="qc-xg-property px-6 py-4 text-left whitespace-no-wrap text-sm leading-5 text-cool-gray-500">
				<?= $str_property ?>
			</td>
	<?php endforeach;
	endif; ?>
</tr>