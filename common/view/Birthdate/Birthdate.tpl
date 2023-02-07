<div class="js-birthdate-ctrl birthdate-ctrl" q-args="$name = null, $birthdate = null, $disabled = false, $cls = null, $params = null">
	@var $year = date("Y");

	@var $birthDateTime = $birthdate ? strtotime($birthdate) : null;

	@var $bDay = $birthDateTime ? (int)date("j", $birthDateTime) : null;
	@var $bMonth = $birthDateTime ? (int)date("n", $birthDateTime) : null;
	@var $bYear = $birthDateTime ? (int)date("Y", $birthDateTime) : null;

	<?php 

	$years = null;
	$months = null;
	$days = null;
	
	if ($params && $params["Values"])
	{
		$years = $params["Values"]["Years"];
		$months = $params["Values"]["Months"];
		$days = $params["Values"]["Days"];
	}

	$ranges = ($params && $params['Ranges']) ? $params['Ranges'] : null;
	if ($ranges)
	{
		if (!$years && $ranges["Years"])
		{
			$years = [];
			list($rangeStart, $rangeEnd) = $ranges["Years"];
			$increment = ($rangeStart < $rangeEnd);
			for ($i = $rangeStart; $increment ? ($i <= $rangeEnd) : ($i >= $rangeEnd); $increment ? $i++ : $i--)
				$years[] = $i;
		}

		if (!$months && $ranges["Months"])
		{
			$months = [];
			list($rangeStart, $rangeEnd) = $ranges["Months"];
			$increment = ($rangeStart < $rangeEnd);
			for ($i = $rangeStart; $increment ? ($i <= $rangeEnd) : ($i >= $rangeEnd); $increment ? $i++ : $i--)
				$months[] = $i;
		}

		if (!$days && $ranges["Days"])
		{
			$days = [];
			list($rangeStart, $rangeEnd) = $ranges["Days"];
			$increment = ($rangeStart < $rangeEnd);
			for ($i = $rangeStart; $increment ? ($i <= $rangeEnd) : ($i >= $rangeEnd); $increment ? $i++ : $i--)
				$days[] = $i;
		}
	}

	// setup defaults here!
	if (!$years)
	{
		$years = [];
		for ($i = 0; $i < 90; $i++)
			$years[] = $year - $i;
	}

	if (!$months)
	{
		$months = [];
		for ($i = 1; $i <= 12; $i++)
			$months[] = $i;
	}

	if (!$days)
	{
		$days = [];
		for ($i = 1; $i <= 31; $i++)
			$days[] = $i;
	}
	
	?>
	<div class="col-3-12">
		<select<?= $disabled ? " disabled='disabled'" : "" ?> class="select-2-no-search js-birthdate-day<?= $cls ? $cls : '' ?>">
			<option selected="selected" disabled="disabled">{{_L("Day")}}</option>
			@each ($days as $day)
				<option<?= ($bDay && ($bDay === $day)) ? " selected='selected'" : "" ?> value="<?= (($day < 10) ? '0' : '').$day ?>">{{$day}}</option>
			@endeach
		</select>
	</div>
	<div class="col-5-12">
		<select<?= $disabled ? " disabled='disabled'" : "" ?> class="select-2-no-search js-birthdate-month<?= $cls ? $cls : '' ?>">
			<option selected="selected" disabled="disabled">{{_L("Month")}}</option>
			@each ($months as $month)
				<option<?= ($bMonth && ($bMonth === $month)) ? " selected='selected'" : "" ?> value="<?= (($month < 10) ? '0' : '').$month ?>"><?= ucfirst(strftime("%B", strtotime($year."-".$month."-01"))) ?></option>
			@endeach
		</select>
	</div>
	<div class="col-4-12">
		<select<?= $disabled ? " disabled='disabled'" : "" ?> class="select-2-no-search js-birthdate-year<?= $cls ? $cls : '' ?>">
			<option selected="selected" disabled="disabled">{{_L("Year")}}</option>
			@each ($years as $yr)
				<option<?= ($bYear && ($bYear === $yr)) ? " selected='selected'" : "" ?> value="<?= $yr ?>"><?= $yr ?></option>
			@endeach
		</select>
	</div>
	<div class="clearfix"><!-- --></div>
	<input type="hidden" name="<?= $name ?>" value="<?= $birthdate ?>" class="js-birthdate" />
</div>
