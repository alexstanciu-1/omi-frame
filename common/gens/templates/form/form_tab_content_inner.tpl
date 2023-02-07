<?php

{
	?><?php if ($tab_content["properties"]) : ?>
		<!-- <div class="qc-table-responsive<?= ($tab_props["breakLabels"] ? ' break-form-input' : '') ?>"> -->
			<?php
				foreach ($tab_content["properties"] ?: [] as $prop)
					echo $prop;
			?>
		<!-- </div> -->
	<?php endif;
	
	$has_any_data = false;

	ob_start();
	foreach ($tab_content["sections"] ?: [] as $sectionData)
	{
		$section = $sectionData["section"];

		$tab_cols = $tab_props["cols"];
		$_tab_c = ($tab_cols && ($tab_cols > 0)) ? 12 / $tab_cols : 0;

		$breakLabels = $tab_props["breakLabels"] ?: $section["breakLabels"];

		$prop = $sectionData["prop"];
		
		$has_any_data_sub_ = false;
		
		ob_start();
		
		?>

		<div class="<?= $prop ? 'qc-prop-' . $prop . ' ' : '' ?><?= $section["caption"] ? ' col-lg-12 col-sm-12 qc-section ' : '' ?><?= (($_tab_c > 0) ? ' col-md-' . $_tab_c : '') . ($breakLabels ? ' break-form-input' : '') ?>">
			<?php if ($section["caption"]) : ?>
				<div class="section-header">
					<h3 class="section-title">{{_L($section["caption"])}}</h3>
				</div>
			<?php endif; ?>
			<div class="section-body">

				<?php if ($sectionData["properties"]) : ?>
					<!-- <div class="qc-table-responsive"> -->
						<?php
							foreach ($sectionData["properties"] ?: [] as $prop)
							{
								echo $prop;
								$has_any_data_sub_ = true;
								$has_any_data = true;
							}
						?>
					<!-- </div> -->
				<?php endif; if ($sectionData["data"]) : 
					foreach ($sectionData["data"] ?: [] as $prop)
					{
						echo $prop;
						$has_any_data_sub_ = true;
						$has_any_data = true;
					}
				endif;

				$section_cols = $section["cols"];
				$_section_c = ($section_cols && ($section_cols > 0)) ? 12 / $section_cols : 0;

				foreach ($sectionData["sections"] ?: [] as $subSectionData)
				{
					$subSection = $subSectionData["section"];
					$breakLabels = $tab_props["breakLabels"] ?: ($section["breakLabels"] ?: $subSection["breakLabels"]);
					?>

					<div class="qc-subsection<?= (($_section_c > 0) ? ' col-md-' . $_section_c : '') ?>">
						<?php if ($subSection["caption"]) : ?>
							<div class="subsection-header">
								<h4 class="subsection-title"><?= $subSection["caption"] ?></h4>
							</div>
						<?php endif; ?>
						<div class="subsection-body">
							<!-- <div class="qc-table-responsive"> -->
							<?php
								foreach ($subSectionData["properties"] ?: [] as $prop)
								{
									echo $prop;
									$has_any_data_sub_ = true;
									$has_any_data = true;
								}
							?>
							<!-- </div> -->
						</div>
					</div>
					<?php
				}
				?>
			</div>
		</div>
		<?php
		
		$out_if_has_sub_ = ob_get_clean();
		if ($has_any_data_sub_)
			echo $out_if_has_sub_;
		else
			unset($out_if_has_sub_);
	}
	
	$out_if_has = ob_get_clean();
	
	if ($has_any_data)
		echo $out_if_has;
	else
		unset($out_if_has);
}
