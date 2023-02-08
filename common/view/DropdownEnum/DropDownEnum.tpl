<div class="qc-enum-dd-ctrl js-enum-dd-ctrl omi-control QWebControl<?= (($multi === true) ? ' qc-enum-dd-ctrl-multi' : '') . ($cssCls ? ' ' . trim($cssCls) : '') ?>" 
	q-args="$itms = null, $captions = null, $selected = null, $caption = null, $params = null, $dd_props = null, $multi = false, $cssCls = null, $fieldsCls = null">

	@var $data_id = uniqid();
	
	<?php
		if (is_array($multi))
		{
			$multi = q_reset($multi);
			$force_heading = true;
		}
	?>
	

	@if ($params === null)
		@var $params = [];
	@endif

	@var $_is_mandatory = $params["_is_mandatory"];
	@var $_xg_tag = $params["_xg_tag"];
	@var $_extra_attrs = $params["_extra_attrs"];
	@var $_q_valid = $params["_q_valid"];
	@var $_q_fix = $params["_q_fix"];
	@var $_q_info = $params["_q_info"];
	@var $_prop_name = $params["_prop_name"];
	@var $_validation = $params['_validation'];

	@if ($dd_props === null)
		@var $dd_props = [];
	@endif

	@var $name = $dd_props['name'] ?:  "_rdrpl_" . uniqid();
	@var $dd_cls = $dd_props['dd_cls'];
	@var $dd_style = $dd_props['dd_style'];

	@if ($dd_cls === null)
		@var $dd_cls = "";

	@elseif (strlen(trim($dd_cls)) > 0)
		@var $dd_cls = " ".trim($dd_cls);
	@endif

	@if ($multi)
		@var $dd_cls .= " qc-enum-dd-multi";
		@php $selected_values = $selected ? array_flip($selected) : null;
	@endif

	@if ($dd_style === null)
		@var $dd_style = "";
	@endif
	@php $multi_gids = [];

	<div class="qc-enum-dd qs-enum-dd<?= $dd_cls ?>" <?= ($dd_style && (strlen($dd_style) > 0)) ? ' style="' . $dd_style . '"' : ""  ?>>
		<div class="qc-enum-dd-tooltip tooltip-top">
			<div class="qc-enum-dd-caption-wr">
				<span class="qc-enum-dd-caption">{{$caption ? _L($caption) : _L("Select")}}</span>
			</div>
		</div>

		<ul class="dropdown" data-keep-title="{{$force_heading ? 1 : 0 }}">
			@php $pos = 0;
			@each ($itms ?: [] as $itm)

				@if ($multi)
					@var $multi_gids[$itm] = $data_id = uniqid();
				@endif

				<li>
					@if ($multi)
						<div class="qc-table-responsive multi-value-row qc-multi-check-row">
							<div class="table-row">
								<div class="table-cell f-w">
									<div class="qc-checkbox">
										<input<?= (($selected_values && isset($selected_values[$itm])) ? ' checked="checked"' : "") ?>  
											class="qc-multi-chk" type="checkbox" id="{{$_multi_chk_unq = uniqid()}}" />
										<label for="{{$_multi_chk_unq}}"></label>
									</div>
								</div>
								<div class="table-cell">
									<input<?= (($selected_values && isset($selected_values[$itm])) ? ' checked="checked"' : "") ?> 
										data-id="<?= $data_id ?>" 
										class="qc-enum-opt-chk qc-not-bool qc-keep-sync"
										id="<?= $unk = uniqid() ?>" 
										name="<?= $name ?>[<?= $pos++ ?>]" 
										value="<?= $itm ?>" 
										type="<?= $multi ? 'checkbox' : 'radio' ?>" />
									<label class="qc-enum-dd-opt" for="{{$unk}}">{{(isset($captions[$itm]) ? _L($captions[$itm]) : _L($itm))}}</label>
								</div>
							</div>
						</div>
					@else
						<input<?= (($selected && ($itm == $selected)) ? ' checked="checked"' : "") ?> 
							data-id="<?= $data_id ?>" 
							class="qc-enum-opt-chk qc-not-bool"
							id="<?= $unk = uniqid() ?>" 
							name="<?= $name ?>" 
							value="<?= $itm ?>" 
							type="<?= $multi ? 'checkbox' : 'radio' ?>" />
						<label class="qc-enum-dd-opt" for="{{$unk}}">{{(isset($captions[$itm]) ? _L($captions[$itm]) : _L($itm))}}</label>
					@endif
				</li>
			@endeach

		</ul>
	</div>

	@if (isset($params["_qis_form_element"]))
		@if ($multi)
			@php $pos = 0;
			@each ($itms ?: [] as $itm)
				<input 
					class="qc-form-element qc-dropdown-hidden<?= ($fieldsCls ? " " . $fieldsCls : "") . ($_is_mandatory ? ' q-mandatory' : '') . 
						(($selected_values && isset($selected_values[$itm])) ? ' qc-changed' : '') ?>" 
					xg-property-value="<?= $_xg_tag ?>" 
					type="hidden" 
					id="{{$multi_gids[$itm]}}"
					<?= $_extra_attrs ?>
					<?= ($_q_valid && (strlen($_q_valid) > 0)) ? " q-valid='{$_q_valid}'" : "" ?>
					<?= ($_q_fix && (strlen($_q_fix) > 0)) ? " q-fix='{$_q_fix}'" : "" ?>
					name<?= ($selected_values && isset($selected_values[$itm]) ? '' : '-x') ?>="<?= $_prop_name ?>[<?= $pos++ ?>]" 
					value="<?= $itm ?>" 
				/>
			@endeach
		@else
			<input class="qc-form-element qc-dropdown-hidden<?= ($fieldsCls ? " " . $fieldsCls : "") . ($_is_mandatory ? ' q-mandatory' : '') ?>" 
				xg-property-value="<?= $_xg_tag ?>" type="hidden" 
				id="<?= $data_id ?>"
				<?= $_extra_attrs ?>
				<?= ($_q_valid && (strlen($_q_valid) > 0)) ? " q-valid='{$_q_valid}'" : "" ?>
				<?= ($_q_fix && (strlen($_q_fix) > 0)) ? " q-fix='{$_q_fix}'" : "" ?>
				name<?= ($selected ? '' : '-x') ?>="<?= $_prop_name ?>" 
				value="<?= $selected ?>" 
			/>
		@endif

		@if ($_q_info)
			<div class="qc-tooltip info">
				<i class="fa fa-info-circle"></i>
				<span class="tooltip">{{$_q_info}}</span>
			</div>
		@endif

		@if ($_validation && (strlen($_validation) > 0))
			<span class="error-block"><?= $_validation ?></span>
		@endif
	@endif
</div>