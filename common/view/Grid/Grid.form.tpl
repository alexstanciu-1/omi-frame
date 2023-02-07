<div class="panel" q-args="$settings = null, $data = null, $bind_params = null, $grid_mode = null, $id = null, $vars_path = '', $_qengine_args = null">
	<div class="panel-header">
		<h2>{{$settings["heading:title"]}}</h2>
	</div>
	<a href="{{\QUrl::WithQueryString(['__gm' => 'add'])}}"><i class="fa fa-plus"></i> NEW</a>
	<form action="{{\QUrl::WithQueryString()}}" method="GET">
		<input type="text" placeholder="Search" /><i class="fa fa-search"></i>
	</form>
</div>
