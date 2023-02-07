
QExtendClass("Omi\\View\\Listing", "QWebControl", {
	
});

jQuery(document).ready(function () {
	jQuery(document.body).on("click", ".js-change-page", function () {
		var jq = jQuery(this);
		jq.closest(".js-paginator").find(".js-start-limit").val(jq.data("start"));
		jq.trigger("afterSetupLimit");
	});
	
	jQuery(".js-change-page").on("afterSetupLimit", function () {
		var formJq = jQuery(this).closest("form");
		if (formJq.length === 0)
			return;
		formJq[0].submit();
	});
});