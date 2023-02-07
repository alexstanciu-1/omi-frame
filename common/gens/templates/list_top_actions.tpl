@code
	$user = \QApi::Call('\Omi\User::GetCurrentUser');
	$dataCls = \QApp::GetDataClass();
@endcode
<div class='qc-top-actions m-bottom-1'>
	@if (!$dataCls::$_USE_SECURITY_FILTERS || ($user && $user->can('add', '<?= $config['__view__'] ?>')))
		<a href="{{$this->getUrlForTag('add')}}" class="qc-add-btn btn btn-success btn-border">
			{{_T('5aeae37641dc5', 'New')}}
		</a>
	@endif
	@if (!$dataCls::$_USE_SECURITY_FILTERS || ($user && $user->can('export', '<?= $config['__view__'] ?>')))
		<div class="export-buttons-v2">
			<a href="javascript: void(0);" class="btn btn-border btn-warning export-dropdown">
				{{_L("Export")}}
				<i class="fa fa-angle-down"></i>
			</a>
			<ul class="export-buttons-dd">
				@if ((!$dataCls::$_USE_SECURITY_FILTERS || ($user && $user->can('export_pdf', '<?= $config['__view__'] ?>'))) && $this->can_export_pdf)
					<li>
						<a title='{{_L("Export to PDF")}}' target="_blank" href="{{$this->getUrlForTag('pdf')}}" class="qc-export-pdf qc-export-binds">
							{{_L("Export to PDF")}}
						</a>
					</li>
				@endif
				@if ((!$dataCls::$_USE_SECURITY_FILTERS || ($user && $user->can('export_excel', '<?= $config['__view__'] ?>'))) && $this->can_export_excel)
					<li>
						<a title='{{_L("Export to EXCEL")}}' target="_blank" href="{{$this->getUrlForTag('excel')}}" class="qc-export-excel qc-export-binds">
							{{_L("Export to EXCEL")}}
						</a>
					</li>
				@endif
				@if ((!$dataCls::$_USE_SECURITY_FILTERS || ($user && $user->can('export_csv', '<?= $config['__view__'] ?>'))) && $this->can_export_csv)
					<li>
						<a title='{{_L("Export to CSV")}}' target="_blank" href="{{$this->getUrlForTag('csv')}}" class="qc-export-csv qc-export-binds">
							{{_L("Export to CSV")}}
						</a>
					</li>
				@endif
				@if ((!$dataCls::$_USE_SECURITY_FILTERS || ($user && $user->can('import_csv', '<?= $config['__view__'] ?>'))) && $this->can_import_from_csv)
					<li>
						<a title='{{_L("Import from CSV file")}}' href="javascript: void(0);" class="qc-import">
							{{_L("Import from CSV file")}}
						</a>
					</li>
				@endif
				@if (!$dataCls::$_USE_SECURITY_FILTERS || ($user && $user->can('print', '<?= $config['__view__'] ?>')))
					<!--
					<li>
						<a title='{{_L("Print")}}' target="_blank" href="{{$this->getUrlForTag('excel')}}" href="javascript: void(0);" class="qc-print">
							{{_L("Print")}}
						</a>
					</li>
					-->
				@endif
			</ul>
		</div>
	@endif

	@if ((!$dataCls::$_USE_SECURITY_FILTERS || ($user && $user->can('provisioning_sync', 'Broadworks_Groups'))) && $this->provisioning_can_sync)
		<div class='export-buttons provisioning-sync-buttons'>
			@if ((!$dataCls::$_USE_SECURITY_FILTERS || ($user && $user->can('provisioning_sync_push', 'Broadworks_Groups'))) && $this->provisioning_can_push)
				<a title='{{_L("Push")}}' target="_blank" data-type="push" class="btn qc-provisioning-sync qc-tooltip tooltip-bottom">
					<i class="fa fa-upload info"></i>
					<span class="tooltip">Push</span>
					Push
				</a>
			@endif
			@if ((!$dataCls::$_USE_SECURITY_FILTERS || ($user && $user->can('provisioning_sync_pull', 'Broadworks_Groups'))) && $this->provisioning_can_pull)
				<a title='{{_L("Pull")}}' data-type="pull" class="btn qc-provisioning-sync qc-tooltip tooltip-bottom">
					<i class="fa fa-download success"></i>
					<span class="tooltip">Pull</span>
					Pull
				</a>
			@endif
		</div>
	@endif

	<?php if ($config && $config['__settings__'] && $config['__settings__']['bulk'] && $config['__settings__']['bulk']['show_bulk_switcher']) : ?>
		@if ($this->grid_mode === "list")
			<a title='{{_L("Edit")}}' href="{{$this->getUrlForTag('mode', 'bulk')}}" class="btn qc-bulk-btn btn-border btn-info">
				Edit
			</a>
		@else
			<a title='{{_L("List")}}' href="{{$this->getUrlForTag('mode', 'list')}}" class="btn qc-list-btn btn-border btn-warning">
				List
			</a>
		@endif			
	<?php endif; ?>
</div>