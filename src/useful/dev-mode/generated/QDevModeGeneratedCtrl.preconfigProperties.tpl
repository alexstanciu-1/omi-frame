<div qArgs="$class_name, $batch_props = null">
	<?php
	
		// $batch_props
	
	?>
	<table qb=".properties[]">
		<tr>
			<th></th>
			<th>Property</th>
			<th>Type</th>
			<th colspan="3">Generate</th>
			<th colspan="2">Include in</th>
		</tr>
	<?php
		foreach ($this->getPropertiesForClass($class_name) as $name => $property)
		{
			$id = "id_".uniqid();
			$is_collection = $property->hasCollectionType();
			
			$is_checked = $batch_props ? $batch_props[$name]["include"] : true;
			
			?><tr qb=".<?= $name ?>">
				<td><input qb=".include" type="checkbox" value="<?= $name ?>" <?= $is_checked ? "checked" : "" ?> id="<?= $id ?>" /></td>
				<td><label for="<?= $id ?>"><?= $name ?></label></td>
				<td><?= $property->types ?></td>
				<?php
					foreach (\Omi\Gens\Generator::$Generators as $Generator)
					{
						$uid = "id_chk_".uniqid();
						$caption = (substr($Generator, 0, strlen("Omi\\Gens\\")) === "Omi\\Gens\\") ? substr($Generator, strlen("Omi\\Gens\\")) : $Generator;
						
						$checked = $batch_props ? in_array($Generator, $batch_props[$name]["gens"]) : ($is_collection ? true : (!$Generator::$ForCollection));
						$g_name = str_replace("\\", "_", $Generator);
						
						?><td style="padding-right: 10px;"><input type="checkbox" qb=".gens[]." value="<?= $Generator ?>" id="<?= $uid ?>" <?= $checked ? "checked" : "" ?> /><label for="<?= $uid ?>"><?= $caption ?></label></td><?php
					}
					?><td style="padding-right: 25px;"></td><?php
					foreach (\Omi\Gens\Generator::$BatchGenerators as $Generator)
					{
						$uid = "id_chk_".uniqid();
						$caption = (substr($Generator, 0, strlen("Omi\\Gens\\")) === "Omi\\Gens\\") ? substr($Generator, strlen("Omi\\Gens\\")) : $Generator;
						
						$checked = $batch_props ? ($batch_props[$name]["gens_incl"] && in_array($Generator, $batch_props[$name]["gens_incl"])) : true;
						$g_name = str_replace("\\", "_", $Generator);
						
						?><td style="padding-right: 10px;"><input type="checkbox" qb=".gens_incl[]." value="<?= $Generator ?>" id="<?= $uid ?>" <?= $checked ? "checked" : "" ?> /><label for="<?= $uid ?>"><?= $caption ?></label></td><?php
					}
				?>
				
			</tr><?php
		}
	?>
	</table>
<div>