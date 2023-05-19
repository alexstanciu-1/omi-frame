<?php

if ($_TYPE_FLAGS['steps'])
{
	?>
		<div class="qc-comp-step">
			<ul class="flex mb-4 -mx-3">
				<?php
					$no = 1;
					$count = count($_TYPE_FLAGS['steps']);
					foreach ($_TYPE_FLAGS['steps'] ?: [] as $step_name => $step_cfg)
					{
						?>
							<li class="px-3 flex items-center">
								<span class="text-sm rounded-full px-3 py-1 w-8 h-8 bg-gray-300 mr-2"><?= $no ?></span>
								<a xg-step-name="<?= $step_name ?>" class="qc-submit-btn qc-save-on-tab {{(!($data && $data->getId())) ? 'qc-on-add' : ''}} bg-white <?= ($config['__view__'] === $step_name) ? ' bg-blue-500 text-white ' : '' ?> px-5 py-3 hover:border-blue-500 rounded-md block shadow font-medium bg-white border-gray-300 border text-sm" href='{{<?= var_export($step_name, true) ?>}}/{{($grid_mode === 'add') ? 'edit' : $grid_mode}}/{{$data ? $data->getId() : ''}}'>{{_L(<?= var_export($step_cfg['@caption'], true) ?>)}}</a>
								<?php if ($no != $count) : ?>
								<!-- <svg class="w-4 h-4 text-gray-500 ml-10 mr-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
									<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
								</svg> -->
								<?php endif; ?>
							</li>
						<?php
						
						$no++;
					}
				?>
			</ul>
		</div>
	<?php
	
	unset($step_name);
}
