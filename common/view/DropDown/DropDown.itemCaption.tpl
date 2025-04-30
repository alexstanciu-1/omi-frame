<virtual
	jsFunc="renderItem($items = null)" 
	q-args="$item = null, $binds = null"><?php 
		$html_items = $binds['html-items'] ?? false;
		if ($html_items) { 
			echo $item->getModelCaption($binds ? $binds["__caption_tag"] : null); 
		} else { 
		?>{{ $item->getModelCaption($binds ? $binds["__caption_tag"] : null) }}<?php } ?>
</virtual>