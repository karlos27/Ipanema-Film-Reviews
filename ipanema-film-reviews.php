<?php

/**
 * Plugin Name: Ipanema Film Reviews
 * Plugin URI: https://github.com/karlos27/Ipanema-Film-Reviews.git
 * Description: This plugin lets you add a film review system to your WordPress site. Using custom post types, administrators will be able to create and edit film reviews to be published on your site. 
 * Author: segcgonz
 * Author URI: https://www.linkedin.com/in/carlossegarragonzalez
 * Version: 1.0
 * Text Domain: ipanema-film-reviews
 * Domain Path: /languages
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/****************************************************************************
 * Plugin Internationalization
 ****************************************************************************/
define('IFR_TRANSLATION_TEXTDOMAIN', 'ipanema-film-reviews');

add_action( 'init', 'fr_plugin_init' );

function fr_plugin_init() {
	$locale = apply_filters( 'plugin_locale', get_locale(), IFR_TRANSLATION_TEXTDOMAIN );

	// Search for Translation in /wp-content/languages/plugin/
	if (file_exists(trailingslashit( WP_LANG_DIR ) . 'plugins' . IFR_TRANSLATION_TEXTDOMAIN . '-' . $locale . '.mo')) {
		load_plugin_textdomain(IFR_TRANSLATION_TEXTDOMAIN, false, trailingslashit( WP_LANG_DIR ));
	}
	// Search for Translation in /wp-content/languages/
	elseif (file_exists(trailingslashit( WP_LANG_DIR ) . IFR_TRANSLATION_TEXTDOMAIN . '-' . $locale . '.mo')) {
		load_textdomain(IFR_TRANSLATION_TEXTDOMAIN, trailingslashit( WP_LANG_DIR ) . IFR_TRANSLATION_TEXTDOMAIN . '-' . $locale . '.mo');
	// Search for Translation in /wp-content/plugins/ipanema-film-reviews/languages/
	} else {
		load_plugin_textdomain( IFR_TRANSLATION_TEXTDOMAIN, false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	}
}

/****************************************************************************
 * Custom post type 'Film Reviews'
 ****************************************************************************/

add_action( 'init', 'fr_create_film_post_type' );

function fr_create_film_post_type() {
  
  // Reset permalinks rules
  global $wp_rewrite;
  $wp_rewrite->flush_rules();
  
  // Create new section to create and edit film posts
  register_post_type( 'film_reviews',
  		array(
				'labels' => array(
				'name' 					=> esc_html__( 'Film Reviews', 'ipanema-film-reviews' ),
				'singular_name' 		=> esc_html__( 'Film Review', 'ipanema-film-reviews' ),
				'add_new' 				=> esc_html__( 'Add New', 'ipanema-film-reviews' ),
				'add_new_item' 			=> esc_html__( 'Add New Film Review', 'ipanema-film-reviews' ),
				'edit' 					=> esc_html__( 'Edit', 'ipanema-film-reviews' ),
				'edit_item' 			=> esc_html__( 'Edit Film Review', 'ipanema-film-reviews' ),
				'new_item' 				=> esc_html__( 'New Film Review', 'ipanema-film-reviews' ),
				'view' 					=> esc_html__( 'View', 'ipanema-film-reviews' ),
				'view_item' 			=> esc_html__( 'View Film Review', 'ipanema-film-reviews' ),
				'search_items' 			=> esc_html__( 'Search Reviews', 'ipanema-film-reviews' ),
				'not_found' 			=> esc_html__( 'No Film Reviews found', 'ipanema-film-reviews' ),
				'not_found_in_trash' 	=> esc_html__( 'No Film Reviews found in Trash', 'ipanema-film-reviews' ),
				'parent' 				=> esc_html__( 'Parent Film Review', 'ipanema-film-reviews' )
			),
		'public' => true,
		'menu_position' => 20,
		'supports' => array( 'title', 'editor', 'comments', 'thumbnail', 'custom-fields' ),
		'taxonomies' => array( '' ),
		'menu_icon' => 'dashicons-format-gallery',
		'has_archive' => false,
		'exclude_from_search' => true,
		'rewrite' => array( 'slug' => 'film-reviews' )
		)
	);
	
  	/* Add custom taxonomies for films custom post types */    
	register_taxonomy(
		'film_reviews_film_type',
		'film_reviews',
		array(
			'labels' => array(
				'name' 			=> esc_html__( 'Film Genre', 'ipanema-film-reviews' ),
				'add_new_item' 	=> esc_html__( 'Add New Film Genre', 'ipanema-film-reviews' ),
				'new_item_name' => esc_html__( 'New Film Genre Name', 'ipanema-film-reviews' )
			),
			'show_ui' => true,
			// 'meta_box_cb' => false,
			// 'show_in_quick_edit' => false,
			'show_tagcloud' => false,
			'hierarchical' => true
		)
	);
}

/****************************************************************************
 * Adding new section 'Film Review Details', 'Post Source', 'Upload File'
 ****************************************************************************/

// Register function to be called when admin interface is visited
add_action( 'admin_init', 'fr_admin_init' );

// Function to register new meta box for film review post editor
function fr_admin_init() {
	add_meta_box( 
		'fr_review_details_meta_box',
         esc_html__( 'Film Review Details', 'ipanema-film-reviews' ),
        'fr_display_review_details_meta_box',
        'film_reviews', 'normal', 'high' );
	
	add_meta_box( 
		'fr_source_meta_box',
		 esc_html__( 'Source', 'ipanema-film-reviews' ),
		'fr_display_source_meta_box',
		'film_reviews', 'normal', 'high' );
	
	add_meta_box( 
		'fr_upload_file',
		 esc_html__( 'Upload File', 'ipanema-film-reviews' ),
		'fr_upload_meta_box',
		'film_reviews', 'normal', 'high' );
	
	// Remove custom fields meta box
	remove_meta_box( 
		'postcustom', 
		'film_reviews', 'normal' );

}

// Register function to be called when post editor form HTML is output
add_action( 'post_edit_form_tag', 'fr_form_add_enctype' );

// Function to add enctype and encoding types to post editor form
function fr_form_add_enctype() {
	echo ' enctype="multipart/form-data"';
}

// Function to display the Film Review Details meta box contents
function fr_display_review_details_meta_box( $film_review ) { 
	// Retrieve current author, actors, length, release date and rating based on film review ID
	$film_author     = esc_html( get_post_meta( $film_review->ID, 'film_author', true ) );
  	$film_actors 	 = esc_html( get_post_meta( $film_review->ID, 'film_actors', true ) );
  	$film_length     = intval( get_post_meta( $film_review->ID, 'film_length', true ) );
  	$film_rdate      = esc_html( get_post_meta( $film_review->ID, 'film_rdate', true ) );
	$film_rating     = intval( get_post_meta( $film_review->ID, 'film_rating', true ) );
  
	?>
	<table>
		<tr>
			<td style="width: 150px"><?php esc_html_e( 'Film Director', 'ipanema-film-reviews' ); ?></td>
			<td><input type='text' name='film_review_author_name' value='<?php esc_html_e( $film_author ); ?>' /></td>
		</tr>
    <tr>
			<td style="width: 150px"><?php esc_html_e( 'Supporting actors', 'ipanema-film-reviews' ); ?></td>
			<td><input type='text' name='film_review_actors_name' value='<?php esc_html_e( $film_actors ); ?>' /></td>
		</tr>
    <tr>
			<td style="width: 150px"><?php esc_html_e( 'Film length', 'ipanema-film-reviews' ); ?></td>
			<td><input type='number' min='0' name='film_review_length' value='<?php esc_html_e( $film_length ); ?>' /></td>
		</tr>
    <tr>
			<td style="width: 150px"><?php esc_html_e( 'Release date', 'ipanema-film-reviews' ); ?></td>
			<td><input type='date' name='film_review_rdate' value='<?php esc_html_e( $film_rdate ); ?>' /></td>
		</tr>
		<tr>
			<td style="width: 150px"><?php esc_html_e( 'Rating', 'ipanema-film-reviews' ); ?></td>
			<td>
				<select name="film_review_rating">
					<!-- Loop to generate all items in dropdown list -->
					<?php for ( $rating = 5; $rating >= 1; $rating -- ) { ?>
					<option value="<?php esc_html_e( $rating ); ?>" <?php esc_html_e( selected( $rating, $film_rating ) ); ?>><?php esc_html_e( $rating ); esc_html_e( ' stars', 'ipanema-film-reviews' ); ?> 
					<?php } ?>
				</select>
			</td>
		</tr>
    
	</table>

<?php }

// Register function to be called when posts are saved
add_action( 'save_post', 'fr_add_film_review_fields', 10, 2 );

function fr_add_film_review_fields( $film_review_id, $film_review ) {
	// Check post type for film reviews
	if ( 'film_reviews' == $film_review->post_type ) {
		// Store data in post meta table if present in post data
		if ( isset( $_POST['film_review_author_name'] ) ) {
			update_post_meta( $film_review_id, 'film_author', sanitize_text_field( $_POST['film_review_author_name'] ) );
		}

    	if ( isset( $_POST['film_review_actors_name'] ) ) {
			update_post_meta( $film_review_id, 'film_actors', sanitize_text_field( $_POST['film_review_actors_name'] ) );
		}

    	if ( isset( $_POST['film_review_length'] ) && !empty( $_POST['film_review_length'] ) ) {
			update_post_meta( $film_review_id, 'film_length', sanitize_text_field( $_POST['film_review_length'] ) );
		}

   		if ( isset( $_POST['film_review_rdate'] ) && !empty( $_POST['film_review_rdate'] ) ) {
			update_post_meta( $film_review_id, 'film_rdate', sanitize_text_field( $_POST['film_review_rdate'] ) );
		}
		
		if ( isset( $_POST['film_review_rating'] ) && !empty( $_POST['film_review_rating'] ) ) {
			update_post_meta( $film_review_id, 'film_rating', sanitize_text_field( intval( $_POST['film_review_rating'] ) ) );
		}
	}
}

// Function to display the Post/Page Source meta box contents
function fr_display_source_meta_box( $custom_post ) { 
	// Retrieve current source name and address based on post ID
	$custom_source_name 		= esc_html( get_post_meta( $custom_post->ID, 'custom_post_source_name', true ) );
	$custom_source_address 		= esc_html( get_post_meta( $custom_post->ID, 'custom_post_source_address', true ) );
	?>

	<!-- Display fields to enter and edit source name and source address -->
	<table>
		<tr>
			<td style="width: 150px"><?php esc_html_e( 'Source Name', 'ipanema-film-reviews' ); ?></td>
			<td>
				<input type='text' name='custom_post_source_name' value='<?php esc_html_e( $custom_source_name ); ?>' />
			</td>
		</tr>
		<tr>
			<td style="width: 150px"><?php esc_html_e( 'Source Address', 'ipanema-film-reviews' ); ?></td>
			<td>
				<input type='text' name='custom_post_source_address' value='<?php esc_html_e( $custom_source_address ); ?>' />
			</td>
		</tr>
	</table>
<?php }

// Register function to be called when custom post is being saved
add_action( 'save_post', 'fr_add_film_reviews_source_data', 10, 2 );

function fr_add_film_reviews_source_data( $custom_post_id, $custom_post ) {
	// Check post type for posts or pages
	if ( 'film_reviews' == $custom_post->post_type ) {
		// Store data in post meta table if present in post data
		if ( isset( $_POST['custom_post_source_name'] ) ) {
			update_post_meta( $custom_post_id, 'custom_post_source_name', sanitize_text_field( $_POST['custom_post_source_name'] ) );
		}

		if ( isset( $_POST['custom_post_source_address'] ) ) {
			update_post_meta( $custom_post_id, 'custom_post_source_address', sanitize_url( $_POST['custom_post_source_address'] ) );
		}
	}
}

// Display meta box contents
function fr_upload_meta_box( $custom_post )
{ ?>
	<table>
		<tr>
			<td style="width: 150px"><?php esc_html_e( 'PDF Attachment', 'ipanema-film-reviews' ); ?></td>
			<td>
			<?php
				// Retrieve attachment data for post
				$attachment_data = get_post_meta( $custom_post->ID, 'attach_data', true );

				// Display message or post link based on presence of data
				if ( empty( $attachment_data['url'] ) ) {
					esc_html_e( 'No Attachment Present', 'ipanema-film-reviews' );
				} else {
					echo '<a target="_blank" href="' . esc_url( $attachment_data['url'] ) . '">';
					esc_html_e( 'Download Attachment', 'ipanema-film-reviews' );
					echo '</a>';
				}
			?>
			</td>
		</tr>
		<tr>
			<td><?php esc_html_e( 'Upload File', 'ipanema-film-reviews' ); ?></td>
			<td><input name="upload_pdf" type="file" /></td>
		</tr>
		<tr>
			<td><?php esc_html_e( 'Delete File', 'ipanema-film-reviews' ); ?></td>
			<td><input name="delete_attachment" type="submit" class="button-primary" id="delete_attachment" value="Delete Attachment" /></td>
		</tr>
	</table>
<?php }

// Register function to be called when post is being saved
add_action( 'save_post', 'fr_save_uploaded_file', 10, 2 );

function fr_save_uploaded_file( $custom_post_id, $custom_post )
{
	if ( isset($_POST['delete_attachment'] ) ) {
		$attach_data = get_post_meta( $custom_post_id, 'attach_data', true );

		if ( !empty( $attach_data ) ) {
			unlink( $attach_data['file'] );
			delete_post_meta( $custom_post_id, 'attach_data' );
		}		
	} elseif ( 'film_reviews' == $custom_post->post_type ) {
		// Look to see if file has been uploaded by user
		if( array_key_exists( 'upload_pdf', $_FILES ) && !$_FILES['upload_pdf']['error']) {
			// Retrieve information on file type and store lower-case version
			$file_type_array = wp_check_filetype( basename( $_FILES['upload_pdf']['name'] ) );
			$file_ext        = strtolower( $file_type_array['ext'] ); 

			// Display error message if file is not a PDF
			if ( 'pdf' != $file_ext ) {
				wp_die( esc_html__( 'Only files of PDF type are allowed.', 'ipanema-film-reviews' ) );
				exit;
			} else {
				// Send uploaded file data to upload directory 
				$upload_return = wp_upload_bits( $_FILES['upload_pdf']['name'], null, file_get_contents( $_FILES['upload_pdf']['tmp_name'] ) );

				// Replace backslashes with slashes for Windows-bases web servers
				$upload_return['file'] = str_replace( '\\', '/', $upload_return['file'] );

				// Display errors if present. Set upload path data if successful.
				if ( isset( $upload_return['error'] ) && $upload_return['error'] != 0 ) {
					wp_die( esc_html__( 'There was an error uploading your file. The error is: ' . $upload_return['error'], 'ipanema-film-reviews' ) );  
					exit;
				} else {
					$attach_data = get_post_meta( $custom_post_id, 'attach_data', true );

					if ( !empty( $attach_data ) ) {
						unlink( $attach_data['file'] );
					}
					
					update_post_meta( $custom_post_id, 'attach_data', $upload_return );
				}
			}
		}
	}
}


/************************************************************************************
 * Custom layout for 'Film Reviews'
 ************************************************************************************/

add_filter( 'template_include', 'fr_template_include', 1 );

function fr_template_include( $template_path ){
	
	if ( 'film_reviews' == get_post_type() ) {
		if ( is_single() ) {
			// checks if the file exists in the theme first,
			// otherwise install content filter
			if ( $theme_file = locate_template( array( 'single-film_reviews.php' ) ) ) {
				$template_path = $theme_file;
			} else {
				add_filter( 'the_content', 'fr_display_single_film_review', 20 );
			}
		}
	}	
	
	return $template_path;
}

function fr_display_single_film_review( $content ) {
  if ( !empty( get_the_ID() ) ) {
      // Display featured image in right-aligned floating div
      $content .= '<div style="float: right; margin: 10px">';
      $content .= get_the_post_thumbnail( get_the_ID(), 'medium' );
      $content .= '</div>';
  
      $content .= '<div class="custom-content">';

      // Display Director Name
      $content .= '<strong>'; 
	  $content .= esc_html__( 'Film director: ', 'ipanema-film-reviews' );
	  $content .= '</strong>';
      $content .= esc_html( get_post_meta( get_the_ID(), 'film_author', true ) );
      $content .= '<br />';

      // Display actors Name
      $content .= '<strong>'; 
	  $content .= esc_html__( 'Supporting actors: ', 'ipanema-film-reviews' );
	  $content .= '</strong>';
      $content .= esc_html( get_post_meta( get_the_ID(), 'film_actors', true ) );
      $content .= '<br />';

      // Display film Length
      $content .= '<strong>';
	  $content .= esc_html__( 'Film length: ', 'ipanema-film-reviews' );
	  $content .= '</strong>';
      $content .= intval( get_post_meta( get_the_ID(), 'film_length', true ) );
      $content .= '\' <br />';

      // Display Release Date
      $rdate    = esc_html( get_post_meta( get_the_ID(), 'film_rdate', true ) );

	  $content .= '<strong>';
      $content .= esc_html__( 'Release date: ', 'ipanema-film-reviews' );
	  $content .= '</strong>';
      $content .= date( 'd/m/Y', strtotime( $rdate ) );
      $content .= '<br />';

      // Display yellow stars based on rating -->
	  $content .= '<strong>'; 
	  $content .= esc_html__( 'Rating: ', 'ipanema-film-reviews' );
	  $content .= '</strong><br />';

      $nb_stars = intval( get_post_meta( get_the_ID(), 'film_rating', true ) );

      for ( $star_counter = 1; $star_counter <= 5; $star_counter++ ) {
          if ( $star_counter <= $nb_stars ) {
              $content .= '<img src="' . plugins_url( 'media/star-icon.png', __FILE__ ) . '" />';
          } else {
              $content .= '<img src="' .
                  plugins_url( 'media/star-icon-grey.png', __FILE__ ) . '" />';
          }
       }
	   
	   // Display Source 
	   $custom_post_source_name =  
			get_post_meta( get_the_ID(), 'custom_post_source_name', true ); 
	   $custom_post_source_address = 
			get_post_meta( get_the_ID(), 'custom_post_source_address', true ); 
		 
	   if ( !empty( $custom_post_source_name ) &&  
			 !empty( $custom_post_source_address ) ) { 
			$content .= '<div class="source_link"><strong>';
			$content .= esc_html__( 'Source: ', 'ipanema-film-reviews' ); 
			$content .= '</strong><a href="' . esc_url ( $custom_post_source_address ); 
			$content .= '">' . esc_html( $custom_post_source_name ) . '</a></div>';
		}
		
		
		// Display Upload File
		$attachment_data = 
			get_post_meta( get_the_ID(), 'attach_data', true ); 
		
			if ( !empty( $attachment_data ) ) { 
			$content .= '<div class="file_attachment">';
			$content .= '<a target="_blank" href="'; 
			$content .= esc_url( $attachment_data['url'] ); 
			$content .= '">' . esc_html__( 'Download additional information', 'ipanema-film-reviews' ); 
			$content .= '</a></div>'; 
		} 
			

      // Display film review contents
      // $content .= '<br /><br />' . get_the_content( get_the_ID() ) . '</div>';

       return $content;
   }
}

/****************************************************************************
 * Display Film custom post type data in shortcode
 ****************************************************************************/

add_shortcode( 'film-review-list', 'fr_film_review_list' );

// Implementation of short code function
function fr_film_review_list() {
	// Preparation of query array to retrieve 5 film reviews
	$query_params = array( 'post_type' 		=> 'film_reviews',
                           'post_status' 	=> 'publish',
                           'posts_per_page' => 3 );
	
	// Retrieve page query variable, if present
	$page_num = ( get_query_var( 'paged' ) ) ? 
                  get_query_var( 'paged' ) : 1;

	// If page number is higher than 1, add to query array
	if ( $page_num != 1 ) {
		$query_params['paged'] = $page_num;
	}

	// Execution of post query
	$film_review_query = new WP_Query;
    $film_review_query->query( $query_params );
	
	// Check if any posts were returned by query
	if ( $film_review_query->have_posts() ) {
		// Display posts in table layout
		$output = '<table>';
		$output .= '<tr><th style="width: 350px"><strong>' . esc_html__( 'Film Title', 'ipanema-film-reviews' ) . '</strong></th>';
		$output .= '<th><strong>' . esc_html__( 'Film Director', 'ipanema-film-reviews' ) . '</strong></th></tr>';

		// Cycle through all items retrieved
		while ( $film_review_query->have_posts() ) {
			$film_review_query->the_post();
			$output .= '<tr><td><a href="' . get_permalink() . '">';
			$output .= get_the_title( get_the_ID() ) . '</a></td>';
			$output .= '<td>' . esc_html( get_post_meta( get_the_ID(), 'film_author', true ) );
			$output .= '</td></tr>';
		}

		$output .= '</table>';

		// Display page navigation links
		if ( $film_review_query->max_num_pages > 1 ) {
			$output .= '<nav id="nav-below">';
			$output .= '<div class="nav-previous">';
			$output .= get_next_posts_link( '<span class="meta-nav">&larr;</span>' . esc_html__(' Older reviews', 'ipanema-film-reviews' ), $film_review_query->max_num_pages );
			$output .= '</div>';
			$output .= "<div class='nav-next'>";
			$output .= get_previous_posts_link( esc_html__('Newer reviews ', 'ipanema-film-reviews' ) . '<span class="meta-nav">&rarr;</span>', $film_review_query->max_num_pages );
			$output .= '</div>';
			$output .= '</nav>';
		}

		// Reset post data query
		wp_reset_postdata();
	}

	return $output;
}

/****************************************************************************
 * Display additional columns in Film custom post list page
 ****************************************************************************/

// Register function to be called when column list is being prepared
add_filter( 'manage_edit-film_reviews_columns', 'fr_add_columns' );

// Function to add columns for director, main actors, movie length, release date and genre in film review listing
// and remove comments columns
function fr_add_columns( $columns ) {
	$columns['film_reviews_author']     = esc_html__( 'Film Director', 'ipanema-film-reviews' );
  	$columns['film_reviews_actors']     = esc_html__( 'Supporting actors', 'ipanema-film-reviews' );
  	$columns['film_reviews_length']     = esc_html__( 'Film Length', 'ipanema-film-reviews' );
  	$columns['film_reviews_rdate']      = esc_html__( 'Release date', 'ipanema-film-reviews' );
	$columns['film_reviews_rating']     = esc_html__( 'Rating', 'ipanema-film-reviews' );
	$columns['film_reviews_type']       = esc_html__( 'Genre', 'ipanema-film-reviews' );
	unset( $columns['comments'] );

	return $columns;
}

// Register function to be called when custom post columns are rendered
add_action( 'manage_posts_custom_column', 'fr_populate_columns' );

// Function to send data for custom columns when displaying items
function fr_populate_columns( $column ) {
	//global $post;

	// Check column name and send back appropriate data
	if ( 'film_reviews_author' == $column ) {
		$film_author = esc_html( get_post_meta( get_the_ID(), 'film_author', true ) );
		echo $film_author;
	}
  	elseif ( 'film_reviews_actors' == $column ) {
		$film_actors = esc_html( get_post_meta( get_the_ID(), 'film_actors', true ) );
		echo $film_actors;
	}
  	elseif ( 'film_reviews_length' == $column ) {
		$film_length = esc_html( get_post_meta( get_the_ID(), 'film_length', true ) );
		echo $film_length . '\'';
	}
  	elseif ( 'film_reviews_rdate' == $column ) {
		$rdate       = esc_html( get_post_meta( get_the_ID(), 'film_rdate', true ) );
    	$film_rdate  = date( 'd/m/Y', strtotime( $rdate ) );
		echo $film_rdate;
	}
	elseif ( 'film_reviews_rating' == $column ) {
		$film_rating = get_post_meta( get_the_ID(), 'film_rating', true );
		echo $film_rating . esc_html__(' stars', 'ipanema-film-reviews');
	}
	elseif ( 'film_reviews_type' == $column ) {
		$film_types = wp_get_post_terms( get_the_ID(), 'film_reviews_film_type' );

		if ( $film_types ) {
      		echo $film_types[0]->name;
		} else {
			esc_html_e( 'None Assigned', 'ipanema-film-reviews' ); 
		}
	}
}

// Let's make sortable columns
add_filter( 'manage_edit-film_reviews_sortable_columns', 'fr_column_sortable' );

// Register the director, release date and rating columns are sortable columns
function fr_column_sortable( $columns ) {
	$columns['film_reviews_author']  = 'film_reviews_author';
  	$columns['film_reviews_rdate']   = 'film_reviews_rdate';
	$columns['film_reviews_rating']  = 'film_reviews_rating';

	return $columns;
}

// Register function to be called when queries are being prepared to
// display post listing
add_filter( 'request', 'fr_column_ordering' );

// Function to add elements to query variable based on incoming arguments
function fr_column_ordering( $vars ) {
	if ( !is_admin() ) {
		return $vars;
	}
        
	if ( isset( $vars['orderby'] ) && 'film_reviews_author' == $vars['orderby'] ) {
		$vars = array_merge( $vars, array(
				'meta_key' => 'film_author',
				'orderby'  => 'meta_value'
		) );
	}
  	elseif ( isset( $vars['orderby'] ) && 'film_reviews_rdate' == $vars['orderby'] ) {
		$vars = array_merge( $vars, array(
				'meta_key' => 'film_rdate',
				'orderby'  => 'meta_value'
		) );
	}
	elseif ( isset( $vars['orderby'] ) && 'film_reviews_rating' == $vars['orderby'] ) {
		$vars = array_merge( $vars, array(
				'meta_key' => 'film_rating',
				'orderby'  => 'meta_value_num'
		) );
	}

	return $vars;
}

/****************************************************************************
 * Adding filters for custom taxonomies to the custom
 * post list page
 ****************************************************************************/

// Register function to be called when displaying post filter drop-down lists
add_action( 'restrict_manage_posts', 'fr_film_type_filter_list' );

// Function to display film type drop-down list for film reviews
function fr_film_type_filter_list() {
	$screen = get_current_screen(); 
    global $wp_query; 
	if ( 'film_reviews' == $screen->post_type ) {
		wp_dropdown_categories( array(
			'show_option_all'	=>  esc_html__( 'Show All Genre', 'ipanema-film-reviews' ),
			'taxonomy'			=>  'film_reviews_film_type',
			'name'				=>  'film_reviews_film_type',
			'orderby'			=>  'name',
			'selected'        =>   
            ( isset( $wp_query->query['film_reviews_film_type'] ) ? 
                 $wp_query->query['film_reviews_film_type'] : '' ),
			'hierarchical'		=>  false,
			'depth'				=>  5,
			'show_count'		=>  false,
			'hide_empty'		=>  true,
		) );
	}
}

// Register function to be called when preparing post query
add_filter( 'parse_query', 'fr_perform_film_type_filtering' );

// Function to modify query variable based on filter selection
function fr_perform_film_type_filtering( $query ) {
	$qv = &$query->query_vars;

	if ( isset( $qv['film_reviews_film_type'] ) &&
         !empty( $qv['film_reviews_film_type'] ) && 
         is_numeric( $qv['film_reviews_film_type'] ) ) {

			$term = get_term_by( 'id',$qv['film_reviews_film_type'],'film_reviews_film_type' );
			$qv['film_reviews_film_type'] = $term->slug;
    }
}

/****************************************************************************
 * Adding Quick Edit fields for custom categories
 ****************************************************************************/

add_action( 'quick_edit_custom_box', 'fr_display_custom_quickedit_link', 10, 2 );

function fr_display_custom_quickedit_link( $column_name, $post_type ) {
    if ( 'film_reviews' == $post_type ) {
        switch ( $column_name ) {
            case 'film_reviews_author': ?>
                <fieldset class="inline-edit-col-right">
                <div class="inline-edit-col">
                    <label><span class="title"><?php esc_html_e( 'Film director', 'ipanema-film-reviews' ); ?></span></label>
                    <input type="text" name='film_reviews_author_input'
                           id='film_reviews_author_input' value="">
                </div>
            <?php break;
            case 'film_reviews_rating': ?>
                <div class="inline-edit-col">
                    <label><span class="title"><?php esc_html_e( 'Rating', 'ipanema-film-reviews' ); ?></span></label>
                    <select name='film_reviews_rating_input'
                            id='film_reviews_rating_input'>
                    <?php // Generate all items of drop-down list 
                    for ( $rating = 5; $rating >= 1; $rating -- ) { ?> 
                        <option value="<?php echo $rating; ?>">
                        <?php echo $rating; esc_html_e( ' stars', 'ipanema-film-reviews'); ?>
                    <?php } ?> 
                    </select>
                </div>
            <?php break;
            case 'film_reviews_type': ?>
                <div class="inline-edit-col">
                    <label><span class="title"><?php esc_html_e( 'Genre', 'ipanema-film-reviews' ); ?></span></label>
                    <?php
                    $terms = get_terms( 
                             array( 'taxonomy' => 'film_reviews_film_type',
                                    'hide_empty' => false ) );
                    ?>
                    <select name='film_reviews_type_input'
                            id='film_reviews_type_input'>
                    <?php foreach ($terms as $index => $term) {
                        echo '<option class="film_reviews_type-option"';
                        echo 'value="' . $term->term_id . '"';
                        selected( 0, $index );
                        echo '>' . $term->name. '</option>';
                    } ?>
                    </select>
                </div>
            <?php break;
        } 
    } 
}

add_action( 'admin_footer', 'fr_quick_edit_js' );

function fr_quick_edit_js() {
    global $current_screen;
    if ( ( 'edit-film_reviews' !== $current_screen->id ) ||
         ( 'film_reviews' !== $current_screen->post_type ) ) {
        return;
    } ?>

    <script type="text/javascript">
    function set_inline_film_reviews( filmReviewArray ) {
        // revert Quick Edit menu so that it refreshes properly
        inlineEditPost.revert();
        var inputfilmAuthor = 
            document.getElementById('film_reviews_author_input');
        		inputfilmAuthor.value = filmReviewArray[0];
 
        var inputRating =
            document.getElementById('film_reviews_rating_input');
        for (i = 0; i < inputRating.options.length; i++) {
            if ( inputRating.options[i].value == filmReviewArray[1] ) {
                inputRating.options[i].setAttribute( 'selected',
                                                     'selected' );
            } else {
                inputRating.options[i].removeAttribute( 'selected' );
            }
        } 
 
        var inputfilmType =
            document.getElementById('film_reviews_type_input');
        for (i = 0; i < inputfilmType.options.length; i++) {
            if ( inputfilmType.options[i].value == filmReviewArray[2] ) {
                inputfilmType.options[i].setAttribute( 'selected',
                                                       'selected' );
            } else {
                inputfilmType.options[i].removeAttribute( 'selected' );
            }
        } 
    }
 	</script>
 <?php }
 
 add_filter( 'post_row_actions', 'fr_quick_edit_link', 10, 2 );
 
 function fr_quick_edit_link( $actions, $post ) {
    global $current_screen;
    $post_id = '';

    if ( ( isset( $current_screen ) && 
           $current_screen->id != 'edit-film_reviews' &&
           $current_screen->post_type != 'film_reviews' ) 
         || ( isset( $_POST['screen'] ) &&
              $_POST['screen'] != 'edit-film_reviews' ) ) {
        return $actions;
    }

    if ( !empty( $post->ID ) ) {
        $post_id = $post->ID;
    } elseif ( isset( $_POST['post_ID'] ) ) {
        $post_id = intval( $_POST['post_ID'] );
    }

    if ( !empty( $post_id ) ) {
        $film_author 		= esc_html( get_post_meta( $post_id, 
                                     'film_author', true ) ); 
        $film_rating 		= esc_html( get_post_meta( $post_id, 
                                     'film_rating', true ) );
        $film_reviews_types = wp_get_post_terms( $post_id, 
                                     'film_reviews_film_type',
                                     array( 'fields' => 'all' ) );
		if ( empty( $film_reviews_types ) ) {
			$film_reviews_types[0] = (object) array( 'term_id' => 0 );
		}
 
        $idx = 'inline hide-if-no-js';
        $actions[$idx] = '<a href="#" class="editinline" title="';
        $actions[$idx] .= esc_attr( esc_html__( 'Edit this item inline', 'ipanema-film-review' ) ) . '" ';
        $actions[$idx] .= " onclick=\"var filmReviewArray = new Array('";
        $actions[$idx] .= "{$film_author}', '{$film_rating}', ";
        $actions[$idx] .= "'{$film_reviews_types[0]->term_id}');";
        $actions[$idx] .= "set_inline_film_reviews(filmReviewArray)\">";
        $actions[$idx] .= esc_html__( 'Quick&nbsp;Edit', 'ipanema-film-reviews' );
        $actions[$idx] .= '</a>';
    }
    return $actions;
}

add_action( 'save_post', 'fr_save_quick_edit_data', 10, 2 );

function fr_save_quick_edit_data( $ID = false, $post = false ) {
    // Do not save if auto-saving, not film reviews, no permissions
    if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) ||
         ( isset( $_POST['post_type'] ) && 'film_reviews' != $_POST['post_type'] ) ||
         !current_user_can( 'edit_page', $ID ) ) {
        return $ID;
    }

    $post = get_post( $ID );
    if ( !empty( $post ) && 'revision' != $post->post_type ) {
        if ( isset( $_POST['film_reviews_author_input'] ) ) {
            update_post_meta( $ID, 'film_author', 
              sanitize_text_field( $_POST['film_reviews_author_input'] ) ); 
        }
 
        if ( isset( $_POST['film_reviews_rating_input'] ) ) {
            update_post_meta( $ID, 'film_rating', 
                intval( $_POST['film_reviews_rating_input'] ) ); 
        }
 
        if ( isset( $_POST['film_reviews_type_input'] ) ) {
            $term = term_exists( 
                        intval( $_POST['film_reviews_type_input'] ),
                                'film_reviews_film_type' );
            if ( !empty( $term ) ) {
                wp_set_object_terms( $ID, 
                    intval( $_POST['film_reviews_type_input'] ), 
                            'film_reviews_film_type' );
            }
        }
    } 
}

/****************************************************************************
 * Client-side content submission form
 ****************************************************************************/
// Declare shortcode and specify function to be called when found
add_shortcode( 'submit-film-review', 'fr_film_review_form' );

// Function to replace shortcode with content when found
function fr_film_review_form() { 

	// make sure user is logged in
	if ( !is_user_logged_in() ) {
		esc_html_e( '<p>You need to be a site member to be able to submit film reviews. Sign up to gain access!</p>', 'ipanema-film-review' );
		return;
	}
	?>
	<h3><?php esc_html_e( 'Add a Film Review', 'ipanema-film-review' ) ?></h3>
	<form method="post" id="add_film_review" action="">
		<!-- Nonce fields to verify visitor provenance -->
		<?php wp_nonce_field( 'add_review_form', 'fr_user_form' ); ?>
		
		<!-- Display confirmation message to users who submit a film review -->
		<?php if ( !empty( $_GET['add_review_message'] ) ) { ?>
		<div style="margin: 8px;border: 1px solid #ddd;background-color: #ff0;">
			<?php esc_html_e( 'Thank for your submission!', 'ipanema-film-review' ) ?>
		</div>
		<?php } ?>
		
	    <!-- Post variable to indicate user-submitted items -->
		<input type="hidden" name="user_film_review_fe" value="1" />
		
		<table>
			<tr>
				<td><?php esc_html_e( 'Film Title', 'ipanema-film-review' ) ?></td>
				<td><input type="text" name="film_title_fe" /></td>
			</tr>
			<tr>
				<td><?php esc_html_e( 'Film Director', 'ipanema-film-review' ) ?></td>
				<td><input type="text" name="film_author_fe" /></td>
			</tr>
			<tr>
				<td><?php esc_html_e( 'Supporting Actors', 'ipanema-film-review' ) ?></td>
				<td><input type="text" name="film_actors_fe" /></td>
			</tr>
			<tr>
				<td><?php esc_html_e( 'Film Length', 'ipanema-film-review' ) ?></td>
				<td><input type="number" name="film_length_fe" /></td>
			</tr>
			<tr>
				<td><?php esc_html_e( 'Release Date', 'ipanema-film-review' ) ?></td>
				<td><input type="date" name="film_rdate_fe" /></td>
			</tr>
			<tr>
				<td><?php esc_html_e( 'Film Review', 'ipanema-film-review' ) ?></td>
				<td><textarea name="film_review_text_fe"></textarea></td>
			</tr>
			<tr>
				<td><?php esc_html_e( 'Rating', 'ipanema-film-review' ) ?></td>
				<td>
					<select name="film_review_rating_fe">
					<?php
					// Generate all rating items in drop-down list
					for ( $rating = 5; $rating >= 1; $rating-- ) { ?>
						<option value="<?php echo $rating; ?>"><?php echo $rating; ?> stars
					<?php } ?>
					</select>
				</td>
			</tr>
			<tr>
				<td><?php esc_html_e( 'Genre', 'ipanema-film-review' ); ?></td>
				<td>
					<?php 

					// Retrieve array of all film types in system
					$film_types = get_terms( 'film_reviews_film_type', array( 'orderby' => 'name', 'hide_empty' => 0 ) );

					// Check if film types were found
					if ( !is_wp_error( $film_types ) && !empty( $film_types ) ) {
						echo '<select name="film_review_film_type">';

						// Display all film types
						foreach ( $film_types as $film_type ) {				
							echo '<option value="' . $film_type->term_id . '">' . $film_type->name . '</option>';
						}		
						echo '</select>';
					} ?>
				</td>
			</tr>
			<tr>
				<td><?php esc_html_e( 'Source Name', 'ipanema-film-reviews' ); ?></td>
				<td><input type="text" name="source_name_fe" /></td>
			</tr>
			<tr>
				<td><?php esc_html_e( 'Source Address', 'ipanema-film-reviews' ); ?></td>
				<td><input type="text" name="source_address_fe" /></td>
			</tr>
			<tr>
				<td><?php esc_html_e( 'Upload File ', 'ipanema-film-reviews' ); ?></td>
				<td><input type="file" name="upload_featuredimage"/></td>
			</tr>
		</table>

		<input type="submit" name="submit" value="Submit Review" />
	</form>

<?php }

// Let's save data from front-end form

add_action( 'template_redirect', 'fr_match_new_film_reviews' );

function fr_match_new_film_reviews( $template ) {	
	if ( !empty( $_POST['user_film_review_fe'] ) ) {
		fr_process_user_book_reviews();
	} else {
		return $template;
	}		
}

function fr_process_user_book_reviews() {

	// Check that all required fields are present and non-empty
	if ( wp_verify_nonce( $_POST['fr_user_form'], 'add_review_form' ) && 
		 !empty( $_POST['film_title_fe'] ) && 
		 !empty( $_POST['film_author_fe'] ) &&
		 !empty( $_POST['film_actors_fe'] ) && 
		 !empty( $_POST['film_length_fe'] ) &&
		 !empty( $_POST['film_rdate_fe'] ) &&
		 !empty( $_POST['film_review_text_fe'] ) &&
		 !empty( $_POST['film_review_rating_fe'] ) &&
		 !empty( $_POST['film_review_film_type'] ) &&
		 !empty( $_POST['source_name_fe'] ) &&
		 !empty( $_POST['source_address_fe'] ) &&
		 !empty( $_POST['upload_featuredimage'] ) ) {

		// Create array with received data
		$new_film_review_data = array(
				'post_status' 	=> 'draft', // Drafts posts are not yet published
				'post_title' 	=> $_POST['film_title_fe'],
				'post_type' 	=> 'film_reviews',
				'post_content' 	=> $_POST['film_review_text_fe']
			);

		// Insert new post in site database
		// Store new post ID from return value in variable
		$new_film_review_id = wp_insert_post( $new_film_review_data );

		// Store the rest of film data
		add_post_meta( $new_film_review_id, 'film_author', wp_kses( $_POST['film_author_fe'], array() ) );
		add_post_meta( $new_film_review_id, 'film_actors', wp_kses( $_POST['film_actors_fe'], array() ) );
		add_post_meta( $new_film_review_id, 'film_length', (int) $_POST['film_length_fe'] );
		add_post_meta( $new_film_review_id, 'film_rdate', wp_kses( $_POST['film_rdate_fe'], array() ) );
		add_post_meta( $new_film_review_id, 'film_rating', (int) $_POST['film_review_rating_fe'] );
		add_post_meta( $new_film_review_id, 'custom_post_source_name', wp_kses( $_POST['source_name_fe'], array() ) );
		add_post_meta( $new_film_review_id, 'custom_post_source_address', wp_kses( $_POST['source_address_fe'], array() ) );
		
		// add_post_meta( $new_film_review_id, 'attach_data', wp_kses( $_POST['upload_pdf_fe'], array() ) );

		// Set film type on post
		wp_set_post_terms( $new_film_review_id, $_POST['film_review_film_type'], 'film_reviews_film_type' );	
		// if ( term_exists( $_POST['film_review_film_type'], 'film_reviews_film_type' ) ) {
			// wp_set_post_terms( $new_film_review_id, $_POST['film_review_film_type'], 'film_reviews_film_type' );
		// }

		// Redirect browser to film review submission page
		$redirect_address = ( empty( $_POST['_wp_http_referer'] ) ? site_url() : $_POST['_wp_http_referer'] );
		wp_redirect( add_query_arg( 'add_review_message', '1', $redirect_address ) );
		exit;
	} else {
		// Display error message if any required fields are missing
		// or if form did not have valid nonce fields.		
		$abort_message = 'Some fields were left empty. Please '; 
        $abort_message .= 'go back and complete the form.'; 
        wp_die( $abort_message ); 
		exit;
	}
}

/****************************************************************************
 * Fonts utilitzades:
 * WordPress Plugin Development Cookfilm (Second edition) - Yannick Lefebvre
 * How to Internationalize Your Plugin (Plugin Handfilm)
 * Plugin Readmes (Plugin Handfilm)
 ****************************************************************************/