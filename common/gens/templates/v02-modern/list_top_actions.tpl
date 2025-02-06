@code
	$user = \Omi\User::GetCurrentUser();
	$dataCls = \QApp::GetDataClass();
@endcode
<div class='qc-top-actions'>
	<?php
		$search_metadata = $config["cfg"]["::"]['@search'] ?? [];
	?>

<div class="mt-5 flex lg:mt-0 lg:ml-4 tpl-top-actions-buttons">
	<?php if ($_TYPE_FLAGS['list_checkboxes']) { ?>
	@if (!$dataCls::$_USE_SECURITY_FILTERS || ($user && $user->can('edit', '<?= $config['__view__'] ?>')) || ($user && $user->can('delete', '<?= $config['__view__'] ?>')))
		<div class="export-buttons-v2">
			<a href="javascript: void(0);" class="btn btn-border btn-info export-dropdown">
				{{_L("With selected")}}
			</a>
			<ul class="export-buttons-dd right-align->ca">
				@if (false && (!$dataCls::$_USE_SECURITY_FILTERS || ($user && $user->can('delete', '<?= $config['__view__'] ?>'))))
					<li>
						<a title='{{_L("Delete")}}' class="js-click qc-withselected" data-js-action="with-selected-delete">
							<span class="_tblack">{{_L("Delete")}}</span>
						</a>
					</li>
				@endif
				@if (false && (!$dataCls::$_USE_SECURITY_FILTERS || ($user && $user->can('edit', '<?= $config['__view__'] ?>'))))
					<li>
						<a title='{{_L("Bulk set values")}}' class="js-click qc-withselected" data-js-action="with-selected-set-values">
							<span class="_tblack">{{_L("Bulk set values")}}</span>
						</a>
					</li>
				@endif
				@if ((!$dataCls::$_USE_SECURITY_FILTERS || ($user && $user->can('edit', '<?= $config['__view__'] ?>'))))
					<li>
						<a title='{{_L("Upgrade")}}' class="js-click qc-withselected js-with-selected-tf-upgrade" data-js-action="with-selected-tf-upgrade">
							<span class="_tblack">{{_L("Upgrade")}}</span>
						</a>
					</li>
				@endif
			</ul>
		</div>
	@endif
	<?php } ?>
	
    @if (!$dataCls::$_USE_SECURITY_FILTERS || ($user && $user->can('add', '<?= $config['__view__'] ?>')))
        <span class="block shadow-sm rounded-md">
            <a xg-security="'add', $settings['model:property'], $vars_path, $data" href="{{$this->getUrlForTag('add')}}" class="qc-add-btn inline-flex items-center px-4 py-2 border border-indigo-300 text-sm leading-5 font-medium rounded-md text-indigo-700 bg-white hover:text-indigo-500 focus:outline-none focus:shadow-outline-indigo focus:border-indigo-300 active:text-indigo-800 active:bg-indigo-50 transition duration-150 ease-in-out">{{_T('5aeae37641dc5', 'New')}}</a>
        </span>
    @endif
    
    @if (!$dataCls::$_USE_SECURITY_FILTERS || ($user && $user->can('export', '<?= $config['__view__'] ?>')))
		<!-- 
        @if ((!$dataCls::$_USE_SECURITY_FILTERS || ($user && $user->can('export_pdf', '<?= $config['__view__'] ?>'))) && $this->can_export_pdf)
            <span class="block ml-3 shadow-sm rounded-md">
                <a title='{{_L("Export to PDF")}}' target="_blank" href="{{$this->getUrlForTag('pdf')}}" class="qc-export-pdf inline-flex items-center px-4 py-2 border border-gray-300 text-sm leading-5 font-medium rounded-md text-gray-700 bg-white hover:text-gray-500 focus:outline-none focus:shadow-outline-blue focus:border-blue-300 active:text-gray-800 active:bg-gray-50 active:text-gray-800 transition duration-150 ease-in-out">
                    {{_L("Export to PDF")}}
                </a>
            </span>
        @endif
        
        @if ((!$dataCls::$_USE_SECURITY_FILTERS || ($user && $user->can('export_excel', '<?= $config['__view__'] ?>'))) && $this->can_export_excel)
            <span class="block ml-3 shadow-sm rounded-md">
                <a title='{{_L("Export to EXCEL")}}' target="_blank" href="{{$this->getUrlForTag('excel')}}" class="qc-export-excel inline-flex items-center px-4 py-2 border border-gray-300 text-sm leading-5 font-medium rounded-md text-gray-700 bg-white hover:text-gray-500 focus:outline-none focus:shadow-outline-blue focus:border-blue-300 active:text-gray-800 active:bg-gray-50 active:text-gray-800 transition duration-150 ease-in-out">
                    {{_L("Export to EXCEL")}}
                </a>
            </span>
        @endif
		-->
        @if (false)
        <div class="export-buttons-v2">
            <ul class="export-buttons-dd right-align">
                @if ((!$dataCls::$_USE_SECURITY_FILTERS || ($user && $user->can('export_csv', '<?= $config['__view__'] ?>'))) && $this->can_export_csv)
                    <li>
                        <a title='{{_L("Export to CSV")}}' target="_blank" href="{{$this->getUrlForTag('csv')}}" class="qc-export-csv">
                            <span class="_tblack">{{_L("Export to CSV")}}</span>
                        </a>
                    </li>
                @endif
                @if ((!$dataCls::$_USE_SECURITY_FILTERS || ($user && $user->can('import_csv', '<?= $config['__view__'] ?>'))) && $this->can_import_from_csv)
                    <li>
                        <a title='{{_L("Import from CSV file")}}' target="_blank" href="javascript: void(0);" class="qc-import">
                            <span class="_tblack">{{_L("Import from CSV file")}}</span>
                        </a>
                    </li>
                @endif
                @if (!$dataCls::$_USE_SECURITY_FILTERS || ($user && $user->can('print', '<?= $config['__view__'] ?>')))
                    <!--
                    <li>
                            <a title='{{_L("Print")}}' target="_blank" href="{{$this->getUrlForTag('excel')}}" href="javascript: void(0);" class="qc-print">
                                    Print
                            </a>
                    </li>
                    -->
                @endif
            </ul>
		</div>
		<?php if ($search_metadata) { ?>
			<a href="javascript://" onclick="jQuery(this).closest('.qc-inner').find('.js-list-wrapper').toggleClass('col-md-9'); jQuery(this).closest('.qc-inner').find('.js-shrc-ctrl').toggle()" class="btn btn-warning btn-border">{{_L("Filter")}}</a>
		<?php } ?>
	@endif

	@if ((!$dataCls::$_USE_SECURITY_FILTERS || ($user && $user->can('provisioning_sync', 'Broadworks_Groups'))) && $this->provisioning_can_sync)
		<div class='export-buttons provisioning-sync-buttons'>
			@if ((!$dataCls::$_USE_SECURITY_FILTERS || ($user && $user->can('provisioning_sync_push', 'Broadworks_Groups'))) && $this->provisioning_can_push)
				<a title='{{_L("Push")}}' target="_blank" data-type="push" class="btn qc-provisioning-sync qc-tooltip tooltip-bottom">
					<i class="fa fa-upload info"></i>
					<span class="tooltip">{{_L('Push')}}</span>
					{{_L('Push')}}
				</a>
			@endif
			@if ((!$dataCls::$_USE_SECURITY_FILTERS || ($user && $user->can('provisioning_sync_pull', 'Broadworks_Groups'))) && $this->provisioning_can_pull)
				<a title='{{_L("Pull")}}' data-type="pull" class="btn qc-provisioning-sync qc-tooltip tooltip-bottom">
					<i class="fa fa-download success"></i>
					<span class="tooltip">{{_L('Pull')}}</span>
					{{_L('Pull')}}
				</a>
			@endif
		</div>
	@endif

	<?php if ($config && $config['__settings__'] && $config['__settings__']['bulk'] && $config['__settings__']['bulk']['show_bulk_switcher']) : ?>
		@if ($this->grid_mode === "list")
			<a title='{{_L("Edit")}}' href="{{$this->getUrlForTag('mode', 'bulk')}}" class="btn qc-bulk-btn btn-border btn-info">
				{{_T('5aeae3f73f915', 'Edit')}}
			</a>
		@else
			<a title='{{_L("List")}}' href="{{$this->getUrlForTag('mode', 'list')}}" class="btn qc-list-btn btn-border btn-warning">
				{{_T('5aeae40bb1664', 'List')}}
			</a>
		@endif			
	<?php endif; ?>
</div>