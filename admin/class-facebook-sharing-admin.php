<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://jba-development.fr
 * @since      1.0.0
 *
 * @package    Facebook_Sharing
 * @subpackage Facebook_Sharing/admin
 */
/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Facebook_Sharing
 * @subpackage Facebook_Sharing/admin
 * @author     Jean-Baptiste Aramendy <plugins@jba-development.fr>
 */
class Facebook_Sharing_Admin {
	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;
	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;
	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}
	
	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/facebook-sharing-admin.css', array(), $this->version, 'all' );
	}
    
    /**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

	}
    
    /**
     * Add admin menu page
     *
     * @since 1.0.0
     */
    public function add_menu_pages() {
		add_menu_page(
			'Facebook Sharing',
			'Facebook Sharing',
			'manage_options',
			'facebook-sharing-menu',
			array( $this, 'fs_admin_menu' ),
			'dashicons-facebook'
		);
    }
     
	/**
	* Admin HTML menu
	*
	* @since 1.0.0
	*/
	function fs_admin_menu() {
		
		// Facebook Authentification
		include 'partials/auth.php';
		
		// Save options
		if( isset( $_POST[ 'fs_option_submit' ] ) ) {
			foreach( $_POST as $key => $value ) {
				if( substr( $key, 0, 10 ) == 'fs_options' ) {					
					update_option( $key, $value );
				}
			}
			$fs_options_updated = true;
		}
		
		// Get options
        $fs_token = get_option( 'fs_fb_options_token' );
        $fs_pages = get_option( 'fs_options_fb_pages' );
        $fs_user_id = get_option( 'fs_fb_options_userid' );
		
		// Get managed Facebook pages
		if( !empty( $fs_token ) && empty( $fs_pages ) ) {
			$fb_pages_array = Facebook_Sharing::fs_fb_get_request( 'me/accounts', array( 'access_token' => $fs_token ) );
			$fb_pages[ $fs_user_id ] = ( array(
				'access_token'	=>	$fs_token,
				'name'			=>	__( 'Your profile', 'fb-sharing' ),
				'id'			=>	$fs_user_id
			));
			foreach( $fb_pages_array['data'] as $fb_page ) {
				$fb_pages[ $fb_page['id'] ] = array(
					'access_token'	=>	$fb_page['access_token'],
					'name'			=>	$fb_page['name'],
					'id'			=>	$fb_page['id']
				);
			}
			update_option( 'fs_options_fb_pages', $fb_pages );
			update_option( 'fs_options_fb_page', $fs_user_id );
		}
        
		// Admin page HTML
		?>
		<div class="wrap">
			<h1><?php echo get_admin_page_title(); ?></h1>
			
			<?php //Notification bars
			// Options saved
			if( isset( $fs_options_updated ) ) { echo '<div class="updated notice is-dismissible"><p><strong>' . __( 'Options saved.', 'fb-sharing' ) . '</strong></p><button type="button" class="notice-dismiss"></div>'; }
			// Authentification error
			if( isset( $auth_error ) ) { echo '<div class="error notice is-dismissible"><p>' . $auth_error . '</p><button type="button" class="notice-dismiss"></div>'; } 
			?>
			
			<div class="fb-sharing-cell" id="fb-sharing-content">
				<div class="card">
					<table class="form-table">				
						<tr>
							<th><?php _e( 'Facebook User ID', 'fb-sharing' ); ?></th>
							<td><input type="text" name="fs_fb_options_userid" value="<?php echo get_option( 'fs_fb_options_userid' ); ?>" readonly><p class="description"><?php if( !empty( $fs_token ) ) { echo get_option( 'fs_fb_options_username' ); } else { _e( 'This field is filled automatically when you login to Facebook', 'fb-sharing' ); } ?></td>
						</tr>
					</table>
					<?php 
					$nonce = wp_create_nonce( 'fb_auth_nonce' );
					if( empty( $fs_token ) ) { ?>
					<form method="post" action="<?php echo admin_url( 'admin.php?page=facebook-sharing-menu' ); ?>">
						<p><a class="button button-primary" href="https://plugins.jba-development.fr/fs_facebook_auth/fs_facebook_auth.php?webdomain=<?php echo urlencode( admin_url( 'admin.php?page=facebook-sharing-menu' ) ); ?>&sitename=<?php urlencode( bloginfo( 'name' ) ); ?>&wpnonce=<?php echo $nonce; ?>" target="_blank" ><?php _e( 'Connect to Facebook', 'fb-sharing' ); ?></a></p>
						<p><?php _e( 'By clicking this button, you will be redirecting to an external authentification server with HTTPS connection.<br>It\'s necessary to make a connection with Facebook. We don\'t collect or use any personnal (or public) data.', 'fb-sharing' ); ?></p>
					</form>
					<?php
						_e( '<h3>What we will do with your Facebook profile ?</h3>', 'fb-sharing' );
						_e( '<p>All we will make is publish your WordPress posts to your Facebook Profile. Nothing else !</p>', 'fb-sharing' );
						_e( '<p>We don\'t include any ad, featured link or copyright. What you see on your WordPress post, you\'ll see that on your Facebook post.</p>', 'fb-sharing' );
					} elseif( !empty( $fs_token ) ) { ?>
					<form method="post" action="<?php echo admin_url( 'admin.php?page=facebook-sharing-menu' ); ?>">
						<p><a class="button button-secondary" href="https://plugins.jba-development.fr/fs_facebook_auth/fs_facebook_auth.php?webdomain=<?php echo urlencode( admin_url( 'admin.php?page=facebook-sharing-menu' ) ); ?>&sitename=<?php urlencode( bloginfo( 'name' ) ); ?>&wpnonce=<?php echo $nonce; ?>" target="_blank" ><?php _e( 'Renew connection to Facebook', 'fb-sharing' ); ?></a> <?php submit_button( __( 'Logout', 'fb-sharing' ), 'delete', 'fs_fb_option_logout', false ); ?></p>
						<p><?php _e( 'Your connection to Facebook is available until: ', 'fb-sharing' ); ?> <?php if( get_option( 'fs_fb_options_token_expiration' ) == '0' ) { _e( 'Unlimited', 'fb-sharing' ); } else { echo date( __( 'Y-m-d', 'fb-sharing' ), get_option( 'fs_fb_options_token_expiration' ) ); } ?></p>
					</form>
					<?php } ?>
				</div>
				<?php if( !empty( $fs_token ) ) { ?>
				<div class="card">
					<form method="post" action="<?php echo admin_url( 'admin.php?page=facebook-sharing-menu' ); ?>">
						<table class="form-table">
							<tr>
								<th><?php _e( 'Enable auto-sharing', 'fb-sharing' ); ?></th>
								<td>
									<label for="fs_options_enable_as_true"><input type="radio" id="fs_options_enable_as_true" name="fs_options_enable_as" value="true" <?php if( get_option( 'fs_options_enable_as' ) == 'true' ) { echo 'checked'; } ?> > <?php _e( 'Enabled', 'fb-sharing' ); ?></label>
									<br>
									<label for="fs_options_enable_as_false"><input type="radio" id="fs_options_enable_as_false" name="fs_options_enable_as" value="false" <?php if( get_option( 'fs_options_enable_as' ) == 'false' ) { echo 'checked'; } ?> > <?php _e( 'Disabled', 'fb-sharing' ); ?></label>
								</td>
							</tr>
							<tr>
								<th><span class="fs-pro-notif">PRO</span><?php _e( 'Enable posts updates', 'fb-sharing' ); ?></th>
								<td>
									<label for="fs_options_enable_mp_true"><input type="radio" id="fs_options_enable_mp_true" name="fs_options_enable_mp" value="true" disabled > <?php _e( 'Enabled', 'fb-sharing' ); ?></label>
									<br>
									<label for="fs_options_enable_mp_false"><input type="radio" id="fs_options_enable_mp_false" name="fs_options_enable_mp" value="false" checked disabled > <?php _e( 'Disabled', 'fb-sharing' ); ?></label>
								</td>
							</tr>
							<tr>
								<th><?php _e( 'Facebook page where auto-share', 'fb-sharing' ); ?></th>
								<td>
									<select name="fs_options_fb_page">									
										<?php
										$fb_pages = get_option( 'fs_options_fb_pages' );
										while( current( $fb_pages ) ) {
											$fb_page = current( $fb_pages ); ?>
											<option value="<?php echo key($fb_pages); ?>" <?php if( get_option( 'fs_options_fb_page' ) == key($fb_pages) ) { echo 'selected'; } ?> ><?php echo $fb_page['name']; ?></option>
										<?php next( $fb_pages ); } ?>
									</select>
								</td>
							</tr>
							<tr>
								<th><span class="fs-pro-notif">PRO</span><?php _e( 'Share only posts with this category', 'fb-sharing' ); ?></th>
								<td>
									<select name="fs_options_post_cat" disabled >
										<option value="" ><?php _e( 'All categories', 'fb-sharing' ); ?></option>
									</select>
								</td>
							</tr>
							<tr>
								<th><span class="fs-pro-notif">PRO</span><?php _e( 'Appearance of Facebook posts', 'fb-sharing' ); ?></th>
								<td>
									<label for="fs_options_fb_post_appearance_all"><input type="radio" id="fs_options_fb_post_appearance_all" name="fs_options_fb_post_appearance" value="all" checked disabled > <?php _e( 'Post content', 'fb-sharing' ); ?></label>
									<br>
									<label for="fs_options_fb_post_appearance_link"><input type="radio" id="fs_options_fb_post_appearance_link" name="fs_options_fb_post_appearance" value="link" disabled > <?php _e( 'Link to post', 'fb-sharing' ); ?></label>
								</td>
							</tr>
						</table>
						<?php submit_button( __( 'Save Changes' ), 'primary', 'fs_option_submit' ); ?>
					</form>
				</div>
				<?php } ?>
			</div>
			<div class="fb-sharing-cell" id="fb-sharing-right-card">
				<div class="card">
					<h2><?php _e( 'Why the Facebook connection isn\'t unlimited ?', 'fb-sharing' ); ?></h2>
					<p><?php _e( 'It\'s only due to Facebook rules. By the past, connections on Facebook via Apps was unlimited. Now, with new security rules, connections are available during 2 months. After that, you will have to renew this connection.', 'fb-sharing' ); ?></p>
				</div>
				<div class="card">
					<h2><?php _e( 'Needs help ?', 'fb-sharing' ); ?></h2>
					<p><?php _e( 'On post screens, a contextual help is available. Just clic on "Help" in the top-right corner of your screen. A tab "Facebook Sharing" is here for you.', 'fb-sharing' ); ?></p>
					<p><?php _e( 'You can also visit the FAQ by clicking <a href="https://wordpress.org/plugins/fb-sharing/faq/" target="_blank">here</a>', 'fb-sharing' ); ?></p>
					<p><?php _e( 'If you don\'t find what you search, please open a topic <a href="https://wordpress.org/support/plugin/fb-sharing" target="_blank">here</a>', 'fb-sharing' ); ?></p>
				</div>
				<div class="card">
					<h2><?php _e( 'Do you like this plugin ?', 'fb-sharing' ); ?></h2>
					<p><?php _e( 'Don\'t hesitate to give it your rate by clicking on the following stars !', 'fb-sharing' ); ?>
					<p id="fb-sharing-rating-stars">
						<a href="https://wordpress.org/support/view/plugin-reviews/fb-sharing?rate=1#postform" target="_blank"><span class="dashicons dashicons-star-filled"></span></a>
						<a href="https://wordpress.org/support/view/plugin-reviews/fb-sharing?rate=2#postform" target="_blank"><span class="dashicons dashicons-star-filled"></span></a>
						<a href="https://wordpress.org/support/view/plugin-reviews/fb-sharing?rate=3#postform" target="_blank"><span class="dashicons dashicons-star-filled"></span></a>
						<a href="https://wordpress.org/support/view/plugin-reviews/fb-sharing?rate=4#postform" target="_blank"><span class="dashicons dashicons-star-filled"></span></a>
						<a href="https://wordpress.org/support/view/plugin-reviews/fb-sharing?rate=5#postform" target="_blank"><span class="dashicons dashicons-star-filled"></span></a>
					</p>
				</div>
				<div class="card">
					<h2><?php _e( '<span class="fs-pro-notif">PRO</span> Version', 'fb-sharing' ); ?></h2>
					<p><?php _e( 'More features available now, and more and more in the future !<br>If you buy this PRO version, future releases will be free for life, no additional costs. Visit CodeCanyon website to buy my PRO version of this plugin', 'fb-sharing' ); ?></p>
					<p><a class="button button-primary" href="http://codecanyon.net/item/facebook-sharing-pro/16467783?ref=jaramend" target="_blank"><?php _e( 'Visit CodeCanyon', 'fb-sharing' ); ?></a></p>
				</div>
			</div>
		</div>
		<?php
	}
	
	/**
	 * Adding fields on post/page publish box
	 * Hooked by 'post_submitbox_misc_actions'
	 *
	 * @since 1.0.0
	 */
	public function fs_submit_box() {
		
		global $post;
		
		$postdata = get_post( $post );
		
		if( get_option( 'fs_options_enable_as' ) == 'true' ) {
			if( $postdata->post_type == 'post' ) {
				if( $postdata->post_status == 'publish' ) {
					$published_posts = get_option( 'fs_published_posts' );
					if( isset( $published_posts[ $postdata->ID ] )  && isset( $published_posts[ $postdata->ID ][ 'state' ] ) ) {
						if( $published_posts[ $postdata->ID ][ 'state' ] == 'published' ) {
							$this->fs_submit_box_html( 'published', array( 'postdata' => $postdata, 'published_posts' => $published_posts ) );
						} elseif( $published_posts[ $postdata->ID ][ 'state' ] == 'not-published' ) {
							$this->fs_submit_box_html( 'not-published' );
						} else {
							$this->fs_submit_box_html( 'publish' );
						}
					} else {
						$this->fs_submit_box_html( 'publish' );
					}
				} elseif( $postdata->post_status == 'future' ) {
					$published_posts = get_option( 'fs_published_posts' );
					if( isset( $published_posts[ $postdata->ID ] )  && isset( $published_posts[ $postdata->ID ][ 'state' ] ) ) {
						if( $published_posts[ $postdata->ID ][ 'state' ] == 'future' ) {
							$this->fs_submit_box_html( 'future', array( 'published_post' => $published_posts[ $postdata->ID ] ) );
						} elseif( $published_posts[ $postdata->ID ][ 'state' ] == 'not-published' ) {
							$this->fs_submit_box_html( 'not-published' );
						}
					}
				} elseif( $postdata->post_status != 'publish' ) {
					$this->fs_submit_box_html( 'publish' );
				}
			}
		}
	}
	
	/**
	 * HTML for the submit box in post screens
	 *
	 * @since 1.0.4
	 */
	function fs_submit_box_html( $type, $parameters = array() ) {
		if( $type == 'publish' ) { 
			$fs_pages = get_option( 'fs_options_fb_pages' );
			$fs_page = $fs_pages[ get_option( 'fs_options_fb_page' ) ];
			$fs_page_name = $fs_page[ 'name' ];
			?>
			<div class="misc-pub-section misc-fs-post-action">
				<span id="fs-post-action-label"><?php _e( 'Publish on Facebook', 'fb-sharing' ); ?></span>
				<input type="checkbox" name="fs_post_publish" checked="checked" ><br>
				<span id="fs-post-page-label" ><?php printf( __( 'Page: <span class="description">%1$s</span>', 'fb-sharing' ), $fs_page_name ); ?></span>
			</div>
			<?php 
		} elseif( $type == 'published' ) {
			$postdata = $parameters[ 'postdata' ];
			$published_posts = $parameters[ 'published_posts' ];
			?>
			<div class="misc-pub-section misc-fs-post-action">
			<?php
			if( $published_posts[ $postdata->ID ][ 'state' ] == 'published' ) {
				$post_url = 'https://facebook.com/' . $published_posts[ $postdata->ID ][ 'fb_post_id' ];
				?>
				<span id="fs-post-action-label"><?php printf( __( 'Published <a href="%1$s" target="_blank" >here</a> !', 'fb-sharing' ), esc_url( $post_url ) ); ?></span>
				<?php
			} else {
				?>
				<span id="fs-post-action-label"><?php _e( 'Publication error :\'(', 'fb-sharing' ); ?></span><br>
				<span><?php _e( 'Check your Facebook page to ensure no mistake!', 'fb-sharing' ); ?></span><br>
				<span><?php _e( 'Try again to publish?', 'fb-sharing' ); ?></span>
				<input type="checkbox" name="fs_post_publish" checked="checked" >
			<?php } ?>
		</div>
		<?php
		} elseif( $type == 'not-published' ) {
			$fs_pages = get_option( 'fs_options_fb_pages' );
			$fs_page = $fs_pages[ get_option( 'fs_options_fb_page' ) ];
			$fs_page_name = $fs_page[ 'name' ];
			?>
			<div class="misc-pub-section misc-fs-post-action">
				<span id="fs-post-action-label"><?php _e( 'Publish on Facebook', 'fb-sharing' ); ?></span>
				<input type="checkbox" name="fs_post_publish" ><br>
				<span id="fs-post-page-label" ><?php printf( __( 'Page: <span class="description">%1$s</span>', 'fb-sharing' ), $fs_page_name ); ?></span>
			</div>
			<?php 
		} elseif( $type == 'future' ) {
			$published_post = $parameters[ 'published_post' ];
			$fs_pages = get_option( 'fs_options_fb_pages' );
			$fs_page = $fs_pages[ $published_post[ 'fb_page_id' ] ];
			?>
			<div class="misc-pub-section misc-fs-post-action">
				<span id="fs-post-action-label"><?php _e( 'Publication scheduled', 'fb-sharing' ); ?></span><br>
				<span id="fs-post-page-label" ><?php printf( __( 'Page: <span class="description">%1$s</span>', 'fb-sharing' ), $fs_page[ 'name' ] ); ?></span>
			</div>
			<?php
		}
	}
	
	/**
	 * Adding contextual help in new post screen
	 * Hooked by 'admin_page'
	 *
	 * @since 1.0.0
	 */
	public function fs_post_context_help() {
		$screen = get_current_screen();
		if ( $screen->base == 'post' ) {
			$screen->add_help_tab( array(
				'id'		=>	'fs_post_context_help',
				'title'	=>	'Facebook Sharing',
				'callback'	=>	array( $this, 'fs_post_context_help_html' )
			));
		}
	}
	
	/**
	 * HTML for the contextual help in new post screen
	 *
	 * @since 1.0.0
	 */
	function fs_post_context_help_html() {
		?>
		<p><?php _e( 'To use Facebook Sharing, simply let checked the checkbox <em>Publish on Facebook</em> right before the <em>Publish</em> button. Facebook Sharing will do different actions according to the content of your post :', 'fb-sharing' ); ?></p>
		<ul>
			<li><strong><?php _e( 'Simple post', 'fb-sharing' ); ?></strong> - <?php _e( 'When you publish a simple post (with just text, without any attachment like photos), Facebook Sharing will just publish your post content on the Facebook page have selected on settings.', 'fb-sharing' ); ?></li>
			<li><strong><?php _e( 'Post with featured picture', 'fb-sharing' ); ?></strong> - <?php _e( 'When you publish a post with a featured picture, Facebook Sharing will include this picture on the Facebook post, with the content of your post.', 'fb-sharing' ); ?></li>
			<li><strong><?php _e( 'Post with gallery', 'fb-sharing' ); ?></strong> - <?php _e( 'When you put a pictures gallery on your WordPress post, Facebook Sharing will create an album on your Facebook page and include all the pictures of your post gallery on this new album.<br><strong>Depending of the number of pictures on your gallery, the publication may take time during which your browser will "think".</strong><br>To reduce this time, the plugin will make many parallel requests to publish your pictures. Thereby, your pictures will not be in the same order on your Facebook album and your WordPress gallery.', 'fb-sharing' ); ?></li>
		</ul>
		<p><?php _e( 'In all case, your WordPress post must be <strong>published</strong> (and not a draft, or pending a review) or <strong>programming for publish</strong> to be send on your Facebook page.', 'fb-sharing' ); ?></p>
		<?php
	}
	
	/**
	 * Publish on Facebook when posting
	 * Hooked by 'publish_post' and 'future_post'
	 *
	 * @since 1.0.0
	 */
	public function fs_publish( $ID, $post ) {
		$published_posts = get_option( 'fs_published_posts' );
		if( isset( $_POST[ 'fs_post_publish' ] ) ) {
			$fb_page_id = get_option( 'fs_options_fb_page' );
			if( $post->post_status == 'publish' ) {
				$fb_pages = get_option( 'fs_options_fb_pages' );
				$fb_page = $fb_pages[ $fb_page_id ];
				include 'partials/publish.php';
			} elseif( $post->post_status == 'future' ) {
				$published_posts[ $post->ID ] = array(
					'fb_post_id'	=>	'',
					'fb_page_id'	=>	$fb_page_id,
					'state'			=>	'future',
				);
				update_option( 'fs_published_posts', $published_posts );
			}
		} else {
			if( isset( $published_posts[ $post->ID ] ) ) {
				if( $published_posts[ $post->ID ][ 'state' ] == 'future' ) {
					$fb_pages = get_option( 'fs_options_fb_pages' );
					$fb_page_id = $published_posts[ $post->ID ][ 'fb_page_id' ];
					$fb_page = $fb_pages[ $fb_page_id ];
					include 'partials/publish.php';
				}
			} else {
				$published_posts = get_option( 'fs_published_posts' );					
				$published_posts[ $post->ID ] = array(
					'fb_post_id'	=>	'',
					'fb_page_id'    =>  '',
					'state'			=>	'not-published',
				);				
				update_option( 'fs_published_posts', $published_posts );
			}
		}
	}
	
	/**
	 * Delete post reference on fs_published_posts option
	 * Hooked by 'delete_post'
	 *
	 * @since 1.0.0
	 */
	public function fs_delete_post( $ID ) {
		$fs_published_posts = get_option( 'fs_published_posts' );
		if( array_key_exists( $ID, $fs_published_posts ) ) {
			unset( $fs_published_posts[ $ID ] );
			update_option( 'fs_published_posts', $fs_published_posts );
		}
	}
}