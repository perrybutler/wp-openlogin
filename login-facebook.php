<?php

// TODO: we are calling wp-load...bad news!

// load the Wordpress core so this page can access WP functions during an ajax call
require_once( "../../../wp-load.php" );

// load the facebook library
require '../../libs/facebook-php-sdk/facebook.php';

// initiate the user session
session_start();

// hard code the OPENID_PROVIDER since it is always Facebook
$_SESSION['OPENID_PROVIDER'] = "Facebook";

// initiate the facebook object
$facebook = new Facebook(array(
  'appId'  => '1380765298818773',
  'secret' => 'e9ed2ba3b8f2892130ef267afaeb206d',
));

// set a session variable we can use to monitor the user's authentication status throughout the lifetime of his connection
$_SESSION['OPENID_IDENTITY'] = "";
$_SESSION['OPENID_EMAIL'] = "";

// check facebook's "user" so we know if we're at pre-authentication or post-authentication
$fb_user = $facebook->getUser();

if (!$fb_user) {
	// user is nothing, we are at pre-authentication so we simply start authentication
	$params = array(
		'scope' => 'email'
	);
	// remember where the user was, so we can return the user to that page
	$_SESSION['LAST_URL'] = $_SERVER['HTTP_REFERER'];
	
	// redirect to the facebook authurl to begin the third party authentication process
	header("Location: " . $facebook->getLoginUrl($params)); exit;
}
else {
	// the third-party responded to our request, check if we're authenticated
	try {
		// get the profile info we requested from the third-party
		$fb_user_profile = $facebook->api('/me');
		// store the user's now-authenticated third-party account info in the user session so we can
		//	access it again later, such as when a registration is initiated through a separate
		//	http request
		$_SESSION['OPENID_IDENTITY'] = $fb_user;
		$_SESSION['OPENID_EMAIL'] = $fb_user_profile["email"];
	}
	catch (FacebookApiException $e) {
		error_log($e);
		$fb_user = null;
		echo "Unforseen Facebook ERROR! Please return to the Home page...";
		// set the session variable to a blank string to indicate an unauthenticated state
		$_SESSION['OPENID_IDENTITY'] = "";
		$_SESSION['OPENID_EMAIL'] = "";
		exit();
	}

	// TODO: this does not prompt for registration and instead automatically registers the user account
	//	but we should follow the StackExchange pattern...
	// check if a WP user account has already been linked to this now-authenticated openid account 
	//  and if so, login that user now...if not, prompt for registration
	$matched_user = $rapid_platform->login->get_user_by_openid($_SESSION["OPENID_IDENTITY"]);
	
	// handle
	if ( $matched_user ) {
		$user_id = $matched_user->ID;
		$user_login = $matched_user->user_login;
		wp_set_current_user( $user_id, $user_login );
		wp_set_auth_cookie( $user_id );
		do_action( 'wp_login', $user_login );
		// after login, redirect to the user's last location
		header("Location: " . $_SESSION["LAST_URL"]); exit;
	}
	
	if ( is_user_logged_in() ) {
		// link accounts & login - no WP user account is associated with this third-party account, but a WP user account is
		//	currently logged in so we link the two accounts
		global $current_user;
		get_currentuserinfo();
		$user_id = $current_user->ID;
		global $rapid_platform;
		$rapid_platform->login->user_add_linked_account($user_id);
		// after linking the account, redirect user to their last url
		header("Location: " . $_SESSION["LAST_URL"]); exit;
	}
	
	if ( !is_user_logged_in() && !$matched_user ) {
		// there is no current WP user account associated with this third-party account, proceed to registration
		header("Location: " . plugins_url('', __FILE__) . "/process-registration.php"); exit;
	}

}
	
?>