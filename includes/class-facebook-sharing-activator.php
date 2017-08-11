<?php
/**
 * Fired during plugin activation
 *
 * @link       http://jba-development.fr
 * @since      1.0.0
 *
 * @package    Facebook_Sharing
 * @subpackage Facebook_Sharing/includes
 */
/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Facebook_Sharing
 * @subpackage Facebook_Sharing/includes
 * @author     Jean-Baptiste Aramendy <plugins@jba-development.fr>
 */
class Facebook_Sharing_Activator {
	/**
	 * Register of plugin options
     *
	 * @since    1.0.0
	 */
	public static function activate() {
        add_option( 'fs_fb_options_userid', '' );
		add_option( 'fs_fb_options_username', '' );
        add_option( 'fs_fb_options_token', '');
		add_option( 'fs_fb_options_token_date', '' );
		add_option( 'fs_fb_options_token_expiration', '');
		add_option( 'fs_options_enable_as', 'true');
		add_option( 'fs_options_fb_pages', array() );
		add_option( 'fs_options_fb_page', '');
		add_option( 'fs_published_posts', '' );
	}
}