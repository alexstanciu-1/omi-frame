<tr xg-item='<?= $xg_tag ?>' class="item qc-xg-item qc-coll-subpart-itm qc-coll-itm border-b"
	q-args='$settings = null, $data = null, $bind_params = null, $grid_mode = null, $id = null, $vars_path = "<?= $vars_post_path ?>", $_qengine_args = null, $_col_widths = null'
	<?= $_is_scalar ? " qc-coll-scalar-itm" : "" ?>>
	@code
		if (is_scalar($data))
			$data = \QApi::QueryById("#<?= addslashes(implode(";#", $src_from_types)) ?>", $data, '<?= qImplodeEntity($selector) ?>');
		$_indx = $_qengine_args && isset($_qengine_args["_k"]) ? $_qengine_args["_k"] : null;
		$_is_first = ($_indx === 0);
		$_is_mandatory = <?= $propIsMandatory  ? "true" : "false" ?>;
		$_hide_rm = ($_is_mandatory && $_is_first);
	@endcode
	<?php if (!$read_only) : ?>		
		<td class='qc-order-handle-wrapper'>
			
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
			<?= $hiddens ?>
		</td>
	<?php endif ?>
		
	<?php if ($properties && (count($properties) > 0)) : 
		foreach ($properties as $propertyData): 
			list($xg_prop_tag, $str_property) = $propertyData;
		?>
			<td xg-property='<?= $xg_prop_tag ?>' class="qc-xg-property px-6 py-4 whitespace-no-wrap text-sm leading-5 text-cool-gray-500">
				<?= $str_property ?>
			</td>
	<?php endforeach;
	endif; if (!$read_only) : ?>
		<td class='qc-actions-wr'>
			@if (!$_hide_rm)
				<ul class="actions mt-1 mb-3">
					<li>
						<a href="javascript: void(0);" title="{{_L('Delete')}}" class="inline-flex items-center px-4 py-2 border border-red-300 text-sm leading-5 rounded-md text-red-700 bg-white hover:text-red-500 focus:outline-none focus:shadow-outline-indigo focus:border-indigo-300 active:text-indigo-800 active:bg-indigo-50 active:text-indigo-800 transition duration-150 ease-in-out font-medium qc-ref-ctrl-delete qc-delete"
						   data-vars-path="{{$_qengine_args['_tsp'] ? $_qengine_args['_tsp'][<?= $isOneToMany ? '1' : '0' ?>] : ""}}">
							{{_L('Delete')}}
						</a>
					</li>
				</ul>
			@endif
		</td>
	<?php endif; ?>
</tr>
