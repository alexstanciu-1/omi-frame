<?php

foreach ($layout_placement["rows"] ?: [] as $lp_row_index => $lp_row)
{
	// count columns
	$cols_count = isset($lp_row['cols']) ? count($lp_row['cols']) : 0;

	$grp_layout .= "<div class='grid grid-cols-1 md:grid-cols-" . $cols_count . " mb-8 gap-8 gap-y-8 qc-boxes-row-{$lp_row_index}'>\n";
	foreach ($lp_row['cols'] ?: [] as $lp_col_index => $lp_col)
	{

		$grp_layout .= "<div class='flex flex-col gap-y-8 qc-boxes-row-{$lp_row_index}-col-{$lp_col_index}'>\n";

		foreach ($lp_col["sub-rows"] ?: [] as $lp_sub_row_index => $lp_sub_row)
		{
			$explicit_columns = false;
			foreach ($lp_sub_row["@select"] ?: [] as $tmp_lsr => $tmp_lsr_val)
			{
				if ((!is_numeric($tmp_lsr)) && (!empty($tmp_lsr_val)))
					$selected_props[$tmp_lsr] = $tmp_lsr;
				else
					$explicit_columns = true;
			}

			$kss_c = array_keys($lp_sub_row["@select"]);
			$inner_str = [];

			if ($explicit_columns)
			{
				$inner_str_pos = 0;
				foreach ($lp_sub_row["@select"] ?: [] as $tmp_lsr => $tmp_lsr_val)
				{
					if ((!is_numeric($tmp_lsr)) && (!empty($tmp_lsr_val)))
						$inner_str[$inner_str_pos] .= "{{@{$tmp_lsr}}}\n";
					else
						$inner_str_pos++;
				}
				$no_of_colls = count($inner_str);
			}
			else
			{
				$no_of_colls = $lp_sub_row["@cols"] ?: 1;
				# $no_of_colls = 2;
				$chunk_size = ceil(count($kss_c) / $no_of_colls);
				for ($nii = 0; $nii < $no_of_colls; $nii++)
				{
					$props_slice = array_slice($kss_c, ($nii * $chunk_size), $chunk_size);
					if (!$props_slice) # this was needed for testing
						continue;
					$inner_str[$nii] = "{{@".implode("}}\n{{@", $props_slice)."}}\n";
				}
			}

			$box_caption = preg_replace_callback('/(?<!\b)[A-Z][a-z]+|(?<=[a-z])[A-Z]/', function($match) {
				return ' '. $match[0];
			}, htmlspecialchars($lp_sub_row["@caption"] ?: $lp_sub_row["@tag"]));

			$box_caption = preg_replace('/[\\_\\s]+/', " ", $box_caption);

			$grp_layout .= "
			<div class='flex-1 rounded-lg bg-white p-4 lg:p-8 shadow js-details-box_".preg_replace("/([^\\w\\d])/uis", "_", $lp_sub_row["@tag"])."  qc-boxes-row-{$lp_row_index}-col-{$lp_col_index}-row-{$lp_sub_row_index}'>
									<h4 class=\"text-lg font-medium leading-6 text-gray-900 mb-4\">{{_L(\"".qaddslashes($box_caption)."\")}}</h4>
									<div class='row'>";

			if ($no_of_colls === 1)
				$responsive_value = 12;
			else if ($no_of_colls === 2)
				$responsive_value = 6;
			else if ($no_of_colls === 3)
				$responsive_value = 4;
			else if ($no_of_colls === 4)
				$responsive_value = 6;
			else if ($no_of_colls === 6)
				$responsive_value = 2;
			else 
				throw new \Exception("Sub-Columns count of {$no_of_colls} is not supported!");

			foreach ($inner_str as $inns)
				$grp_layout .= "<div class='col-lg-{$responsive_value} col-md-12'>
						{$inns}
					</div>";

			$grp_layout .= "	</div>

			</div>
			";
		}

		$grp_layout .= "</div>\n";
	}

	$grp_layout .= "</div>\n";
}
