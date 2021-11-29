<div class="QDevModeModelCtrl">
	<h3>Data Model</h3>
	
	<div class='searchable_heading'><h5><?= $this->showClass ?></h5>
		<qCtrl qCtrl="QDevModeSelectCtrl" tag="classesDropDown" name="classesDropDown">
			<?php
				// If this PHP code block is not present then it is created and an instance of the control is created
				$this->addControl($classesDropDown);
				$classesDropDown->init();
				$classesDropDown->render();
			?>
			<init qArgs="$recursive = true"><?php
				$this->url_tag = "modelitem";
				$this->drop_down = true;
				$this->class_filter = function ($class_name)
				{
					// check that is model and not a control
					return QModelQuery::GetTypesCache($class_name) ? true : false;
				};
			?></init>
		</qCtrl>
		<br/><br/>
	</div>
	<table>
		<thead>
			<tr>
				<th>Properties</th>
				<th>Type</th>
				<th>Type<br/>table</th>
				<th>Scalar<br/>column</th>
				<th>Reference<br/>column</th>
				<th>Type<br/>column</th>
				<th>Collection<br/>table</th>
				<th>Collection<br/>type</th>
				<th>Collection<br/>backref</th>
				<th>Collection<br/>forwardref</th>
				<th>Collection<br/>forwardtype<br/>column</th>
			</tr>
			<!-- ["typetab", "scalarcol", "refcol", "typecol", "colltab", "colltype", "collback", "collfwd", "collfwd_ty"];-->
		</thead>
		<tbody>
		<?php
			$this->getPrintData();
		?>
		</tbody>
	</table>
</div>