=== Facebook Pictures ===
Contributors: nbadano
Donate link: 
Tags: facebook pictures, open graph api, facebook photos, albums
Requires at least: 3.3
Tested up to: 3.5.1
Stable tag: 
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Pull most commented and liked pictures from your Facebook Profile or Page you manage and display them in your sidebar or any widgetized area.

== Description ==

Facebook Pictures allows you to include albums and embed pictures from your Facebook account into your site. You'll need a Facebook App ID and Secret, that you can create for free on [Facebook's Developer Page](http://developers.facebook.com/apps). 

Facebook Pictures takes your latest 5 albums and selects the most liked and commented pictures from them. You can choose how many pictures you want to pull using the widget options.

Albums works in a similar way, but only the most recently updated ones are pulled.

You can embed pictures from both your personal Profile and any Pages you manage. 

== Installation ==

1. Install either via the WordPress.org plugin directory, or by uploading the files to your server.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Create a new Facebook App using [Facebook's Developer Page](http://developers.facebook.com/apps). Make sure you enter your website as the App's domain page.
4. Enter your newly created App ID and App Secret in the plugin's settings page (Settings -> Facebook Pictures)
5. Link your Facebook Account. You'll be asked for permissions on Facebook.
6. You are all set! Find the Facebook Pictures widget under Appearance -> Widgets, drag it into your sidebar or any widgetized area and select how many pictures or albums you want to display.

== Frequently asked questions ==

= Can I use Facebook Pictures on my template? =

Sure you can. You'll have to use the global variable $fbdata and call either the $fbdata->get_pictures($amount, $offset) method for pictures or $fbdata->get_albums($amount) for albums. This will return an array containing the data, that you can display and style as needed.

= Will this plugin slow down my page? =

It really depends on how many pictures you have on your Facebook account: the more pictures, the more it takes Facebook to give us the information we need. In any case, data is stored on your site for 24 hours, so worst case scenario you'll see a load delay on one pageview every day.


== Screenshots ==

1. Admin Panel Settings
2. An example of how the widget would look like in your sidebar. Two pictures are being pulled.

== Changelog ==

= 0.1 =
* Plugin is released
