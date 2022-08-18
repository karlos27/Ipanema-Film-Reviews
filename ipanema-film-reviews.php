<?php

/**
 * Plugin Name: Ipanema Film Reviews
 * Description: This plugin lets you add a film review system to your WordPress site. Using custom post types, administrators will be able to create and edit film reviews to be published on your site. 
 * Author: segcgonz
 * Version: 1.0
 * Author URI: https://www.linkedin.com/in/carlossegarragonzalez
 * Text Domain: ipanema-film-reviews
 * Domain Path: /languages
 * License: GPL v2 or later
 */

/****************************************************************************
 * Fonts utilitzades:
 * WordPress Plugin Development Cookbook (Second edition) - Yannick Lefebvre
 * How to Internationalize Your Plugin (Plugin Handbook)
 * Plugin Readmes (Plugin Handbook)
 ****************************************************************************/

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
				'name' => esc_html__( 'Film Reviews', 'ipanema-film-reviews' ),
				'singular_name' => esc_html__( 'Film Review', 'ipanema-film-reviews' ),
				'add_new' => esc_html__( 'Add New', 'ipanema-film-reviews' ),
				'add_new_item' => esc_html__( 'Add New Film Review', 'ipanema-film-reviews' ),
				'edit' => esc_html__( 'Edit', 'ipanema-film-reviews' ),
				'edit_item' => esc_html__( 'Edit Film Review', 'ipanema-film-reviews' ),
				'new_item' => esc_html__( 'New Film Review', 'ipanema-film-reviews' ),
				'view' => esc_html__( 'View', 'ipanema-film-reviews' ),
				'view_item' => esc_html__( 'View Film Review', 'ipanema-film-reviews' ),
				'search_items' => esc_html__( 'Search', 'ipanema-film-reviews' ),
				'not_found' => esc_html__( 'No Film Reviews found', 'ipanema-film-reviews' ),
				'not_found_in_trash' => esc_html__( 'No Film Reviews found in Trash', 'ipanema-film-reviews' ),
				'parent' => esc_html__( 'Parent Film Review', 'ipanema-film-reviews' )
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
				'name' => esc_html__( 'Film Genre', 'ipanema-film-reviews' ),
				'add_new_item' => esc_html__( 'Add New Film Genre', 'ipanema-film-reviews' ),
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
 * Adding new section 'Film Review Details'
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
}

// Function to display meta box contents
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
			<td style="width: 150px"><?php _e( 'Author', 'ipanema-film-reviews' ); ?></td>
			<td><input type='text' name='film_review_author_name' value='<?php esc_html_e( $film_author ); ?>' /></td>
		</tr>
    <tr>
			<td style="width: 150px"><?php _e( 'Film Actors', 'ipanema-film-reviews' ); ?></td>
			<td><input type='text' name='film_review_actors_name' value='<?php esc_html_e( $film_actors ); ?>' /></td>
		</tr>
    <tr>
			<td style="width: 150px"><?php _e( 'Film length', 'ipanema-film-reviews' ); ?></td>
			<td><input type='number' min='0' name='film_review_length' value='<?php esc_html_e( $film_length ); ?>' /></td>
		</tr>
    <tr>
			<td style="width: 150px"><?php _e( 'Release date', 'ipanema-film-reviews' ); ?></td>
			<td><input type='date' name='film_review_rdate' value='<?php esc_html_e( $film_rdate ); ?>' /></td>
		</tr>
		<tr>
			<td style="width: 150px"><?php _e( 'Rating', 'ipanema-film-reviews' ); ?></td>
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

      // Display Author Name
      $content .= esc_html__( '<strong>Author: </strong>', 'ipanema-film-reviews' );
      $content .= esc_html( get_post_meta( get_the_ID(), 'film_author', true ) );
      $content .= '<br />';

      // Display actors Name
      $content .= esc_html__( '<strong>Film actors: </strong>', 'ipanema-film-reviews' );
      $content .= esc_html( get_post_meta( get_the_ID(), 'film_actors', true ) );
      $content .= '<br />';

      // Display film Length
      $content .= esc_html__( '<strong>Film length: </strong>', 'ipanema-film-reviews' );
      $content .= intval( get_post_meta( get_the_ID(), 'film_length', true ) );
      $content .= '\' <br />';

      // Display Release Date
      $rdate    = esc_html( get_post_meta( get_the_ID(), 'film_rdate', true ) );

      $content .= esc_html__( '<strong>Release date: </strong>', 'ipanema-film-reviews' );
      $content .= date( 'd/m/Y', strtotime( $rdate ) );
      $content .= '<br />';

      // Display yellow stars based on rating -->
      $content .= esc_html__( '<strong>Rating: </strong><br />', 'ipanema-film-reviews' );

      $nb_stars = intval( get_post_meta( get_the_ID(), 'film_rating', true ) );

      for ( $star_counter = 1; $star_counter <= 5; $star_counter++ ) {
          if ( $star_counter <= $nb_stars ) {
              $content .= '<img src="' . plugins_url( 'media/star-icon.png', __FILE__ ) . '" />';
          } else {
              $content .= '<img src="' .
                  plugins_url( 'media/star-icon-grey.png', __FILE__ ) . '" />';
          }
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
	$query_params = array( 'post_type' => 'film_reviews',
                           'post_status' => 'publish',
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
		$output .= '<tr><th style="width: 350px"><strong>' . esc_html__( 'Title', 'ipanema-film-reviews' ) . '</strong></th>';
		$output .= '<th><strong>' . esc_html__( 'Author', 'ipanema-film-reviews' ) . '</strong></th></tr>';

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

// Function to add columns for author, main actors, movie length, release date and genre in film review listing
// and remove comments columns
function fr_add_columns( $columns ) {
	$columns['film_reviews_author']     = esc_html__( 'Author', 'ipanema-film-reviews' );
  	$columns['film_reviews_actors']     = esc_html__( 'Actors', 'ipanema-film-reviews' );
  	$columns['film_reviews_length']     = esc_html__( 'Length', 'ipanema-film-reviews' );
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
		 _e( 'None Assigned', 'ipanema-film-reviews' ); 
		}
	}
}

// Let's make sortable columns
add_filter( 'manage_edit-film_reviews_sortable_columns', 'fr_column_sortable' );

// Register the author, release date and rating columns are sortable columns
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
                    <label><span class="title"><?php esc_html_e( 'Author', 'ipanema-film-reviews' ); ?></span></label>
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

