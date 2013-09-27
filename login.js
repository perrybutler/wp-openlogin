jQuery(document).ready(function() {

	jQuery(".delete-linked-account").click(function(event) {
		event.preventDefault();
		var post_data = {
				action: "rp_user_delete_linked_account",
				openid_email: jQuery(this).data["openid_email"],
			}
		jQuery.ajax({
			type: "POST",
			url: wpjs.ajaxurl, //wpjs.plugins_url + "/components/login/login-ajax.php?openid_email=" + jQuery(this).data["openid_email"], 
			data: post_data,
			success: function(json_response) {
				var oresponse = JSON.parse(json_response);
				if (oresponse["result"] == 1) {
					jQuery(".delete-linked-account").parent().fadeOut();
				}
			}
		});
	});
	
	/*
	jQuery(".add-linked-account").click(function() {
		// TODO: same ajax pattern as delete? not quite...we need to show the login providers first...
		loginOpenID("google");
	});
	*/

});

// =========================================
// LOGIN/LOGOUT FUNCTIONS
// =========================================

function loginFacebook() {
	window.location = wpjs.plugins_url + "/wp-openlogin/login-facebook.php";
}

function loginOpenID(oid_provider) {
	window.location = wpjs.plugins_url + "/wp-openlogin/login-openid.php?oid_provider=" + encodeURIComponent(oid_provider);
}

function processLogout() {
	jQuery.ajax({
		url: wpjs.plugins_url + "/wp-openlogin/process-logout.php", 
		success: function(ret) {
			//alert("Result: " + ret + " Args: " + openid_args);
			//alert("handleOpenIDResponse SUCCESS: " + ret);
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