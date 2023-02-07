<div jsFunc="renderItems($items = null)" q-args="$items = null, $from = null, $selector = null, $binds = null">
	<?php
	foreach ($binds['_props_'] ?: [] as $k => $v)
			$this->$k = $v;
	?>
	@if (!$binds || !$binds['hideNoneOption'])
		<div class="qc-dd-item qc-dd-reset-item">{{$binds['noneOptionCaption'] ?: _L('None')}}</div>
	@endif
	@if ($items)
		@each ($items as $item)
			@if ($this->_call_renderItem)
				@php call_user_func($this->_call_renderItem, $this, $item, $binds)
			@else
				@include (item, $item, $binds)
			@endif
		@endeach
	@endif
</div>