@php $this->renderSideMenu();

<?php
if ($__search_fields || $oby_data) : ?>
	<form action='{{$this->url()}}' class="row qc-advanced-search js-advanced-search">
	<?php if ($__search_fields) : ?>
		<h5>Filters</h5>
			@code
				$_has_search = false;
				if ($bind_params && (count($bind_params) > 0))
				{
					foreach ($bind_params as $_bpk => $bpv)
					{
						if ((substr($_bpk, 0, strlen("QINSEARCH")) === "QINSEARCH") && $bpv)
						{
							$_has_search = true;
							break;
						}
					}
				}
			@endcode

		<div class="row adv-search">
			<?php foreach ($__search_fields as $_fieldData) :
				list($property, $caption, $field, $bind_val_index, $operation, $input_name, $property, $parent_model) = $_fieldData;
				?>
				<div class="search-input{{$bind_params<?= $bind_val_index ?> ? ' toggled' : ''}}">
					<?= $field ?>
					<label qc-search-label='<?= $property ?>'>
						{{_L('<?= $caption ?>')}}
					</label>
				</div>
			<?php endforeach; ?>
			<button class="btn btn-default qc-tooltip tooltip-right qc-search-btn" onclick="return false;"><fa class="fa fa-search"></fa><span class="tooltip">Search</span></button>
			<a href='{{$this->url()}}' class="btn btn-default qc-tooltip tooltip-left float-right"><fa class="fa fa-times"></fa><span class="tooltip">Reset</span></a>
		</div>

	<?php endif; ?>
	<?php if ($oby_data) :
	foreach ($oby_data as $oby) : 
		list($input_name, $bind_val_index) = $oby; ?>
		<input sync-identifier='<?= $input_name ?>' class="js-oby-hidden js-keepin-sync" type='hidden' name='<?= $input_name ?>' value='{{$bind_params<?= $bind_val_index ?>}}' />
	<?php 
		endforeach; 
		endif; ?>
	</form>
<?php endif; ?>