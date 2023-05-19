<tr xg-item='<?= $xg_tag ?>' class="item qc-xg-item qc-coll-scalar-itm qc-coll-itm"
	q-args='$settings = null, $data = null, $bind_params = null, $grid_mode = null, $id = null, $vars_path = "<?= $vars_post_path ?>", $_qengine_args = null, $_col_widths = null'>
	@code
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
	<td xg-property='<?= $xg_prop_tag ?>' class="qc-xg-property qc-ref-cell">
		<?php 
			if (!$read_only) :
				$prev_md5_seed__ = $md5_seed__;
				$md5_seed__ = md5($md5_seed__."\ns-edit");
				include(static::GetTemplate("scalar/scalar_edit.tpl", $config));
				$md5_seed__ = $prev_md5_seed__;
			else :
				?>
				@echo $data;
				<?php
			endif;
			?>
	</td>
	<?php if (!$read_only) : ?>
		<td class='qc-actions-wr px-6 py-4 pr-0 text-right'>
			@if (!$_hide_rm)
				<ul class='actions'>
					<li>
						<a href="javascript: void(0);" title="{{_L('Delete')}}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm leading-5 font-medium rounded-md text-red-500 bg-white hover:text-red-700 hover:border-red-300 focus:outline-none focus:shadow-outline-blue focus:border-blue-300 active:text-gray-800 active:bg-gray-50 active:text-gray-800 transition duration-150 ease-in-out qc-ref-ctrl-delete qc-delete" {{$_qengine_args['_tsp'] ? " data-vars-path='".$_qengine_args['_tsp'][0]."'" : ""}}>
							Delete
						</a>
					</li>
				</ul>
			@endif
		</td>
	<?php endif; ?>
</tr>
