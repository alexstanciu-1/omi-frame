<div class="js-registration-number-ctrl registration-number-ctrl" q-args="$name = null, $data = null, $disabled = false, $cls = null">
	@var $year = date("Y");
	@var $parts = $data ? explode("/", $data) : null;
	<div class="row">
		<div class="col-2-12">
			<select<?= $disabled ? " disabled='disabled'" : "" ?> class="js-regno-part js-regno-sel select-2-no-search<?= $cls ? $cls : '' ?>">
				<option selected='selected' disabled="disabled">--</option>
				<option<?= ($parts && $parts[0] && ($parts[0] == "J")) ? " selected='selected'" : "" ?> value="J">J</option>
				<option<?= ($parts && $parts[0] && ($parts[0] == "F")) ? " selected='selected'" : "" ?> value="F">F</option>
				<option<?= ($parts && $parts[0] && ($parts[0] == "C")) ? " selected='selected'" : "" ?> value="C">C</option>
				<option<?= ($parts && $parts[0] && ($parts[0] == "-")) ? " selected='selected'" : "" ?> value="-">-</option>
			</select>
		</div>
		<div class="col-3-12">
			<select<?= $disabled ? " disabled='disabled'" : "" ?> class="js-regno-part js-regno-sel select-2<?= $cls ? $cls : '' ?>">
				<option selected='selected' disabled="disabled">--</option>
				@for ($i = 0; $i < 53; $i++)
					@var $currentValue = (($i < 10) ? "0" : "").$i;
					<option<?= ($parts && $parts[1] && ($parts[1] == $currentValue)) ? " selected='selected'" : "" ?> value="{{$currentValue}}">{{$currentValue}}</option>
				@endfor
			</select>
		</div>
		<div class="col-3-12">
			<input<?= $disabled ? " disabled='disabled'" : "" ?> type="text" value="<?= ($parts && $parts[2]) ? $parts[2] : '' ?>" class="js-regno-part js-regno-text<?= $cls ? $cls : '' ?>" />
		</div>
		<div class="col-4-12">
			<select<?= $disabled ? " disabled='disabled'" : ""  ?> class="js-regno-part js-regno-sel select-2<?= $cls ? $cls : '' ?>">
				<option selected='selected' disabled="disabled">--</option>
				@for ($i = 1990; $i <= $year; $i++)
					<option<?= ($parts && $parts[3] && ($parts[3] == $i)) ? " selected='selected'" : "" ?> value="{{$i}}">{{$i}}</option>
				@endfor
			</select>
		</div>
	</div>
	<input type="hidden" name="{{$name}}" value="{{$data}}" class="js-registration-number" />
</div>