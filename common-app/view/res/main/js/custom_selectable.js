
/**

needs events: 
	select start
	select end
	select reset
	
 */

window.qqs = {};

function qq_select_click($element, $box, $nodes, $mouse_over, $ending_event, $starting_event)
{
	var $id = $box.dataset.qqsId;
	var $obj = window.qqs[$box.dataset.qqsId];
	
	if (!$obj)
	{
		window.qqs[$box.dataset.qqsId] = $obj = {};
		$obj.box = $box;
		$obj.id = $box.dataset.qqsId;
		$obj.get_selected = function ($nodes)
		{
			if (!$obj.first)
				return null;
			$nodes = $nodes ? $nodes : this.box.querySelectorAll(this.box.dataset.qqsSelector);
			var $ret_nodes = [];
			for (var $i = 0; $i < $nodes.length; $i++)
			{
				if ($nodes[$i].classList.contains('qqs-selected'))
					$ret_nodes.push($nodes[$i]);
			}
		
			return $ret_nodes;
		};
	}
	
	// console.log('qq_select_click::start', $obj);
	$nodes = $nodes ? $nodes : $obj.box.querySelectorAll($obj.box.dataset.qqsSelector);
		
	if ((!$obj.first) || ($starting_event && $obj.first && $obj.last))
	{
		if ($mouse_over || $ending_event)
			return;
		
		qq_select_reset();
		$obj.first = $element;
		$element.classList.add('qqs-first', 'qqs-selected');
		window.qqs.live = $obj;
		
		window.document.dispatchEvent(new Event('qqs-start', {'id': $obj.id, 'obj': $obj, 'element': $element, 'nodes': $nodes}));
	}
	else if ($obj.first && (!$obj.last))
	{
		/*if (($obj.first === $element) && (!$mouse_over))
		{
			// reset selection
			qq_select_reset($id, $obj);
		}
		*/
		// else
		{
			// determine who is first , swap them if needed !
			
			// all the elements in between are to be set to selected
			//var $nodes = $nodes ? $nodes : $obj.box.querySelectorAll($obj.box.dataset.qqsSelector);
			//var $after_first = false;
			//var $after_last = false;
			var $flag = false;
			
			var $tmp_first = null;
			
			for (var $i = 0; $i < $nodes.length; $i++)
			{
				var $node = $nodes[$i];
				var $flag_this = false;
				
				if (!$tmp_first)
				{
					if ($node === $obj.first)
					{
						if ($node === $element)
							$flag = false;
						else
						{
							$tmp_first = $obj.first;
							$flag = true;
						}
					}
					else if ($node === $element)
					{
						$tmp_first = $element;
						$flag = true;
					}
				}
				else if (($node === $obj.first) || ($node === $element))
				{
					$flag = false;
					if ($mouse_over)
						$flag_this = true;
				}
				
				if ($flag || $flag_this)
					$node.classList.add('qqs-selected');
				else
					$node.classList.remove('qqs-selected');
				
			}
			
			if (!$mouse_over)
			{
				if ($tmp_first === $element)
				{
					$obj.last = $obj.first;
					$obj.first = $element;
					// we make a swap
					$obj.last.classList.remove('qqs-first');
					$obj.first.classList.add('qqs-first');
				}
				else
					$obj.last = $element;
				$obj.last.classList.add('qqs-last', 'qqs-selected');
				window.qqs.live = null; // detaching
				
				window.document.dispatchEvent(new CustomEvent('qqs-end', {'detail':{'id': $obj.id, 'obj': $obj, 'element': $element, 'nodes': $nodes}}));
			}
			else
				window.document.dispatchEvent(new Event('qqs-move', {'id': $obj.id, 'obj': $obj, 'element': $element, 'nodes': $nodes}));
		}
	}
	else if ($obj.first && $obj.last)
	{
		qq_select_reset($id, $obj);
	}
	
	// console.log('qq_select_click::end', $obj);
}

function qq_select_reset($qqs_id, $obj)
{
	if ((!$qqs_id) && (!$obj))
	{
		// reset all
		for (var $k in window.qqs)
			qq_select_reset($k, window.qqs[$k]);
	}
	
	$obj = $obj ? $obj : window.qqs[$qqs_id];
	if (!$obj)
		return;
	var $nodes = $obj.box.querySelectorAll($obj.box.dataset.qqsSelector);
	
	window.document.dispatchEvent(new Event('qqs-reset', {'id': $obj.id, 'obj': $obj, 'nodes': $nodes}));
	
	for (var $i = 0; $i < $nodes.length; $i++)
		$nodes[$i].classList.remove('qqs-selected', 'qqs-first', 'qqs-last');
	$obj.first = null;
	$obj.last = null;
	window.qqs.live = null; // detaching
}

/**
 * @param {Event} $event
 * @returns {undefined}
 */
function qq_onevent($event)
{
	var $mouse_over = false;
	var $ending_event = false;
	var $starting_event = false;
	if (($event.type === "mouseover") || ($event.type === "dragover") || ($event.type === "touchmove"))
	{
		if (!window.qqs.live)
			return;
		var $mouse_over = true;
	}
	else if (($event.type === "mouseup") || ($event.type === "touchend"))
		$ending_event = true;
	else if (($event.type === "mousedown") || ($event.type === "touchstart"))
		$starting_event = true;
	
	/**
	 * @type Element
	 */
	var $target = $event.target;
	// console.log($target.classList);
	if ($target.classList.contains('qqs'))
	{
		qq_select_click($target);
	}
	else
	{
		var $box = $target.closest(".qqs-box");
		if ($box)
		{
			var $nodes = $box.querySelectorAll($box.dataset.qqsSelector);
			for (var $i = 0; $i < $nodes.length; $i++)
			{
				if (($nodes[$i] === $target) || $nodes[$i].contains($target))
				{
					qq_select_click($nodes[$i], $box, $nodes, $mouse_over, $ending_event, $starting_event);
					break;
				}
			}
		}
	}
}

window.document.addEventListener("click", qq_onevent);
window.document.addEventListener("mouseover", qq_onevent);

/*
window.document.addEventListener("mouseover", qq_onevent);

window.document.addEventListener("mousedown", qq_onevent);
window.document.addEventListener("mouseup", qq_onevent);

window.document.addEventListener("touchstart", qq_onevent);
window.document.addEventListener("touchend", qq_onevent);
// window.document.addEventListener("touchcancel", handleCancel, false);
window.document.addEventListener("touchmove", qq_onevent);
// window.document.addEventListener("dragover", qq_onevent);

*/
