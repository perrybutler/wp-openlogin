jQuery(document).ready(function() {

	jQuery(".delete-linked-account").click(function(event) {
		event.preventDefault();
		var btn = jQuery(this);
		var eml = btn.data("openid-email");
		var post_data = {
				action: "rp_user_delete_linked_account",
				openid_email: eml,
			}
		jQuery.ajax({
			type: "POST",
			url: wpjs.ajaxurl,
			data: post_data,
			success: function(json_response) {
				var oresponse = JSON.parse(json_response);
				if (oresponse["result"] == 1) {
					btn.parent().fadeOut();
				}
			}
		});
	});

});

// =========================================
// LOGIN/LOGOUT FUNCTIONS
// =========================================

function loginFacebook() {
	window.location = wpjs.plugin_dir_url + "/login-facebook.php";
}

function loginOpenID(oid_provider) {
	window.location = wpjs.plugin_dir_url + "/login-openid.php?oid_provider=" + encodeURIComponent(oid_provider);
}

function processLogout() {
	jQuery.ajax({
		url: wpjs.plugin_dir_url + "/wp-openlogin/process-logout.php", 
		success: function(ret) {
			//TODO: refresh page (user login status) with js
			if (ret == true) {
				alert('logged out;refresh page');
			}
		}
	});
	// remove the viewport dimmer
	jQuery("#custom-dialog-dimmer").hide();
	jQuery("#custom-dialog").hide();
}