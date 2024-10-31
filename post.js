"use strict";

jQuery(document).ready(function() {
	jQuery('#post-plugin-refresh').on('click', function(){
		var pkey = jQuery('#p_key').val();
		this.disabled = true;

		jQuery.ajax({
		    url: "https://po.st/v1/profiles/" + pkey + "/widgets?callback=?",
				crossDomain: true,
				dataType: "jsonp"
		}).done(function(response, status){
			if(response.error) {
				if(jQuery('#post-pubkeyerror').length < 1){
					var showError = "<div class='error fade' id='post-pubkeyerror'><p>You must <a href='/wp-admin/options-general.php?page=post.php'>enter your Po.st publisher key</a> for this plugin to work properly.</p></div>";
					jQuery('.post-form').before(showError);
				}
			}
			else {
				if(jQuery('#post-pubkeyerror').length){
					jQuery('#post-pubkeyerror').remove();
				}
				updateList(response);
			}
			jQuery('#post-plugin-refresh')[0].disabled = false;

		})
		return false;
	})
});

function updateList(data){
	var post_widgets = data.widgets,
		listWidgets = jQuery('#post-list-widgets'),
		sharingWidget = false,
		nativeWidget = false;
	for (var i = 0; i < post_widgets.length; i++) {
		var widget_curr = post_widgets[i];

		if(widget_curr.t === "SHARING" || widget_curr.t === "NATIVE"){
			widget_curr.t === 'SHARING' ? sharingWidget = true : nativeWidget = true;

			if(widget_curr.d === true && jQuery("li[data-id='display_type_" + widget_curr.t + "']",listWidgets).length > 0) {
				jQuery("li[data-id='display_type_" + widget_curr.t + "']",listWidgets).remove();
			}
			else if(widget_curr.d === false && jQuery("li[data-id='display_type_" + widget_curr.t + "']").length < 1){

				var markup = '<li data-id="display_type_' + widget_curr.t +'"><input type="radio" id="display_type_' + widget_curr.t +'" name="display_type" value="' + widget_curr.t +'"> <label for="display_type_' + widget_curr.t + '">'
				markup += widget_curr.t === 'SHARING' ? "Standard sharing buttons" : "Native sharing buttons";
				markup += '</label></li>';
				listWidgets.append(markup);
			}
		}
		if(i === post_widgets.length - 1) {

			if(sharingWidget === false && jQuery("li[data-id='display_type_SHARING']").length < 1) {
				jQuery("li[data-id='display_type_SHARING']").remove();
			}
			if(nativeWidget === false && jQuery("li[data-id='display_type_NATIVE']").length < 1) {
				jQuery("li[data-id='display_type_NATIVE']").remove();
			}
			if(	jQuery("li",listWidgets).length === 0) {

			}
			else {
				if(	jQuery("li input:checked",listWidgets).length < 1) {
					jQuery("li:first-child input",listWidgets)[0].checked = true;
				}
			}

		}
	}
}
