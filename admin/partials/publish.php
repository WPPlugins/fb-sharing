<?php
/* Publication file when publish posts */
/* Generate POST requests on Facebook Graph API to publish on user profile/page */
    
if( get_post_gallery( $post) != false ) {
    try {
        
        $gallery = get_post_gallery( $post, false );
        $gallery_images = explode( ',', $gallery[ 'ids' ] );
        
        foreach( $gallery_images as $image ) {
            $image_array = wp_get_attachment_image_src( $image, 'post' );
            $images_url[] = $image_array[0];
        }
        $multi_images_url = array_chunk( $images_url, 20 );

        $fb_gallery = Facebook_Sharing::fs_fb_post_request( $fb_page_id . '/albums', array(
            'access_token'	=>	$fb_page[ 'access_token' ],
            'name'			=>	html_entity_decode( sanitize_text_field( $post->post_title ), ENT_QUOTES ),
        ));
        
        $fb_post_id = $fb_gallery[ 'id' ];
        
        if( isset( $fb_post_id ) ) {
            while( current( $multi_images_url ) ) {
                foreach( $multi_images_url[key($multi_images_url)] as $image_url ) {
                    $batch_array[] = array(
                        'method'		=>	'POST',
                        'relative_url'	=>	$fb_post_id . '/photos',
                        'body'			=>	'url=' . $image_url,
                    );
                }
                $multi_batch_array[ key( $multi_images_url ) ] = $batch_array;
                unset($batch_array);
                next($multi_images_url);
            }
            
            while( current( $multi_batch_array ) ) {
                $curl_array[key($multi_batch_array)] = curl_init();
                curl_setopt_array( $curl_array[key($multi_batch_array)], array(
                    CURLOPT_URL 			=>	'https://graph.facebook.com',
                    CURLOPT_RETURNTRANSFER 	=> 	true,
                    CURLOPT_HEADER 			=>	false,
                    CURLOPT_POST 			=>	2,
                    CURLOPT_POSTFIELDS 		=>	array(
                        'access_token'	=>	$fb_page[ 'access_token' ],
                        'batch'			=>	json_encode( current( $multi_batch_array ) ),
                    )
                ));
                next( $multi_batch_array );
            }
            $multi_curl = curl_multi_init();
            while( current( $curl_array ) ) {
                curl_multi_add_handle( $multi_curl, current( $curl_array ) );
                next( $curl_array );
            }
            
            $active = null;
            do {
                $multi_curl_exec = curl_multi_exec( $multi_curl, $active );
            } while( $multi_curl_exec == CURLM_CALL_MULTI_PERFORM );
            
            while( $active && $multi_curl_exec == CURLM_OK ) {
                if( curl_multi_select( $multi_curl ) != -1 ) {
                    do {
                        $multi_curl_exec = curl_multi_exec( $multi_curl, $active );
                    } while( $multi_curl_exec == CURLM_CALL_MULTI_PERFORM );
                }
            }
            
            foreach( $curl_array as $curl ) {
                $results_json = curl_multi_getcontent( $curl );
                $results_array = json_decode( $results_json, true );
                foreach( $results_array as $result_array ) {
                    $results_body[] = json_decode( $result_array[ 'body' ], true );
                }
            }
            
            foreach( $results_body as $result_body ) {
                if( array_key_exists( 'id', $result_body ) == false ) {
                    $publish_state = 'graph-error';
                    $fb_post_id = '';
                    break;
                } else {
                    $publish_state = 'published';
                }
            }					
        } else {
            $publish_state = 'graph-error';
            $fb_post_id = '';
        }
    } catch( Exception $e ) {
        $publish_state = 'error';
        $fb_post_id = '';
    }
    
    $published_posts = get_option( 'fs_published_posts' );					
    $published_posts[ $post->ID ] = array(
        'fb_post_id'	=>	$fb_post_id,
        'fb_page_id'    =>  $fb_page_id,
        'state'			=>	$publish_state,
    );			
    update_option( 'fs_published_posts', $published_posts );
    
} elseif( has_post_thumbnail( $post ) ) {
    try {
        $image = wp_get_attachment_image_src( get_post_thumbnail_id( $post ), 'post' );
        $image_url = $image[ 0 ];
        $response = Facebook_Sharing::fs_fb_post_request( $fb_page_id . '/photos', array(
            'access_token'	=>	$fb_page[ 'access_token' ],
            'caption'		=>	html_entity_decode( sanitize_text_field( apply_filters( 'the_content', $post->post_content ) ), ENT_QUOTES ),
            'url'			=>	$image_url
        ));
        
        if ( !empty( $response[ 'post_id' ] ) ) {
            $publish_state = 'published';
            $fb_post_id = $response[ 'post_id' ];
        } else {
            $publish_state = 'graph-error';
            $fb_post_id = '';
        }
        
    } catch( Exception $e ) {
        $publish_state = 'error';
        $fb_post_id = '';
    }
    
    $published_posts = get_option( 'fs_published_posts' );
    $published_posts[ $post->ID ] = array(
        'fb_post_id'	=>	$fb_post_id,
        'fb_page_id'    =>  $fb_page_id,
        'state'			=>	$publish_state,
    );				
    update_option( 'fs_published_posts', $published_posts );
    
} else {
    try {
        $response = Facebook_Sharing::fs_fb_post_request( $fb_page_id . '/feed', array(
            'access_token'	=>	$fb_page['access_token'],
            'message'	=>	html_entity_decode( sanitize_text_field( apply_filters( 'the_content', $post->post_content ) ), ENT_QUOTES ),				 
        ) );
        
        $fb_post_id = $response[ 'id' ];

        if ( !empty( $fb_post_id ) ) {					
            $publish_state = 'published';			 
        } else {
            $publish_state = 'graph-error';
            $fb_post_id = '';
        }
    } catch( Exception $e ) {
        $publish_state = 'error';
        $fb_post_id = '';
    }
    
    $published_posts = get_option( 'fs_published_posts' );					
    $published_posts[ $post->ID ] = array(
        'fb_post_id'	=>	$fb_post_id,
        'fb_page_id'    =>  $fb_page_id,
        'state'			=>	$publish_state,
    );				
    update_option( 'fs_published_posts', $published_posts );
}