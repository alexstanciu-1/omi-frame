/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


//Returns true if it is a DOM node
function __imgrsp_isNode(o)
{
	return ((typeof (Node) === "object") ? (o instanceof Node) : 
		(o && (typeof (o) === "object") && (typeof (o.nodeType) === "number") && (typeof (o.nodeName) === "string"))
	);
}

//Returns true if it is a DOM element    
function __imgrsp_isElement(o)
{
	return ((typeof (HTMLElement) === "object") ? (o instanceof HTMLElement) : //DOM2
		(o && (typeof (o) === "object") && (o !== null) && (o.nodeType === 1) && (typeof (o.nodeName) === "string"))
	);
}

function __imgrsp_resizeImages(context)
{
	var imgsCls = (window.IMGSRESIZECFG && window.IMGSRESIZECFG["search_cls"]) ? window.IMGSRESIZECFG["search_cls"] : null;
	var imgSrcAttr = (window.IMGSRESIZECFG && window.IMGSRESIZECFG["src_attr"]) ? window.IMGSRESIZECFG["src_attr"] : null;
	if (!imgsCls || !imgSrcAttr)
		return;

	if (!context || (!__imgrsp_isNode(context) && !__imgrsp_isElement(context)))
		context = document;
	var imgs = context.getElementsByClassName(imgsCls);
	__imgrsp_loadImages(imgs, imgSrcAttr);
}

function __imgrsp_loadImages(imgs, attribute)
{
	if (!imgs || (imgs.length === 0))
		return;

	for (var i = 0; i < imgs.length; i++)
	{
		var img = imgs[i];

		var rsrc = img.getAttribute(attribute);
		if (!rsrc)
			continue;

		//console.log(rsrc);
		var isBackground = (img.tagName !== "IMG");
		var actualWidth = __imgrsp_actualWidth(isBackground ? img : img.parentNode);
		//console.log(actualWidth);

		var neededWidth = __imgrsp_getStepWidth(actualWidth);

		//console.log(neededWidth);

		if (!neededWidth)
			continue;

		var newsrc = __imgrsp_getNewSrc(rsrc, neededWidth);
		if (!newsrc)
			continue;

		// setup background or image only if was not set yet
		// here we use background image for certain
		//console.log("process here the image :: " + img.src);
		if (isBackground)
		{
			var existingBgImg = img.style["background-image"];
			var bckgImgName = (existingBgImg && (existingBgImg.length > 0)) ? existingBgImg.substring(5, existingBgImg.length - 2) : null;
			if (!bckgImgName || (bckgImgName !== newsrc))
				img.style["background-image"] = "url('" + newsrc + "')";
		}
		else if (newsrc !== img.src)
		{
			//console.log("change src here for image:: " + newsrc + " at needed width of :: " + neededWidth);
			img.src = newsrc;
		}
	}
}

function __imgrsp_actualWidth(node)
{
	// if the node is visible - it is not affected by style properties like visibility hidden, display none or opacity
	// and the parents are visible as well - for determine this we use clientWidth and clientHeight properties
	if (__imgrsp_isVisible(node) && (node.clientWidth !== 0) && (node.clientHeight !== 0))
		return node.offsetWidth;

	// get all hidden parents
	var hiddenParents = __imgrsp_getHiddenParents(node);
	// save the origin style props
	// set the hidden el css to be got the actual value later
	var origStyleProps = [];
	for (var i = 0; i < hiddenParents.length; i++)
	{
		var hiddenP = hiddenParents[i];
		origStyleProps.push([hiddenP, {
			"style" : {
				"display" : hiddenP.style.display, 
				"visibility" : hiddenP.style.visibility,
				"height" : hiddenP.style.height
			}
		}]);

		hiddenP.style.visibility = "hidden";
		hiddenP.style.display = "block";
		hiddenP.style.height = "1px";
	}

	// save the width of the node
	
	var width = node.offsetWidth;
	//alert(width);

	//restore original style properties on parents
	for (var i = 0; i < origStyleProps.length; i++)
	{
		var hiddenP = origStyleProps[i][0];
		var props = origStyleProps[i][1];
		for (var k in props)
		{
			var prop = k;
			var propVals = props[k];
			if (typeof(propVals) === "object")
			{
				for (var j in propVals)
					hiddenP[prop][j] = propVals[j];
			}
			else
				hiddenP[prop] = propVals;
		}
    }
	return width;
}

function __imgrsp_getHiddenParents(node)
{
	var list = [];
	while(node)
	{
		if (!__imgrsp_isVisible(node))
			list.push(node);
		node = node.parentNode;
		if (node.tagName === "HTML")
			break;
	}
	return list;
}

function __imgrsp_isVisible(node) 
{
	//return (node.offsetParent === null);
	return (__imgrsp_getStyle(node, "display") !== "none");
}

function __imgrsp_getStyle(el, property) 
{	
	if (el.__cmpStyle)
		return el.__cmpStyle[property];

	if (window.getComputedStyle)
	{
		el.__cmpStyle = document.defaultView.getComputedStyle(el, null);
		return el.__cmpStyle[property];
	}

	if (el.currentStyle) 
	{
		el.__cmpStyle = el.currentStyle;
		return el.__cmpStyle[property];
	}
	return null;
}

function __imgrsp_getNewSrc(rsrc, neededWidth)
{
	var dir = (window.IMGSRESIZECFG && window.IMGSRESIZECFG["images_folder"]) ? window.IMGSRESIZECFG["images_folder"] : null;
	if (!dir)
		return null;

	var lpos = rsrc.lastIndexOf("/") + 1;
	var imgBaseName = rsrc.substr(lpos);
	var imgPath = rsrc.substr(0, lpos);

	var ppos = imgBaseName.lastIndexOf(".");
	var imgExt = imgBaseName.substr(ppos + 1);
	var imgName = imgBaseName.substr(0, ppos);
	return imgPath + dir + "/" + imgName + "." + neededWidth + "." + imgExt;
}

function __imgrsp_getStepWidth(width)
{
	if (!window.IMGSRESIZECFG || !window.IMGSRESIZECFG["responsive_sizes"])
		return null;

	var sizes = window.IMGSRESIZECFG["responsive_sizes"];
	for (var i = 0; i <sizes.length; i++)
	{
		if (sizes[i] >= width)
			return sizes[i];
	}

	return null;
	//return sizes.pop();
}

if (window.IMGSRESIZECFG)
{
	if ((document.readyState === "loaded") || (document.readyState === "interactive") || (document.readyState === "complete"))
	{
		// DO IT
		__imgrsp_resizeImages();
	}
	else
	{
		window._rsimgsExecutedOnReady = false;
		document.onreadystatechange = function () {
			if (window._rsimgsExecutedOnReady)
				return;

			if ((document.readyState === "loaded") || (document.readyState === "interactive") || (document.readyState === "complete"))
			{
				window._rsimgsExecutedOnReady = true;
				//console.log("__imgrsp_resizeImages");
				__imgrsp_resizeImages();
			}
		};
	}
}