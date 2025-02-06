omi2.control("jx-controls", {
	
	binds: [
		{
			context: ".jx-dd",
			for: ".jx-dd-container",
			do: function($ctrl_dom, $sender) 
			{
				var $classActive = 'active';
				
				// this = for's dom
				if (this.classList) 
					this.classList.toggle($classActive);
				else 
				{
					var classes = this.$classActive.split(' ');
					var existingIndex = classes.indexOf($classActive);

					if (existingIndex >= 0)
					  classes.splice(existingIndex, 1);
					else
					  classes.push($classActive);

					this.$classActive = classes.join(' ');
				}
			},
			watch: {
				'click': '.jx-dd-trigger'
			}
		},
		{
			context: ".jx-tab-panel",
			for: ".jx-tabs-content .tab-content",
			do: function($ctrl_dom, $sender) 
			{				
				// get tab content id which is on sender attribute
				var $contentId = $sender.getAttribute("data-controls");

				// define active class
				var $classActive = 'active';
				
				// check if tab content has classes (it always has)
				if (this.classList && this.classList.contains($classActive))
					this.classList.remove($classActive);
				
				var $tabs = $ctrl_dom.querySelectorAll(".jx-tab");
				for (var i = 0; i < $tabs.length; i++)
				{
					if (($contentId !== $tabs[i].getAttribute("data-controls"))
							&& ($tabs[i].classList)
							&& $tabs[i].classList.contains($classActive))
						
						$tabs[i].classList.remove($classActive);
				}
				
				if (this.id !== $contentId)
					return;
				
				if (this.classList)
					this.classList.add($classActive);
				else
					this.className += ' ' + $classActive;

				if ($sender.classList)
					$sender.classList.add($classActive);
				else
					$sender.className += ' ' + $classActive;
			},
			watch: {
				'click': '.jx-tab'
			}
		},
		{
			context: ".jx-accordion",
			for: ".jx-acc-content",
			do: function($ctrl_dom, $sender) 
			{
				// define active class
				var $classActive = 'active';
				
				// get accordion content
				var $accordionContent = $sender.nextElementSibling;
				// this = for's dom
				if ($sender.classList) 
				{
					$sender.classList.toggle($classActive);
					$accordionContent.classList.toggle($classActive);
				}
				else 
				{
					var classes = this.$classActive.split(' ');
					var existingIndex = classes.indexOf($classActive);

					if (existingIndex >= 0)
					  classes.splice(existingIndex, 1);
					else
					  classes.push($classActive);

					$sender.$classActive = classes.join(' ');
				}
			},
			watch: {
				'click': '.jx-acc-trigger'
			},
			init: true
		},
		{
			context: ".jx-modal",
			for: ".jx-modal-content",
			do: function($ctrl_dom, $sender) 
			{
				// define active class
				var $classActive = 'is-visible';
				
				if (this.classList)
					this.classList.add($classActive);
				else
					this.className += ' ' + $classActive;
			},
			watch: {'click': '.jx-btn-modal'}
		},
		{
			context: ".jx-modal",
			for: ".jx-modal-content",
			do: function($ctrl_dom, $sender) 
			{
				// define active class
				// var $classActive = 'is-visible';
				
				// check if tab content has classes (it always has)
				// if (this.classList && this.classList.contains($classActive))
				// 	this.classList.remove($classActive);
				
				this.parentElement.remove();
			},
			watch: {'click': '.jx-close-modal'}
		},
		{
			context: ".jx-select",
			for: ".jx-select-dd",
			do: function($ctrl_dom, $sender) 
			{
				var $classActive = 'active';
				
				// this = for's dom
				if (this.classList) 
					this.classList.toggle($classActive);
				else 
				{
					var classes = this.$classActive.split(' ');
					var existingIndex = classes.indexOf($classActive);

					if (existingIndex >= 0)
					  classes.splice(existingIndex, 1);
					else
					  classes.push($classActive);

					this.$classActive = classes.join(' ');
				}
			},
			watch: {'click': '.jx-selection'}
		},
		{
			context: ".jx-select",
			for: ".jx-select-results",
			do : function ($ctrl_dom, $sender)
			{
				var $search_str = $sender.value.toUpperCase();
				var $items = this.getElementsByTagName("div");
				var $reg_exp = new RegExp($search_str.replace(/\s+/g, '.*'));
				for (var $i = 0; $i < $items.length; $i++)
				{
					var $content = $items[$i].innerText.toUpperCase();
					
					$items[$i].style.display = $reg_exp.test($content) ? "block" : "none";
				}

			},
			watch: {'keyup' : '.jx-select-search-input'}
		},
		{
			context: ".jx-select",
			for: ".jx-selection",
			do : function ($ctrl_dom, $sender)
			{
				var $classSelected = 'selected';
				var $items = $ctrl_dom.getElementsByClassName("jx-select-results")[0].getElementsByTagName("div");

				for (var $i = 0; $i < $items.length; $i++)
				{
					if ($items[$i].classList)
						$items[$i].classList.remove($classSelected);
				}
				
				if ($sender.classList)
					$sender.classList.add($classSelected);
				else
					$sender.className += ' ' + $classSelected;
				
				this.innerText = $sender.innerText;
				
				var $selectDD = $ctrl_dom.getElementsByClassName("jx-select-dd")[0];
				if ($selectDD.classList)
					$selectDD.classList.remove('active');
				
				var $selectInput = this.getElementsByClassName("jx-select-value")[0];
				if ($selectInput)
					this.removeChild($selectInput);
				
				var input = document.createElement("input");
				input.setAttribute("type", "hidden");
				input.setAttribute("name", "SimpleSelect");
				input.setAttribute("value", $sender.innerText);
				input.setAttribute("class", "jx-select-value");
				this.appendChild(input);
			},
			watch : {'click' : '.jx-select-results > div'}
		},
		{
			context: ".jx-multi-select",
			for: ".jx-select-dd",
			do: function($ctrl_dom, $sender) 
			{
				var $classActive = 'active';
				
				// this = for's dom
				if (this.classList) 
					this.classList.toggle($classActive);
				else 
				{
					var classes = this.$classActive.split(' ');
					var existingIndex = classes.indexOf($classActive);

					if (existingIndex >= 0)
					  classes.splice(existingIndex, 1);
					else
					  classes.push($classActive);

					this.$classActive = classes.join(' ');
				}
			},
			watch: {'click': '.jx-selection'}
		},
		{
			context: ".jx-multi-select",
			for: ".jx-selection",
			do : function ($ctrl_dom, $sender)
			{
				var $classSelected = 'selected';
				var $items = $ctrl_dom.getElementsByClassName("jx-select-item");
				
				var $divContainer = $ctrl_dom.getElementsByClassName("jx-select-value")[0];
								
				if (!$divContainer)
				{
					$divContainer = document.createElement("div");
					$divContainer.setAttribute("class", "jx-select-value");
					$ctrl_dom.appendChild($divContainer);
				}
				
				for (var $i = 0; $i < $items.length; $i++)
				{
					if ($items[$i].classList)
						$items[$i].classList.remove($classSelected);
				}
				
				// clicked element is already selected, that means we remove it from the select
				if ($sender.className === $classSelected)
				{
					// remove selcted class
					$sender.classList.remove($classSelected);
					
					// get all selected items
					var $selectedItems = this.getElementsByClassName("jx-selected-item");
					
					// go through each item
					for (var $i = 0; $i < $selectedItems.length; $i++)
					{
						if ($selectedItems[$i].innerText === $sender.innerText)
							this.removeChild($selectedItems[$i]);
					}
					
					if (this.innerText === '')
						this.innerText = 'Select';
					
					var $inputs = $divContainer.getElementsByTagName("input");
					
					for (var $j = 0; $j < $inputs.length; $j++)
					{
						if ($inputs[$j].value === $sender.innerText)
							$divContainer.removeChild($inputs[$j]);
					}
				}
				// clicked element is not selected, we add it to selection
				else
				{
					// add class selected on clicked element item
					if ($sender.classList)
						$sender.classList.add($classSelected);
					else
						$sender.className += ' ' + $classSelected;
					
					// get all selected items
					var $selectedItems = this.getElementsByClassName("jx-selected-item");
					
					// check if the item selected is the first item
					if ($selectedItems.length === 0)
					{
						// clear selection text
						this.innerText = '';
						
						// create and append element in selection zone
						var $spanElem = document.createElement("span");
						$spanElem.setAttribute("class", "jx-selected-item selected-item");
						$spanElem.innerText = $sender.innerText;
						
						var $removeElem = document.createElement("i");
						$removeElem.setAttribute("class", "jx-remove-item fa fa-times");
						$spanElem.appendChild($removeElem);
						this.appendChild($spanElem);
					}
					// we already have at least one item in the selection
					else
					{
						// init item exists
						var $elemExists = true;
						
						// go through each item
						for (var $i = 0; $i < $selectedItems.length; $i++)
						{
							// the item does not exists
							if ($selectedItems[$i].innerText !== $sender.innerText)
								$elemExists = false;
						}
						
						// element does not exists
						if (!$elemExists)
						{
							// create item and append to selction
							var $spanElem = document.createElement("span");
							$spanElem.setAttribute("class", "jx-selected-item selected-item");
							$spanElem.innerText = $sender.innerText;
							
							var $removeElem = document.createElement("i");
							$removeElem.setAttribute("class", "jx-remove-item fa fa-times");
							$spanElem.appendChild($removeElem);
							this.appendChild($spanElem);
						}
					}
					
					// create input item and append to saelcted values
					var input = document.createElement("input");
					input.setAttribute("type", "hidden");
					input.setAttribute("name", "MultiSelect[]");
					input.setAttribute("value", $sender.innerText);
					$divContainer.appendChild(input);
				}
				
				
				// @I don't know what the fuck did I do here!!!
				var $string = "Select,";
				
				if (this.innerText.indexOf($string) !== -1)
					this.innerText = this.innerText.replace($string, '');
			},
			watch : {'click' : '.jx-select-results > div'}
		},
		{
			context: ".jx-multi-select",
			for: ".jx-selection",
			do : function ($ctrl_dom, $sender)
			{
				var $classSelected = "selected";
				// get parent elem
				var $selectionItem = $sender.parentElement;
				var $valueString = $selectionItem.innerText;
				var $resultItems = $ctrl_dom.getElementsByClassName("jx-select-results")[0].getElementsByTagName("div");
				
				for (var $i = 0; $i < $resultItems.length; $i++)
				{
					if ($resultItems[$i].innerText === $valueString)
						$resultItems[$i].classList.remove($classSelected);
				}
				
				this.removeChild($selectionItem);
				if (this.innerText === '')
					this.innerText = 'Select';
				
				var $divContainer = $ctrl_dom.getElementsByClassName("jx-select-value")[0];
				var $inputs = $divContainer.getElementsByTagName("input");
					
				for (var $j = 0; $j < $inputs.length; $j++)
				{
					if ($inputs[$j].value === $valueString)
						$divContainer.removeChild($inputs[$j]);
				}
			},
			watch : {'click' : '.jx-remove-item'}
		},
		{
			context: ".jx-multi-select",
			for: ".jx-select-results",
			do : function ($ctrl_dom, $sender)
			{
				var $search_str = $sender.value.toUpperCase();
				var $items = this.getElementsByTagName("div");
				var $reg_exp = new RegExp($search_str.replace(/\s+/g, '.*'));
				for (var $i = 0; $i < $items.length; $i++)
				{
					var $content = $items[$i].innerText.toUpperCase();
					
					$items[$i].style.display = $reg_exp.test($content) ? "block" : "none";
				}

			},
			watch: {'keyup' : '.jx-select-search-input'}
		},
		{
			context: ".jx-menu",
			for: ".jx-menu-wrapper",
			do: function($ctrl_dom, $sender)
			{
				// define active class
				var $classOpen = 'is-open';
				
				// check if tab content has classes (it always has)
				if (this.classList && this.classList.contains($classOpen))
					this.classList.remove($classOpen);
				else
					this.classList.add($classOpen);
				
				var $menuBtnTrigger = $ctrl_dom.getElementsByClassName('jx-menu-open')[0];
				
				console.log($menuBtnTrigger);
				
				if ($menuBtnTrigger.classList && $menuBtnTrigger.classList.contains($classOpen))
					$menuBtnTrigger.classList.remove($classOpen);
				else
					$menuBtnTrigger.classList.add($classOpen);
			},
			watch: {'click' : '.jx-menu-open'}
		}
	]
});
