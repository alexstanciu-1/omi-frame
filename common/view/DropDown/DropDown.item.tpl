<div
	jsFunc="renderItem($items = null)" 
	q-args="$item = null, $binds = null"
	data-full='<?= htmlspecialchars(json_encode($item->toJSON()), ENT_QUOTES) ?>' 
	class="qc-dd-item" 
	item.id="{{$item->Id}}" item.ty="{{get_class($item)}}">
	
	@include (itemCaption, $item, $binds)
</div>