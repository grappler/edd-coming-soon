<?php
/*
Plugin Name: EDD Coming Soon
Plugin URI: http://sumobi.com/shop/edd-coming-soon/
Description: Allows "custom status" downloads (not available for purchase) in Easy Digital Downloads
Version: 1.2
Author: Andrew Munro, Sumobi
Author URI: http://sumobi.com/
Contributors: sc0ttkclark
License: GPL-2.0+
License URI: http://www.opensource.org/licenses/gpl-license.php
*/

// Plugin constants
if ( !defined( 'EDD_COMING_SOON' ) )
	define( 'EDD_COMING_SOON', '1.2' );

if ( !defined( 'EDD_COMING_SOON_URL' ) )
	define( 'EDD_COMING_SOON_URL', plugin_dir_url( __FILE__ ) );

if ( !defined( 'EDD_COMING_SOON_DIR' ) )
	define( 'EDD_COMING_SOON_DIR', plugin_dir_path( __FILE__ ) );


/**
 * Internationalization
 *
 * @since 1.0
 */
function edd_coming_soon_textdomain() {

	load_plugin_textdomain( 'edd-coming-soon', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

}
add_action( 'init', 'edd_coming_soon_textdomain' );


/**
 * Check if it's a Custom Status download
 *
 * @param int $download_id 	Download Post ID
 *
 * @return boolean 			Whether Custom Status is active
 *
 * @since 1.0
 */
function edd_coming_soon_is_active( $download_id = 0 ) {
	global $post;

	if ( empty( $download_id ) && is_object( $post ) && isset( $post->ID ) )
		$download_id = $post->ID;

	if ( !empty( $download_id ) )
		return (boolean) get_post_meta( $download_id, 'edd_coming_soon', true );

	return false;
}


/**
 * Render the Custom Status checkbox
 *
 * @param int 	$post_id Post ID
 *
 * @since 1.0
 */
function edd_coming_soon_render_option( $post_id ) {

	$coming_soon = (boolean) get_post_meta( $post_id, 'edd_coming_soon', true );
	$coming_soon_text = get_post_meta( $post_id, 'edd_coming_soon_text', true );

	// Default
	$default_text = apply_filters( 'edd_cs_coming_soon_text', __( 'Coming Soon', 'edd-coming-soon' ) );
?>
	<p>
		<label for="edd_coming_soon">
			<input type="checkbox" name="edd_coming_soon" id="edd_coming_soon" value="1" <?php checked( true, $coming_soon ); ?> />
			<?php _e( 'Enable Coming Soon / Custom Status download', 'edd-coming-soon' ); ?>
		</label>
	</p>

	<p id="edd_coming_soon_container"<?php echo $coming_soon ? '' : ' style="display:none;"'; ?>>
		<label for="edd_coming_soon_text">
			<input type="text" name="edd_coming_soon_text" id="edd_coming_soon_text" size="45" style="width:110px;" value="<?php echo esc_attr( $coming_soon_text ); ?>" />
			<?php echo sprintf( __( 'Custom Status text (default: <em>%s</em>)', 'edd-coming-soon' ), $default_text ); ?>
		</label>
	</p>
<?php
}
add_action( 'edd_meta_box_fields', 'edd_coming_soon_render_option', 10 );


/**
 * Hook into EDD save filter and add the download image fields
 *
 * @param array $fields 	Array of fields to save for EDD
 *
 * @return array 			Array of fields to save for EDD
 *
 * @since 1.0
 */
function edd_coming_soon_metabox_fields_save( $fields ) {

	$fields[] = 'edd_coming_soon';
	$fields[] = 'edd_coming_soon_text';

	return $fields;

}
add_filter( 'edd_metabox_fields_save', 'edd_coming_soon_metabox_fields_save' );


/**
 * Append custom status text to normal prices and price ranges within the admin price column
 *
 * @return string	The text to display
 *
 * @since 1.2
 */
function edd_coming_soon_admin_price_column( $price, $download_id ) {

	$price .= '<br />' . edd_coming_soon_get_custom_status_text();

	return $price;

}
add_filter( 'edd_download_price', 'edd_coming_soon_admin_price_column', 20, 2 );
add_filter( 'edd_price_range', 'edd_coming_soon_admin_price_column', 20, 2 );


/**
 * Get the custom status text
 *
 * @return string	The custom status text or default 'Coming Soon' text
 *
 * @since 1.2
 */
function edd_coming_soon_get_custom_status_text() {

	if ( ! edd_coming_soon_is_active( get_the_ID() ) )
		return;

	$custom_text = get_post_meta( get_the_ID(), 'edd_coming_soon_text', true );
	$custom_text = !empty ( $custom_text ) ? $custom_text : apply_filters( 'edd_cs_coming_soon_text', __( 'Coming Soon', 'edd-coming-soon' ) );

	// either the custom status or default 'Coming Soon' text

	// admin colum text
	if ( is_admin() )
		return apply_filters( 'edd_coming_soon_display_admin_text', '<strong>' . $custom_text . '</strong>' );
	else
	// front-end text.
		return apply_filters( 'edd_coming_soon_display_text', '<p><strong>' . $custom_text . '</strong></p>' );
}


/**
 * Display the coming soon text. Hooks onto bottom of shortcode.
 * Hook this function to wherever you want it to display
 *
 * @since 1.2
 */
function edd_coming_soon_display_text() {

	echo edd_coming_soon_get_custom_status_text();

}
add_action( 'edd_download_after', 'edd_coming_soon_display_text' );


/**
 * Append coming soon text after main content on single download pages
 *
 * @return $content The main post content
 * @since 1.2
*/
function edd_coming_soon_single_download( $content ) {

	if ( is_singular( 'download' ) && is_main_query() ) {
		return $content . edd_coming_soon_get_custom_status_text();
	}

	return $content;

}
add_filter( 'the_content', 'edd_coming_soon_single_download' );



/**
 * Remove the purchase form if it's not a Custom Status download
 * Purchase form includes the buy button and any options if it's variable priced
 *
 * @param string  $purchase_form Form HTML
 * @param array   $args          Arguments for display
 *
 * @return string Form HTML
 *
 * @since 1.0
 */
function edd_coming_soon_purchase_download_form( $purchase_form, $args ) {

	if ( edd_coming_soon_is_active( $args[ 'download_id' ] ) )
		return '';

	return $purchase_form;
}
add_filter( 'edd_purchase_download_form', 'edd_coming_soon_purchase_download_form', 10, 2 );


/**
 * Prevent download from being added to cart (free or priced) with ?edd_action=add_to_cart&download_id=XXX
 *
 * @param int	$download_id Download Post ID
 *
 * @since 1.0
 */
function edd_coming_soon_pre_add_to_cart( $download_id ) {

	if ( edd_coming_soon_is_active( $download_id ) ) {
		$add_text = apply_filters( 'edd_coming_soon_pre_add_to_cart', __( 'This download cannot be purchased', 'edd-coming-soon' ), $download_id );

		wp_die( $add_text, '', array( 'back_link' => true ) );
	}

}
add_action( 'edd_pre_add_to_cart', 'edd_coming_soon_pre_add_to_cart' );


/**
 * Scripts
 *
 * @since 1.0
 */
function edd_coming_soon_admin_scripts( $hook ) {

	global $post;

	if ( is_object( $post ) && $post->post_type != 'download' ) {
		return;
	}

	wp_enqueue_script( 'edd-cp-admin-scripts', EDD_COMING_SOON_URL . 'js/edd-coming-soon-admin.js', array( 'jquery' ), EDD_COMING_SOON );

}
add_action( 'admin_enqueue_scripts', 'edd_coming_soon_admin_scripts' );
