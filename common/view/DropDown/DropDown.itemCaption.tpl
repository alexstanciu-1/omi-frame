<virtual
	jsFunc="renderItem($items = null)" 
	q-args="$item = null, $binds = null">
	{{ $item->getModelCaption($binds ? $binds["__caption_tag"] : null) }}
</virtual>