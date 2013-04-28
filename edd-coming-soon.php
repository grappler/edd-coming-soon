<?php
/*
Plugin Name: EDD Coming Soon
Plugin URI: http://sumobi.com/store/edd-download-images/
Description: Allows "coming soon" downloads in Easy Digital Downloads.
Version: 1.0
Author: Andrew Munro - Sumobi
Author URI: http://sumobi.com
License: GPL-2.0+
License URI: http://www.opensource.org/licenses/gpl-license.php
*/


/**
 * Internationalization
 * @since 1.0 
 */
function edd_cs_textdomain() {
	load_plugin_textdomain( 'edd-di', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}
add_action( 'init', 'edd_cs_textdomain' );


/**		
 * Render the coming soon checkbox
 * @since 1.0 
*/
function edd_cs_render_option( $post_id ) { 

	$coming_soon = get_post_meta( $post_id, 'edd_coming_soon', true );

	?>
	<p>
		<label for="edd_coming_soon">
			<input type="checkbox" name="edd_coming_soon" id="edd_coming_soon" value="1" <?php checked( 1, $coming_soon ); ?> />
			<?php echo apply_filters( 'edd_cs_toggle_text', __( 'Enable coming soon download', 'edd-cs' ) ); ?>
		</label>
	</p>

<?php }
add_action( 'edd_meta_box_fields', 'edd_cs_render_option', 10 );


/**		
 * Hook into save filter and add the download image fields
 * @since 1.0 
*/
function edd_cs_metabox_fields_save( $fields ) {

	$fields[] = 'edd_coming_soon';

	return $fields;
}
add_filter( 'edd_metabox_fields_save', 'edd_cs_metabox_fields_save' );


/**		
 * Check if it's a coming soon download
 * @since 1.0 
*/
function edd_cs_is_coming_soon() {

	global $post;

	$coming_soon = isset( $post->ID ) ? get_post_meta( $post->ID, 'edd_coming_soon', true ) : '';

	if( $coming_soon )
		return true;

}


/**		
 * Hook into currency filter and if the download is a coming soon download, return no currency
 * @since 1.0 
*/
function edd_cs_edd_currency( $currency ) {
	
	if( edd_cs_is_coming_soon() )
		return false;
	else
		return $currency;
	
}
add_filter( 'edd_currency', 'edd_cs_edd_currency' );


/**		
 * Filter price function so it shows 'Coming Soon' instead of price
 * @since 1.0 
*/
function edd_cs_filter_price( $price, $download_id ) {

	if ( edd_cs_is_coming_soon() ) {
		$price = __( 'Coming Soon', 'edd-cs' );
		return apply_filters( 'edd_cs_coming_soon_text', $price );
	}
	else 
		return $price;

}
add_filter( 'edd_download_price', 'edd_cs_filter_price', 10, 2 );


/**		
 * Only show purchase form if it's not a coming soon download
 * @since 1.0
 * @todo override purchase_link shortcode also
*/
function edd_cs_purchase_download_form( $purchase_form, $args ) {

	if( edd_cs_is_coming_soon() ) {
		return false;
	}
	else
		return $purchase_form;
}
add_filter( 'edd_purchase_download_form', 'edd_cs_purchase_download_form', 10, 2 );


/**		
 * Prevent download from being added to cart (free or priced) with ?edd_action=add_to_cart&download_id=XXX
 * @since 1.0 
*/

function edd_cs_pre_add_to_cart( $download_id ) {

	if( get_post_meta( $download_id, 'edd_coming_soon', true ) )
		wp_die( apply_filters( 'edd_cs_pre_add_to_cart', __( 'This download cannot be purchased', 'edd-cs' ) ), '', 'back_link=true' );

}
add_action( 'edd_pre_add_to_cart', 'edd_cs_pre_add_to_cart' );