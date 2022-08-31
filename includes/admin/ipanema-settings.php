<?php

if ( !defined( 'is_admin' ) ) {
	exit;
}

// Register function to be called when the plugin is activated
register_activation_hook( __FILE__, 'ifr_set_settings_default_options' );


// Function called upon plugin activation to initialize the options values
// if they are not present already
function ifr_set_settings_default_options() { 
	ifr_settings_get_options();
}

// Function to retrieve options from database as well as create or 
// add new options
function ifr_settings_get_options() {
    $options = get_option( 'ifr_options', array() );

    $new_options['g_recaptcha']    = sanitize_text_field( '' ); 
	$new_options['e_notification'] = sanitize_email( '' ); 
	
    $merged_options  = wp_parse_args( $options, $new_options ); 
    $compare_options = array_diff_key( $new_options, $options );

    if ( empty( $options ) || !empty( $compare_options ) ) {
        update_option( 'ifr_options', $merged_options );
    }
    
	return $merged_options;
}


/*****************************************************************
 * Administration page menu item   
 *****************************************************************/
add_action( 'admin_menu', 'ifr_settings_settings_menu', 1 );

function ifr_settings_settings_menu() {
	global $options_page;

	$options_page = 
		add_options_page( 
			esc_html__( 'Ipanema Configuration', 'ipanema-film-reviews' ),
			esc_html__( 'Ipanema', 'ipanema-film-reviews' ),
			'manage_options',
			'ifr-settings',
			'ifr_settings_config_page' );
	
	if ( !empty( $options_page ) ) {
		add_action( 'load-' . $options_page, 'ifr_settings_help_tabs' );
	}
}

// Function called to render the contents of the plugin
// configuration page
function ifr_settings_config_page() {
	// Retrieve plugin configuration options from database
	$options = ifr_settings_get_options();
	global $options_page;
	?>

	<div id="ifr-general" class="wrap">
		
		<h2><?php esc_html_e( 'Ipanema Settings', 'ipanema-film-reviews' ) ?></h2><br />

		<?php if ( isset( $_GET['message'] ) && $_GET['message'] == '1' ):?>
			<div id='message' class='updated fade'>
				<p><strong><?php esc_html_e( 'Settings saved successfully', 'ipanema-film-reviews' ) ?></strong></p>
			</div>
		<?php endif; ?>

		<form method="post" action="admin-post.php">

			<input type="hidden" name="action" value="save_ifr_options" />

			<!-- Adding security through hidden referrer field -->
			<?php wp_nonce_field( 'ifr_plugin_options' ); ?>
			<!-- Security fields for meta box save processing --> 
			<?php wp_nonce_field( 'closedpostboxes', 
				'closedpostboxesnonce', false ); ?> 
			<?php wp_nonce_field( 'meta-box-order', 
				'meta-box-order-nonce', false ); ?> 

			<div id="poststuff" class="metabox-holder"> 
				<div id="post-body"> 
					<div id="post-body-content"> 
					<?php do_meta_boxes( $options_page, 'normal', $options) ; 
					?> 
					<input type="submit" value="Submit" class="button-primary"/>      
					</div> 
				</div> 
				<br class="clear"/> 
			</div>
		</form>
	</div>
	
	<script type="text/javascript">  
    //<![CDATA[  
    jQuery( document ).ready( function( $ ) { 
      // close postboxes that should be closed 
      $( '.if-js-closed' ) .removeClass( 'if-js-closed' ).
         addClass( 'closed' ); 
 
      // postboxes setup 
      postboxes.add_postbox_toggles
         ( '<?php esc_html_e(  $options_page, 'ipanema-film-reviews' ); ?>' ); 
            }); 
 
        //]]>  
    </script> 
<?php }

/*****************************************************************
 * Processing and storing admin page post data
 *****************************************************************/

add_action( 'admin_init', 'ifr_settings_admin_init' );

function ifr_settings_admin_init() {
	add_action( 'admin_post_save_ifr_options', 'ifr_settings_options_process' );
}

function ifr_settings_options_process() {

	// Check that user has proper security level
	if ( !current_user_can( 'manage_options' ) )
		wp_die( 'Not allowed' );
        
	// Check that nonce field created in configuration form is present
	check_admin_referer( 'ifr_plugin_options' );

	// Retrieve original plugin options array
	$options = ifr_settings_get_options();

	// Cycle through all text form fields and store their values in the options array
	foreach ( array( 'g_recaptcha' ) as $option_name ) {
		if ( isset( $_POST[$option_name] ) ) {
			$options[$option_name] =
				sanitize_text_field($_POST[$option_name]);
		}
	}

	foreach ( array( 'e_notification' ) as $option_name ) {
		if ( isset( $_POST[$option_name] ) ) {
			$options[$option_name] =
				sanitize_email($_POST[$option_name]);
		}
	}

	// Store updated options array to database
	update_option( 'ifr_options', $options );

	// Redirect the page to the configuration form that was
	// processed
	wp_redirect( add_query_arg( array( 'page' => 'ifr-settings', 'message' => '1' ), admin_url( 'options-general.php' ) ) );
	exit;
}

/*****************************************************************
 * Custom help page in Ipanema Settings
 *****************************************************************/
 
function ifr_settings_help_tabs() {
	$screen = get_current_screen();

	$screen->add_help_tab( array(
		'id'       => 'ifr-help-instructions',
		'title'    => esc_html( 'Instructions', 'ipanema-film-reviews' ),
		'callback' => 'ifr_settings_help_instructions',
	) );

	$screen->add_help_tab( array(
		'id'       => 'ifr-help-faq',
		'title'    => esc_html( 'FAQ', 'ipanema-film-reviews' ),
		'callback' => 'ifr_settings_help_faq',
	) );

	$screen->set_help_sidebar( '<p><strong>' . esc_html__( 'Ipanema Film Reviews ', 'ipanema-film-reviews' ) . '</strong>' . esc_html__( 'is a WordPress plugin for those who love movies and cinema.', 'ipanema-film-reviews' ) . '</p>' );
	
	global $options_page; 
 
	add_meta_box(
		'ifr_grecaptcha_meta_box', 
		esc_html__( 'Google reCaptcha', 'ipanema-film-reviews' ), 
		'ifr_settings_grecaptcha_meta_box', 
		$options_page, 
		'normal', 
		'core');
	
	add_meta_box(
		'ifr_enotification_meta_box', 
		esc_html__( 'Email Notification', 'ipanema-film-reviews' ), 
		'ifr_settings_enotification_meta_box', 
		$options_page, 
		'normal', 
		'core'); 
}

function ifr_settings_help_instructions() { 

	$admin_mail   = sanitize_email( get_option( 'admin_email' ) );
	$google_url   = sanitize_url( "https://www.google.com/recaptcha/intro/invisible.html?ref=producthunt" );
	$google_intro = sanitize_url( "https://developers.google.com/recaptcha/intro" );

	?>
	<h3><?php esc_html_e( 'Follow the instructions: ', 'ipanema-film-reviews' ) ?></h3>
	<dl>
  		<dt><strong><?php esc_html_e( 'You must integrate Google reCaptcha.', 'ipanema-film-reviews' ) ?></strong></dt>
		<dd><?php esc_html_e( 'reCAPTCHA is a free service that protects your website from spam and abuse.', 'ipanema-film-reviews' ) ?></dd>
		<dd><?php esc_html_e( 'reCAPTCHA uses an advanced risk analysis engine and adaptive CAPTCHAs to keep automated software from engaging in abusive activities on your site. It does this while letting your valid users pass through with ease.', 'ipanema-film-reviews' ) ?></dd>
		<dd><a target="_blank" href="<?php esc_html_e( $google_url ) ?>"><?php esc_html_e( 'Read more', 'ipanema-film-reviews' ) ?></a></dd>
		<dd><a target="_blank" href="<?php esc_html_e( $google_intro ) ?>"><?php esc_html_e( 'Developer\'s Guide', 'ipanema-film-reviews' ) ?></a></dd>
		<dt><strong><?php esc_html_e( 'Change email notification.', 'ipanema-film-reviews' ) ?></strong></dt>
		<dd><?php esc_html_e( 'When a user submits a new film review you will receive an email notification. You can set your preferred email to receive these kind of notifications.', 'ipanema-film-reviews' ) ?></dd>
		<dd><?php esc_html_e( 'If you don\'t, you will receive the notification message in the "Administration Email Address".', 'ipanema-film-reviews' ) ?></dd>
		<dd><?php esc_html_e( 'In your case, the default "Administration Email Address" is: ', 'ipanema-film-reviews' ) ?><strong><?php esc_html_e( $admin_mail, 'ipanema-film-reviews' ); ?></strong></dd>
	</dl>
<?php }

function ifr_settings_help_faq() { 
	
	$admin_mail = sanitize_email( get_option( 'admin_email' ) );

	?>
	<h3><?php esc_html_e( 'FAQ:', 'ipanema-film-reviews' ) ?></h3>
	<dl>
		<dt><strong><?php esc_html_e( 'What happens if you don\'t set the "Google reCaptcha"?', 'ipanema-film-reviews' ) ?></strong></dt>
		<dd><?php esc_html_e( 'If you don\'t provide the Google reCaptcha value, users won\'t be allowed to submit any film review from the "Add a Film Review" form.', 'ipanema-film-reviews' ) ?></dd>
		<dt><strong><?php esc_html_e( 'What happens if you don\'t set the "Email Notification"?', 'ipanema-film-reviews' ) ?></strong></dt>
		<dd><?php esc_html_e( 'If you don\'t provide an email for the plugin\'s notifications, you will receive these kind of alerts in your "Administration Email Address".', 'ipanema-film-reviews' ) ?></dd>
		<dd><?php esc_html_e( 'In your case, the email is: ', 'ipanema-film-reviews' ) ?><strong><?php esc_html_e( $admin_mail, 'ipanema-film-reviews' ); ?></strong>.</dd>
	</dl>
<?php }

add_action( 'admin_enqueue_scripts', 'ifr_settings_load_admin_scripts' );

function ifr_settings_load_admin_scripts() {
    global $current_screen;
    global $options_page;

    if ( $current_screen->id == $options_page ) {
        wp_enqueue_script( 'common' );
        wp_enqueue_script( 'wp-lists' );
        wp_enqueue_script( 'postbox' );
    }
}

function ifr_settings_grecaptcha_meta_box( $options ) { ?> 
    <table>
		<tr>
			<td style="width: 150px"><?php esc_html_e( 'Site Key: ', 'ipanema-film-reviews' ) ?></td>
			<td><input type="text" name="g_recaptcha" value="<?php esc_html_e( $options['g_recaptcha'], 'ipanema-film-reviews' ); ?>"/><br /></td>
		</tr>
	</table>  
<?php }

function ifr_settings_enotification_meta_box( $options ) { ?> 
    <table>
		<tr>
			<td style="width: 150px"><?php esc_html_e( 'Email Notification: ', 'ipanema-film-reviews' ) ?></td>
			<td><input type="text" name="e_notification" value="<?php esc_html_e( $options['e_notification'], 'ipanema-film-reviews' ); ?>"/><br /></td>
		</tr>
	</table>  
<?php }

