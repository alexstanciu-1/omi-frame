<div
	jsFunc="renderItem($items = null)" 
	q-args="$item = null, $binds = null"
	data-full='<?= htmlspecialchars(json_encode($item->toJSON()), ENT_QUOTES) ?>' 
	class="qc-dd-item block px-4 py-2 text-sm leading-5 text-gray-700 hover:bg-gray-100 hover:text-gray-900 focus:outline-none focus:bg-gray-100 focus:text-gray-900 cursor-pointer" 
	item.id="{{$item->Id}}" item.ty="{{q_get_class($item)}}">
	
	@include (itemCaption, $item, $binds)
</div>