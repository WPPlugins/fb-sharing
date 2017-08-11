<?php
/**
 * Fired during plugin deactivation
 *
 * @link       http://jba-development.fr
 * @since      1.0.0
 *
 * @package    Facebook_Sharing
 * @subpackage Facebook_Sharing/includes
 */
/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Facebook_Sharing
 * @subpackage Facebook_Sharing/includes
 * @author     Jean-Baptiste Aramendy <plugins@jba-development.fr>
 */
class Facebook_Sharing_Deactivator {

	public static function deactivate() {        
        delete_option( 'fs_fb_options_userid' );
		delete_option( 'fs_fb_options_username' );
        delete_option( 'fs_fb_options_token' );
		delete_option( 'fs_fb_options_token_date' );
        delete_option( 'fs_fb_options_token_expiration' );
		delete_option( 'fs_options_enable_as' );
		delete_option( 'fs_options_fb_pages' );
		delete_option( 'fs_options_fb_page' );        
	}
}