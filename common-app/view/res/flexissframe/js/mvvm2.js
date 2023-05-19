
// @todo - remove jQuery dependency !!!

window.omi2 = {
	
	_InitGlobal: false,
	_objects: {},
	_watch: {},
	__bind_count: 0,
	
	control: function ($name, $extends, $def)
	{
		if ($def === undefined)
		{
			$def = $extends;
			$extends = null;
		}

		$name = $name || $def.name;
		if (!$name)
		{
			console.error("Missing name");
			return null;
		}
		
		// ensure name
		$def.name = $name;
		if (!$def.self)
			$def.self = "." + $name;
		// @todo: implement inheritance, make it an object
		// @todo: delay load if inherited element is not loaded
		omi2._objects[$name] = $def;
		
		var $binds = $def.binds;
		if ($binds)
		{
			var $len = $binds.length;
			for (var $i = 0; $i < $len; $i++)
			{
				var $bind = $binds[$i];
				var $watch = $bind.watch;
				if ($watch)
				{
					for (var $key in $watch)
						omi2.setWatch($key, $watch[$key], $bind, $def);
				}
			}
		}
	},
	
	setWatch: function($events, $value, $bind, $control)
	{
		$ev = $events.split(/\s*,\s*/);
		// alert($ev[1]);
		var $len = $ev.length;
		for (var $i = 0; $i < $len; $i++)
		{
			var $ev_type = $ev[$i];
			var $watch = omi2._watch[$ev_type] || (omi2._watch[$ev_type] = {});
			
			// make it simple for now
			var $watch_event_selectors = $watch.e || ($watch.e = []);
			var $watch_binds = $watch.b || ($watch.b = []);
			var $watch_ctrls = $watch.c || ($watch.c = []);
			
			var $b_index = $watch_binds.indexOf($bind);
			if ($b_index < 0)
				$b_index = ($watch_binds.push($bind) - 1);
			$watch_event_selectors[$b_index] = (typeof($value) === "function") ? $value : ($value ? ($control.self + " " + $value) : $control.self);
			$watch_ctrls[$b_index] = $control;
		}
	},
	
	execBind: function($ev_bind, $bind_ctrl, $bind_ctrl_dom, $sender)
	{
		if (!$bind_ctrl_dom.length)
			return;
		$bind_ctrl_dom = $bind_ctrl_dom || $($bind_ctrl.self);
		if ($ev_bind.context && $ev_bind.context.length)
		{
			// $bind_ctrl_dom = $bind_ctrl_dom.find($ev_bind.context);
			// @TODO - this is not optimized ok !!!!!!! - we also do closest before !!!
			$bind_ctrl_dom = $($sender).closest($bind_ctrl.self + ' ' + $ev_bind.context, $bind_ctrl_dom[0]);
			if (!$bind_ctrl_dom.length)
				return;
		}
		// 1. resolve for
		var $bind_for = $ev_bind.for;
		var $bind_elements = (typeof($bind_for) === "function") ? $bind_for($bind_ctrl_dom) : 
								$bind_for ? $bind_ctrl_dom.find($bind_for) : $bind_ctrl_dom;

		// 2. foreach element execute 
		var $b_len = $bind_elements.length;
		var $bind_do = $ev_bind.do;
		var $use_bcd = $bind_ctrl_dom && $bind_ctrl_dom.length ? $bind_ctrl_dom[0] : null;
		for (var $k = 0; $k < $b_len; $k++)
		{
			var $dom = $bind_elements[$k];
			$bind_do.apply($dom, [$use_bcd, $sender]);
		}
	},
	
	execBinds: function($event_type, $sender, $if_needs_init)
	{
		if (!$event_type)
		{
			// loop them all
			if (!omi2._objects)
				return;
			for (var $name in omi2._objects)
			{
				var $bind_ctrl = omi2._objects[$name];
				if ($bind_ctrl)
				{
					var $bind_ctrl_dom = $($sender).closest($bind_ctrl.self);
					$binds = $bind_ctrl.binds;
					var $b_len = $binds.length;
					for (var $b = 0; $b < $b_len; $b++)
						omi2.execBind($binds[$b], $bind_ctrl, $bind_ctrl_dom, $sender);
				}
			}
		}
		else
		{
			var $listeners = (typeof($event_type) === "string") ? omi2._watch[$event_type] : $event_type;
			var $matches_cache = {};
			var $watch_event_selectors = $listeners.e;

			var $len = $watch_event_selectors.length;
			
			for (var $i = 0; $i < $len; $i++)
			{
				var $ev_selector = $watch_event_selectors[$i];
				
				var $matches = false;

				if (typeof($ev_selector) === "function")
				{
					alert("@todo : omi2.handleEvent, when the event selector is a function");
					$ev_selector($listeners.c[$i]);
				}
				else // it's a string selector
				{
					// if in context ... we will need to address only the binds in this context
					$matches = ($matches_cache[$ev_selector] === undefined) ? 
								($matches_cache[$ev_selector] = ($sender.matches($ev_selector) || $($sender).closest($ev_selector).length)) : $matches_cache[$ev_selector];
				}

				if ($matches)
				{
					console.log('$ev_selector', $ev_selector);
					// we should only do it for matches that are in context
					var $ev_bind = $listeners.b[$i];
					var $bind_ctrl = $listeners.c[$i];
					var $bind_ctrl_dom = $($sender).closest($bind_ctrl.self);

					omi2.execBind($ev_bind, $bind_ctrl, $bind_ctrl_dom, $sender);
				}
			}
		}
	},
	
	handleEvent: function(event)
	{
		var event_type = event.type;
		var $listeners = omi2._watch[event_type];
		
		if (!$listeners)
			return;
		
		var t1 = new Date();
		
		omi2.execBinds($listeners, event.target);
		
		var t2 = new Date();
		
		///omi2.__bind_count++;
		$(".log").html("Count: " + omi2.__bind_count + " | Time: " + (t2.getTime() - t1.getTime()) + " ms <br/>\n");
	},
	
	InitGlobal: function()
	{
		if (omi2._InitGlobal)
			return;
		omi2._InitGlobal = true;
		
		if (omi2._setup.events)
		{
			var r_events = omi2._setup.events;
			for (var i = 0; i < r_events.length; i++)
			{
				var event = r_events[i];
				window.addEventListener(event, omi2.handleEvent, ((event === "focus") || (event === "focusin") || (event === "focusout")));
			}
		}
	},
	
	_setup: {
		// the events we will be registering to
		events: ["click", "submit", "input", "change", "focus", "focusin", "DOMFocusIn", "mouseover", "mouseout", "keyup"],
		delay_timeout: 500, // min time in ms between retries
		delay_retry_timeout: 50 // time between rechecks 
	},
	
	setup: function(options)
	{
		if (!options)
			return;
		for (var k in options)
			omi2._setup[k] = options[k];
	}
};

// DO THE INIT -----------------------
if (!document)
	console.error("missing referece to document");
else if ((document.readyState === "loaded") || (document.readyState === "interactive") || (document.readyState === "complete"))
	omi2.InitGlobal(omi);
else
	document.addEventListener("readystatechange", function ()
	{
		/*
		uninitialized - Has not started loading yet
		loading - Is loading
		loaded - Has been loaded
		interactive - Has loaded enough and the user can interact with it
		complete - Fully loaded */

		if ((document.readyState === "loaded") || (document.readyState === "interactive") || (document.readyState === "complete"))
			omi2.InitGlobal(omi);
	});
