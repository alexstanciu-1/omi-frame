<div qb="$preconfig" qArgs="$batch = null" <?= $batch ? "" : 'style="display: none;"' ?> class="batch-add-generators">
	<?php
	
		$className = $batch ? $batch["className"] : QApp::GetDataClass();
		// $batch
		$namespace_val = $batch ? $batch["namespace"] : null;
		if (!$namespace_val)
		{
			$ns_class = $className;
			$namespace_val = (($lns_val = strrpos($ns_class, "\\")) !== false) ? substr($ns_class, 0, $lns_val) : "";
		}
	
	?>
	<form autocomplete="off">
	<table>
		<tr>
			<td style='vertical-align: top;'>
				<table>
					<?php
					if ($batch)
					{
						?><tr class="show-if-type-selected">
							<td><label>Batch Id: </label></td>
							<td>
								<input qb=".batchid" readonly type="hidden" value="<?= $batch["batchid"] ?>" />
								<input qb=".newbatchid" readonly type="text" value="<?= $batch["batchid"] ?>" />
							</td>
						</tr><?php
					}
					?>
					<tr>
						<td><label>Type: </label></td>
						<td>
						<qCtrl qCtrl="QDevModeSelectCtrl" tag="classesDropDown" name="classesDropDown">
							<?php
								// If this PHP code block is not present then it is created and an instance of the control is created
								$this->addControl($classesDropDown);
								$classesDropDown->init();
								
								$this->input_default = $batch ? $batch["className"] : QApp::GetDataClass();
								
								$classesDropDown->render();
							?>
							<init qArgs="$recursive = true"><?php
								$this->drop_down = true;
								$this->input_qb = ".className";
								$this->input_default = QApp::GetDataClass();
								$this->class_filter = function ($class_name)
								{
									// check that is model and not a control
									return QModelQuery::GetTypesCache($class_name) ? true : false;
								};
								$this->url_getter = function ($tag, $class) { return null; };
							?></init>
						</qCtrl>
						</td>
					</tr>
					<!-- 
					<tr class="show-if-type-selected">
						<td><label>Caption: </label></td>
						<td><input type="text" /></td>
					</tr>
					-->
					<tr class="show-if-type-selected">
						<td></td>
						<td><input qb=".autosync" type="checkbox" value="1" <?= ((!$batch) || $batch["autosync"]) ? "checked" : "" ?> /> <label>Auto Sync Batch</label></td>
					</tr>
					<tr class="show-if-type-selected">
						<td></td>
						<td><input qb=".autosyncadd" type="checkbox" value="1" <?= ((!$batch) || $batch["autosyncadd"]) ? "checked" : "" ?> /> <label>Auto Add in Batch</label></td>
					</tr>
					<tr class="show-if-type-selected">
						<td></td>
						<td><input qb=".autosyncremove" type="checkbox" value="1" <?= ((!$batch) || $batch["autosyncremove"]) ? "checked" : "" ?> /> <label>Auto Remove from Batch</label></td>
					</tr>
					<tr class="show-if-type-selected">
						<td><label>Namespace: </label></td>
						<td><input qb=".namespace" type="text" <?= $namespace_val ? "value=\"".htmlentities($namespace_val)."\"" : "" ?> /></td>
					</tr>
					<tr class="show-if-type-selected">
						<td><label>Prefix: </label></td>
						<td><input qb=".prefix" type="text" <?= $batch ? "value=\"".htmlentities($batch["prefix"])."\"" : "" ?> /></td>
					</tr>
					<tr class="show-if-type-selected">
						<td><label>Rel Path: </label></td>
						<td><input qb=".rel_path" type="text" <?= $batch ? "value=\"".htmlentities($batch["rel_path"])."\"" : "" ?> /></td>
					</tr>
					<!--
					<tr class="show-if-type-selected">
						<th>Generate for type:</th>
						<td>
							<input type="checkbox" id="dev-mode-gen-add-page" /><label for="dev-mode-gen-add-page">Page</label><br/>
							<input type="checkbox" id="dev-mode-gen-add-controller" /><label for="dev-mode-gen-add-controller">Controller</label><br/>
							<input type="checkbox" id="dev-mode-gen-add-menu" /><label for="dev-mode-gen-add-menu">Menu</label>
							<br/><br/>
							<button>Create</button>
						</td>
					</tr>
					-->
					<tr class="show-if-type-selected">
						<th>Generate<br/>for batch:</th>
						<td qb=".batchGens[]"><?php
							foreach (\Omi\Gens\Generator::$BatchGenerators as $BatchGen)
							{
								$caption = (substr($BatchGen, 0, strlen("Omi\\Gens\\")) === "Omi\\Gens\\") ? substr($BatchGen, strlen("Omi\\Gens\\")) : $BatchGen;
								$tag = strtolower(str_replace("\\", "-", $caption));
								
								$is_checked = $batch ? in_array($BatchGen, $batch["batchGens"]) : true;
								
								?><input type="checkbox" <?= $is_checked ? "checked" : "" ?> qb="." value="<?= htmlentities($BatchGen) ?>" /><label><?= $caption ?></label><br/><?php
							}
						?></td>
					</tr>
				</table>
			</td>
			<td class="preconfig-render-properties" style='vertical-align: top;'>
				<?php
					$this->renderPreconfigProperties($className, $batch["properties"]);
				?>
			</td>
		</tr>
		<tr>
			<td style="vertical-align: top;">
				<button onclick="$ctrl(this).createBatch(this); return false;"><?= $batch ? "Save" : "Create" ?></button>
				<?php 
					if ($batch) { 
						?><button onclick="if (confirm('Are you sure you wish to remove the batch ?')) $ctrl(this).deleteBatch(this); return false;" style="color: red;">Delete</button><?php 
					} ?>
			</td>
			<td style="vertical-align: top;">
				<?php 
				if ($batch) { 
					?>
						<button onclick="$ctrl(this).syncBatch(this, 'propose'); return false;" style="color: green;">Propose</button>
						<button onclick="if (confirm('Are you sure you wish to merge the config from this batch ?')) $ctrl(this).syncBatch(this, 'merge'); return false;" style="color: blue;">Merge</button>
						<button onclick="if (confirm('Are you sure you wish to overwrite values in config from this batch ?')) $ctrl(this).syncBatch(this, 'overwrite-values'); return false;" style="color: orange;">Overwrite Values</button>
						<button onclick="if (confirm('Are you sure you wish to overwrite the config from this batch ?')) $ctrl(this).syncBatch(this, 'overwrite'); return false;" style="color: red;">Overwrite</button>
						<h5>Generate Code</h5>
						<button onclick="if (confirm('Are you sure you wish to generate and OVERWRITE existing code for this batch ?')) $ctrl(this).generateCode(this, 'overwrite'); return false;" style="color: red;">Overwrite</button>
					<?php 
				} ?>
			</td>
		</tr>
	</table>
	</form>
	<hr/>
</div>