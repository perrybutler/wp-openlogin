WordPress Open Login
====================

An OAuth 2.0 / OpenID user registration and login plugin for WordPress which integrates with the existing WordPress Users system. Includes/uses the [LightOpenID library](https://github.com/iignatov/LightOpenID).

Who it's for
------------

Implement a single sign-on experience for visitors to your blog/site. 

Visitors can login with a Google/Yahoo/Facebook button on your blog/site. If the WordPress setting to allow new user registrations is enabled, any guest who authenticates successfully with their OAuth/OpenID provider will have a WordPress user account created for them automatically and linked to their OAuth/OpenID account.

Users can self-manage their linked accounts via the existing Edit Profile page:

![WP Open Login](http://files.glassocean.net/github/wp-openlogin.png)

Roadmap
-------

**Don't use wp-load.php**

Figure out how to eliminate the use of "../../wp-load.php" in some of the files, since this isn't always the path.

History
-------

This is a port of the **Unified Login** component from [Rapid Platform](http://github.com/perrybutler/rapidplatform).
