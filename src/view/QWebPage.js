
// this is a very useful way to hide popups and drop downs on click away

// jQuery(document.body).click(qHideOnClickAway);

jQuery(document.body).click(function ($event)
{
	// try to fix hide on click away here
	// var $search_within = jQuery(e.target).is('.qHideOnClickAway, .q-hide-on-click-away');
	// if (!$node.contains($event.target))
		// $node.__ctx.dom_event($event, $evs[$i][2]);
	var $items = jQuery(".qHideOnClickAway, .q-hide-on-click-away");
	
	var $popup_containers = $items.filter('.js-popup-container');
	var $remove_popup = null;
	if ($popup_containers.last().length > 0)
	{
		// if we have more than one ... we will only close the last one
		$remove_popup = $popup_containers.last()[0];
	}

	var $targetJq = jQuery($event.target);
	
	for (var $i = 0; $i < $items.length; $i++)
	{
		var $node = $items[$i];
		var $skipTarget = 'qSkipHideOnClickAway';
		
		if ($remove_popup && (jQuery($items[$i]).hasClass('js-popup-container')) && ($remove_popup !== $node))
			continue;
		
		if (!$node.contains($event.target) && (!$targetJq.hasClass($skipTarget)))
		{
			var $node_jq = jQuery($node);
			var $container = $node_jq.find(".qHideOnClickAway-container, .q-hide-on-click-away-container").not(
					$node_jq.find(".qHideOnClickAway .qHideOnClickAway-container, .q-hide-on-click-away .q-hide-on-click-away-container"));
		
			if ($container && $container.length)
			{
				if ($container.hasClass("qHideOnClickAway-remove") || $container.hasClass("q-hide-on-click-away-remove"))
					$container.remove();
				else
					$container.hide();
			}
			else
			{
				// qHideOnClickAway-remove qHideOnClickAway-parent
				var $possible_parent = $node_jq.closest(".qHideOnClickAway-parent, .q-hide-on-click-away-parent");
				var $first;
				
				if ($possible_parent && $possible_parent.length && 
						($first = $possible_parent.find('.qHideOnClickAway, .q-hide-on-click-away').first()) && ($first.length === 1) &&
						($first[0] === $node))
				{
					if ($possible_parent.hasClass("qHideOnClickAway-remove") || $possible_parent.hasClass("q-hide-on-click-away-remove"))
						$possible_parent.remove();
					else
						$possible_parent.hide();
					
					jQuery('body').trigger('on-qHideOnClickAway-remove');
				}
				else
				{
					if ($node_jq.hasClass("qHideOnClickAway-remove") || $node_jq.hasClass("q-hide-on-click-away-remove"))
						$node_jq.remove();
					else
						$node_jq.hide();					
				}
			}
		}
	}
});

jQuery(document).ready(function ()
{
	if (window["__MultiResponseId"])
		jQuery(window).trigger("MultiResponse", [window["__MultiResponseId"]]);
});


/* this can be anoying !!
jQuery(document).keyup(function (e)
{
	if (e.keyCode === 27) // esc
	{
		qHideOnClickAway(e);
	}
});*/

QExtendClass("QWebPage", "QWebControl",
{
	indexedViews : null,
	
	wpCtrl : null,

	setView : function (view)
	{
		if (!this.indexedViews)
			this.indexedViews = {};
		this.indexedViews[view._id] = view;
	},

	isWebPage: function()
	{
		return true;
	},

	getViewById : function (id)
	{
		return (this.indexedViews && this.indexedViews[id]) ? this.indexedViews[id] : null;
	}
});
