<?php

/**
* @since        1.0.0
* @package      Facebook_Sharing
* @subpackage   Facebook_Sharing
* @author       Jean-Baptiste Aramendy <contact@jba-development.fr>
*/

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

delete_option( 'fs_fb_options_userid' );
delete_option( 'fs_fb_options_username' );
delete_option( 'fs_fb_options_token' );
delete_option( 'fs_fb_options_token_date' );
delete_option( 'fs_fb_options_token_expiration' );
delete_option( 'fs_options_enable_as' );
delete_option( 'fs_options_fb_pages' );
delete_option( 'fs_options_fb_page' );
delete_option( 'fs_published_posts' );
delete_option( 'fs_options_version' );