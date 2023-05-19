<div class="py-2 px-2" style="width: 700px; min-height: 100%;">
		<!-- q-init="dev_panels_checked = []; console.log('dev_panels_checked@init', dev_panels_checked.length);" -->
		<!-- q-init="dev_panels_checked = $.live_array(dev_panels, {Expand: true})" -->
	<div	
			q-data="{dev_panels_checked: [{Name: 'Test checked'}], show_menu: false, show_logs: true}"
			q-api-data="{dev_panels: 'Dev_Panels'}">

		<nav> <!-- nav bar -->
			<span @click="show_menu = !show_menu" style="font-size: 12px; cursor: pointer;">
				<svg viewBox="0 0 20 20" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
					<g id="Page-1" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
						<g id="icon-shape">
							<path d="M0,3 L20,3 L20,5 L0,5 L0,3 Z M0,9 L20,9 L20,11 L0,11 L0,9 Z M0,15 L20,15 L20,17 L0,17 L0,15 Z" id="Combined-Shape"></path>
						</g>
					</g>
				</svg>
			</span>
		</nav>

		<div q-show="show_menu">
			<template q-each="panel in dev_panels.items">
				<div>
					<!-- <button @click="$node.toggle(true, dev_panels_checked, panel)">Switch ON</button>
					<button @click="$node.toggle(false, dev_panels_checked, panel)">Switch OFF</button> -->
					<label :id="panel.Name" class="block">
						<input class="mr-2 leading-tight" type="checkbox" q-model='panel.Expand' />
						<span class="text-sm" q-text="panel.Name">n/a</span>
					</label>
					<hr/>
				</div>
			</template>
		</div>
		<!--
		<hr/>

		<template q-each="panel_chk in dev_panels_checked">
			<div q-text="'dev_panels_checked: ' + panel_chk.Name">
			</div>
		</template>

		<hr/>

		<template q-each="panel_chk in dev_panels">
			<div>
				<template q-if='panel_chk.Expand'>
					<div q-text="panel_chk.Name">
					</div>
				</template>
			</div>
		</template>

		<hr/>
		-->

		<template q-if='dev_panels.items.find(e => (e.Name === "Model" && e.Expand))'>
			<div>
				this is the model
			</div>
		</template>
		<template q-if='dev_panels.items.find(e => (e.Name === "View" && e.Expand))'>
			<div q-data="{show: true, data: {}}"
					q-api-data="{data: 'UI_Views'}">
				<h2><span @click="show = !show" q-text="show ? '[-]' : '[+]'"></span>UI Views <span q-text=" '(' + (data ? data.items.length : 0) + ')' "></span></h2>
				<div q-show="show">
					<table>
						<tr>
							<th>Name</th>
							<th>Namespace</th>
						</tr>
						<tr>
							<td><input type="text" q-model="data.filter.Name" value="" /></td>
							<td><input type="text" q-model="data.filter.Namespace" value="" /></td>
						</tr>
						<template q-each="item in data.items">
							<tbody>
								<tr>
									<td><span @click="item.$populate(); item._expand = item._expand ? false : true" q-text="item._expand ? '[-]' : '[+]'"></span></td>
									<td q-text="item.ShortName"></td>
									<td><small q-text="item.Namespace"></small></td>
								</tr>
								<template q-if='item._expand'>
									<!-- <tr q-data='{item_data: {filter: {Name: "Custom"}}}' q-api-data="{item_data: 'UI_Views'}"> -->
									<tr>
										<td></td>
										<!-- <td rowspan="2" q-text="item_data.items[0].Name"></td> -->
										<td rowspan="2" q-text="item.FakePopulate"></td>
									</tr>
								</template>
							</tbody>
						</template>
					</table>
				</div>
			</div>
		</template>
		<template q-if='dev_panels.items.find(e => (e.Name === "Controller" && e.Expand))'>
			<div>
				this is the controller
			</div>
		</template>
		<template q-if='dev_panels.items.find(e => (e.Name === "Services" && e.Expand))'>
			<div>
				this is the services
			</div>
		</template>

		<template q-each="panel_xyz in dev_panels.items.filter(panel_xyz => panel_xyz.Expand)">
			<div q-text="panel_xyz.Name">
			</div>
		</template>

		<hr/>

		<button @click="show_logs = !show_logs">Toggle</button>
		<div id="dev-console" q-show="show_logs" style="border: 1px solid red;">
			<h5>Logs</h5>
		</div>
	</div>

</div>