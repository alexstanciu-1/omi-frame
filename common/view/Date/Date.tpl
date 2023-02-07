<div<?= $attrs ? $attrs : "" ?> class="js-datepickr-ctrl datepickr-ctrl" jsFunc="render($name = @qb, $dbValue, $showValue, $params, $cls, $attrs)" 
	q-args="$name = null, $dbValue = null, $showValue = null, $params = null, $cls = null, $attrs = null" 
	data-params='<?= $params ? json_encode($params) : "" ?>'>
	<div class="omi-datepickr-wrapper">
		<input type="text" class="full-width datepickr-show js-datepickr<?= $cls ? $cls : "" ?>" readonly="readonly" value="<?= $showValue ? $showValue : "" ?>" />
		<i class="mdt-c fa fa-calendar datepickr-calendar-icon"></i>
	</div>
	<input type="hidden" class="js-datepickr-dbval" name="<?= $name ? $name : "" ?>" value="<?= $dbValue ? $dbValue : "" ?>" />
</div>