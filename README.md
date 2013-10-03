WordPress OpenLogin
====================

It's an OAuth 2.0 / OpenID user registration and login plugin for WordPress which integrates with the existing WordPress Users system. Includes and uses the [LightOpenID library](https://github.com/iignatov/LightOpenID) and [Facebook-PHP-SDK](https://github.com/facebook/facebook-php-sdk).

Functions in a similar way to the StackExchange/StackOverflow login system - user's have the ability to link multiple third-party accounts to your WordPress site for logging into their WordPress user account.

Now supports WordPress Multisite.

Instructions
------------

1. Download the plugin files, place them into a "wp-openlogin" folder, then put this folder in your wp-content/plugins directory.
2. Login to the WordPress admin dashboard and activate the plugin.
2. Configure the plugin through Settings >> WP-OpenLogin.
3. Add a login form to your site using the shortcode [rp_login_form].

Who it's for
------------

Implement a single sign-on experience for visitors to your blog/site. Visitors can login with a Google/Yahoo/Facebook button. If the WordPress setting to allow new user registrations is enabled, any guest who authenticates successfully with their OAuth/OpenID provider will have a WordPress user account created for them automatically and linked to their OAuth/OpenID account.

Single sign-on is useful for building a social/community driven site/app where guests would want to authenticate with their third-party account instead of maintaining a separate account for your site/app.

Users can self-manage their linked accounts via the existing Edit Profile page:

![WP Open Login](http://files.glassocean.net/github/wp-openlogin.png)

Roadmap
-------

**Don't use wp-load.php**

Figure out how to eliminate the use of "../../wp-load.php" in some of the files, since this isn't always the path for some WordPress installations.

**Keep the user informed of progress**

Plugin should inform the user when a login/registration is taking place, with a loading icon or Please wait... message. We should also notify the user if it succeeds or fails, with a popup message that fades out quickly. Ideally, we need a simple way to push a message from the server back to the client (using JSON), but the login flow could make this difficult to implement.

**Integrate with wp-login**

Integrate with the default WordPress login page, with the ability to remove the default username/password fields (forcing the use of a third-party provider).

**Provider selector**

Include a selector on the settings page which would allow the admin to enable certain providers, ultimately determining which providers will show up in the [rp_login_form] and on the wp-login page.

**Fancy login buttons**

The login form [rp_login_form] is currently text-only, there are no brand icons/buttons for the various third-party providers. Include a few alternate "designs" such as basic/list/grid which the admin could choose from in the backend for quickly changing the style of the login form.

History
-------

This is a port of the **Unified Login** component from two of my other projects, [Rapid Platform](http://github.com/perrybutler/rapidplatform) and BirthSource.
