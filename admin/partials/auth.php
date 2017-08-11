<?php
/* Get access token from authentification server */
/* Clear all datas when click on logout */

if( isset( $_GET[ 'fs_access_token' ] ) ) {
    if( current_user_can( 'manage_options' ) ) {
        if( ! wp_verify_nonce( $_REQUEST[ 'wpnonce' ], 'fb_auth_nonce' ) ){
            $auth_error = __( 'The connection isn\'t safe. Please try again.', 'fb-sharing' );
        } else {
            $access_token = $_GET[ 'fs_access_token' ];
            $token_expiration = $_GET[ 'fs_token_expiration' ];
            $user_id = $_GET[ 'fs_user_id' ];
            $user_name = Facebook_Sharing::fs_fb_get_request( $user_id, array( 'access_token' => $access_token ), 'name' );

            update_option( 'fs_fb_options_token', $access_token );
            update_option( 'fs_fb_options_token_expiration', $token_expiration );
            update_option( 'fs_fb_options_userid', $user_id );
            update_option( 'fs_fb_options_username', $user_name );
            update_option( 'fs_options_enable_as', 'true' );
        }
    }
}

if( isset( $_POST[ 'fs_fb_option_logout' ] ) ) {
    if( current_user_can( 'manage_options' ) ) {
        update_option( 'fs_fb_options_token', '' );
        update_option( 'fs_fb_options_token_expiration', '' );
        update_option( 'fs_fb_options_userid', '' );
        update_option( 'fs_fb_options_username', '' );
        update_option( 'fs_options_fb_pages', array() );
        update_option( 'fs_options_enable_as', 'false' );
    }
}