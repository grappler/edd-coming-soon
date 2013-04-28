=== EDD Coming Soon ===
Contributors: sumobi
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=EFUPMPEZPGW7L
Tags: easy digital downloads, digital downloads, e-downloads, edd, coming soon, sumobi
Requires at least: 3.3
Tested up to: 3.5.1
Stable tag: 1.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Allows "coming soon" downloads in Easy Digital Downloads.

== Description ==

This plugin requires [Easy Digital Downloads](http://wordpress.org/extend/plugins/easy-digital-downloads/ "Easy Digital Downloads"). It allows downloads to be "coming soon", and prevents them from being added to the cart. It does a few things:

1. Adds a checkbox to the download configuration so you can set the download to "coming soon".
1. Replaces the download's price in the pricing admin column with "coming soon".
1. Displays “Coming soon” instead of the price when using the [downloads] shortcode, and anywhere else where the edd_price() function has been called.
1. Prevents the coming soon download from being purchased. The plugin will remove the purchase button and stop the download from being added to cart via the edd_action. Eg ?edd_action=add_to_cart&download_id=XXX

= Filtering the coming soon text =    

Paste this into your functions.php and modify the text below:

    function themename_coming_soon_text() { 
	    return 'Available Soon';
    }
    add_filter( 'edd_cs_coming_soon_text', 'themename_coming_soon_text' );


== Installation ==

1. Upload entire `edd-coming soon` folder to the `/wp-content/plugins/` directory, or just upload the ZIP package via 'Plugins > Add New > Upload' in your WP Admin
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Mark downloads as "coming soon"

== Screenshots ==

1. Download Configuration metabox with new coming soon checkbox option

== Changelog ==

= 1.0 =
* Initial release