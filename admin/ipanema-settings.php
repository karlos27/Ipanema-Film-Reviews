<?php

if ( !defined( 'is_admin' ) ) {
	exit;
}

// Register function to be called when the plugin is activated
register_activation_hook( __FILE__, 'ifr_set_default_options' );

// Function called upon plugin activation to initialize the options values
// if they are not present already
function ifr_set_default_options() { 
	ifr_get_options();
}

// Function to retrieve options from database as well as create or 
// add new options
function ifr_get_options() {
    $options = get_option( 'ifr_options', array() );

    $new_options['g_recaptcha'] = ''; 
	
    $merged_options = wp_parse_args( $options, $new_options ); 

    $compare_options = array_diff_key( $new_options, $options );   
    if ( empty( $options ) || !empty( $compare_options ) ) {
        update_option( 'ifr_options', $merged_options );
    }
    return $merged_options;
}


/*****************************************************************
 * Administration page menu item   
 *****************************************************************/
add_action( 'admin_menu', 'ifr_settings_menu', 1 );

function ifr_settings_menu() {

	global $options_page;

	$options_page = 
		add_options_page( 
			esc_html__( 'Ipanema Configuration', 'ipanema-film-reviews' ),
			esc_html__( 'Ipanema', 'ipanema-film-reviews' ),
			'manage_options',
			'ifr-settings',
			'ifr_config_page' );
	
	if ( !empty( $options_page ) ) {
		add_action( 'load-' . $options_page, 'ifr_help_tabs' );
	}
}

// Function called to render the contents of the plugin
// configuration page
function ifr_config_page() {
	// Retrieve plugin configuration options from database
	$options = ifr_get_options();
	global $options_page;
	?>

	<div id="ifr-general" class="wrap">
	<h2><?php esc_html_e( 'Ipanema Settings', 'ipanema-film-reviews' ) ?></h2><br />

	<?php if (isset( $_GET['message'] ) && $_GET['message'] == '1'):?>
	<div id='message' class='updated fade'><p><strong><?php esc_html_e( 'Settings saved successfully', 'ipanema-film-reviews' ) ?></strong></p></div>
	<?php endif; ?>

	<form method="post" action="admin-post.php">

	 <input type="hidden" name="action" value="save_ifr_options" />

	 	<!-- Adding security through hidden referrer field -->
	 	<?php wp_nonce_field( 'ifr_plugin_recaptcha' ); ?>
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
         ( '<?php echo $options_page; ?>' ); 
            }); 
 
        //]]>  
    </script> 
<?php }

/*****************************************************************
 * Processing and storing admin page post data
 *****************************************************************/

add_action( 'admin_init', 'ifr_admin_init' );

function ifr_admin_init() {
	add_action( 'admin_post_save_ifr_options', 'process_ifr_options' );
}

function process_ifr_options() {

	// Check that user has proper security level

	if ( !current_user_can( 'manage_options' ) )
		wp_die( 'Not allowed' );
        
	// Check that nonce field created in configuration form
	// is present

	check_admin_referer( 'ifr_plugin_recaptcha' );

	// Retrieve original plugin options array
	$options = ifr_get_options();

	// Cycle through all text form fields and store their values
	// in the options array

	foreach ( array( 'g_recaptcha' ) as $option_name ) {
		if ( isset( $_POST[$option_name] ) ) {
			$options[$option_name] =
				sanitize_text_field($_POST[$option_name]);
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
 
function ifr_help_tabs() 
{
	$screen = get_current_screen();
	$screen->add_help_tab( array(
		'id'       => 'ifr-help-instructions',
		'title'    => esc_html( 'Instructions', 'ipanema-film-reviews' ),
		'callback' => 'ifr_help_instructions',
	) );

	$screen->add_help_tab( array(
		'id'       => 'ifr-help-faq',
		'title'    => esc_html( 'FAQ', 'ipanema-film-reviews' ),
		'callback' => 'ifr_help_faq',
	) );

	$screen->set_help_sidebar( '<p>This is the sidebar content</p>' );
	
	global $options_page; 
 
	add_meta_box(
		'ifr_grecaptcha_meta_box', 
		esc_html( 'Google Recaptcha', 'ipanema-film-reviews' ), 
		'ifr_grecaptcha_meta_box', 
		$options_page, 
		'normal', 
		'core'); 
}

function ifr_help_instructions() { ?>
	<p>These are instructions explaining how to use this plugin.</p>
    <!-- https://developers.google.com/recaptcha/intro -->
<?php }

function ifr_help_faq() { ?>
	<p>These are the most frequently asked questions on the use of this plugin.</p>
<?php }

add_action( 'admin_enqueue_scripts', 'ifr_load_admin_scripts' );

function ifr_load_admin_scripts() {
    global $current_screen;
    global $options_page;

    if ( $current_screen->id == $options_page ) {
        wp_enqueue_script( 'common' );
        wp_enqueue_script( 'wp-lists' );
        wp_enqueue_script( 'postbox' );
    }
}

function ifr_grecaptcha_meta_box( $options ) { ?> 
    <table>
		<tr>
			<td style="width: 150px"><?php esc_html_e( 'Site Key: ', 'ipanema-film-reviews' ) ?></td>
			<td><input type="text" name="g_recaptcha" value="<?php esc_html_e( $options['g_recaptcha'], 'ipanema-film-reviews' ); ?>"/><br /></td>
		</tr>
	</table>  
<?php }

