# IIS / WP Post Email Notification

A [WordPress](https://wordpress.org/) plugin sending email notifications to subscribers when a new post is published.

The ORIGINAL plugin is available on [wordpress.org](https://wordpress.org/plugins/wp-post-email-notification).

## Developers

The version hosted on GitHub seweb is the development version and by itself not able to run in your WordPress installation.

To use this version, you will need [Node](http://nodejs.org/), [Composer](https://getcomposer.org/) and [Webpack](https://webpack.github.io/) installed.

* `git clone git@github.com:sewebb/iis-wp-post-email-notification.git` into the plugins-folder of your WordPress installation
* `cd wp-post-email-notification/`
* `composer install` to install php dependencies
* `npm install` to install JS dependencies
* `webpack` to compile the JS files
* Activate the plugin in the WordPress plugin administration panel

[Gulp](http://gulpjs.com/) is used to build an installable bundle, but not required for development.

## Distribute new version
* Run `gulp build`
* The `_dist`-folder is created
* In this folder is "iis-wp-post-email-notification"
* Zip this folder and add it to your site

For exampel add the .zip to a public dropbox-folder and install it with wp-cli
Example: `wp plugin install https://dl.dropboxusercontent.com/u/8036/SE/plugins/iis-wp-post-email-notification.zip`
(If plugin already exits, use: `wp plugin install --force https://dl.dropboxusercontent.com/u/8036/SE/plugins/iis-wp-post-email-notification.zip`)

In a multisite you could activate by site with:
`wp plugin activate iis-wp-post-email-notification --url=https://domain.se`
(Replace https://domain.se with site url to activate on)
