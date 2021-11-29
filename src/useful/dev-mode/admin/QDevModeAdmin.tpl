<div class="QDevModeAdmin">
	
	<h2>Admin</h2>
	<!-- $from, $selector, $binds, $caption, $input_id_name, $input_id_default, $input_type_name, $input_type_default -->
	@include (\Omi\View\DropDown, "Countries", "Id,Name", [], null, "Id", null, "_ty", null)
	<!--
	<qControl class="\Omi\View\DropDown">
		<renderItems q-args="$items">
			<ul>
			</ul>
		</renderItems>
	</qControl>
	-->
	<?php
	/*
		$dd = new \Omi\View\DropDown();
		$dd->init();
		$dd->render();
	*/
	?>
	<div style="display: flex;">
		<div class="col" style="padding-top: 20px; padding-right: 35px; border-right: 1px solid #7080ac; margin-right: 20px;">
			<ul class="classesList">
			@each ($this->getProperties() as $prop)
				<li>
					<a href="{{qUrl('adminitem', $prop)}}">{{$prop}}</a>
				</li>
			@endeach
			</ul>
			<button onclick="$ctrl(this).call('SyncAdmin', [<?= ($this->showProperty ? "'".qaddslashes($this->showProperty)."'" : "") ?>])">SyncAdmin</button>
		</div>

		<div class="col">
			@if ($this->content)
				@php $this->content->render();
			@endif
		</div>
	</div>
	
	<div style="clear: both;">
		<hr />
		@php echo $this->sync_output;
	</div>
	
</div>