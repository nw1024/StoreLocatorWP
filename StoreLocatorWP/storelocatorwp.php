<?php
/*
Plugin Name: StoreLocatorWP
Plugin URI:  none
Description: Location custom post types for display on Google Maps
Version:     20180201
Author:      Jeremiah Swicegood
Author URI:  http://www.nw1024.com
License:     none
License URI: none
Text Domain: storelocator
Domain Path: /languages
*/

$GOOGLEKEY = ""; // Google Maps API key goes here
// TODO settings page with API key field

function storelocatorwp_theme() {
    wp_enqueue_style( 'storelocatorwp', plugins_url('/storelocatorwp.css', __FILE__ )  );
}
add_action( 'wp_enqueue_scripts', 'storelocatorwp_theme' );

function storelocatorwp_adminstyle() {
  echo '<style>
  		#locations-form td { vertical-align: top; }
        #locations-form textarea, input[type=text] { width: 100%; vertical-align: top; }
	    #locations-form textarea { height: 8em; }
        </style>';
}

add_action('admin_head', 'storelocatorwp_adminstyle');

function storelocatorwp_register() {
	
	register_post_type( 'location',
		array(
			'labels' => array(
				'name' => __( 'Locations' ),
				'singular_name' => __( 'location' ),
			),
			'public' => true,
			'has_archive' => true,
			'hierarchical' => false,
			'taxonomies' => array('county'),
			'supports' => array(
		            'title',
				    'editor',
					'thumbnail'
		        ),
			'rewrite' => array( 'slug' => 'location' )
		)
	);

	register_taxonomy(
		'county',
		'location',
		array(
			'label' => __( 'County' ),
			'rewrite' => array( 'slug' => 'county' ),
			'hierarchical' => true,
		)
	);

}

add_action( 'init', 'storelocatorwp_register', 0 );

function get_storelocatorwp_template( $archive_template ) {
        global $post;

        if ( is_post_type_archive ( 'location' ) ) {
                $archive_template = dirname( __FILE__ ) . '/archive-location.php';
        }
        return $archive_template;
}

add_filter(  'archive_template',  'get_storelocatorwp_template' ) ;


function storelocatorwp_redirect_homepage() {
    //if ( is_page( 'home' ) ) {
    //    wp_redirect( home_url( '/storelocations/' ) );
    //    exit();
    //}
}

//add_action( 'template_redirect', 'storelocatorwp_redirect_homepage' );

function storelocatorwp_listfull($args) {
	extract($args);
	echo $before_widget;
	echo $before_title;
	echo $after_title; 
	echo $after_widget;   
}

register_sidebar_widget('StoreLocatorWP Listing Full',    'storelocatorwp_listfull');

function storelocatorwp_listspecials($args) {
	extract($args);
	echo $before_widget;
	echo $before_title;
	echo $after_title; 
	echo $after_widget;   
}

register_sidebar_widget('StoreLocatorWP Listing Specials',    'storelocatorwp_listspecials');

function storelocatorwp_listdetails($args) {
	extract($args);
	echo $before_widget;
	echo $before_title;
	echo $after_title; 
	echo $after_widget;   
}

register_sidebar_widget('StoreLocatorWP Listing Details',    'storelocatorwp_listdetails');

class storelocator_Meta_Box {

	public function __construct() {

		if ( is_admin() ) {
			add_action( 'load-post.php',     array( $this, 'init_metabox' ) );
			add_action( 'load-post-new.php', array( $this, 'init_metabox' ) );
		}

	}

	public function init_metabox() {

		add_action( 'add_meta_boxes', array( $this, 'add_metabox'  ) );
		add_action( 'save_post',      array( $this, 'save_metabox' ), 10, 2 );

	}

	public function add_metabox() {

		add_meta_box(
			'location',
			__( 'Information', 'storelocatorwp' ),
			array( $this, 'render_metabox' ),
			'location'
		);

	}
	
	public function render_metabox( $post ) {

		// Add nonce for security and authentication.
		wp_nonce_field( 'storelocator_nonce_action', 'storelocator_nonce' );

		// Retrieve an existing value from the database.
		$storelocator_active = get_post_meta( $post->ID, 'storelocator_active', true );
		$storelocator_desc = get_post_meta( $post->ID, 'storelocator_desc', true );

		$storelocator_lat = get_post_meta( $post->ID, 'storelocator_lat', true );
		$storelocator_lng = get_post_meta( $post->ID, 'storelocator_lng', true );
		$storelocator_address = str_replace("<br />", "\n", get_post_meta( $post->ID, 'storelocator_address', true ) );
		$storelocator_website = get_post_meta( $post->ID, 'storelocator_website', true );
		$storelocator_phone = get_post_meta( $post->ID, 'storelocator_phone', true );

		// Set default values.
		
		if( empty( $storelocator_active ) ) $storelocator_active = '';
		if( empty( $storelocator_desc ) ) $storelocator_desc = '';
		
		if( empty( $storelocator_lat ) ) $storelocator_lat = '';
		if( empty( $storelocator_lng ) ) $storelocator_lng = '';
		if( empty( $storelocator_address ) ) $storelocator_address = '';
		if( empty( $storelocator_website ) ) $storelocator_website = '';
		if( empty( $storelocator_phone ) ) $storelocator_phone = '';

		// Form fields.
		
		echo '<div id="locations-form">';		

		echo '<table class="form-table">';

		echo '	<tr>';
		echo '	<td>';
		echo '  <input type="checkbox" value="checked" name="storelocator_active[]" ' . $storelocator_active . ' id="active" /> Active <br/><br/>';
		echo '  <label for="storelocator_desc" class="storelocator_desc_label">' . __( 'Description', 'storelocatorwp' ) . '</label></td>';
		echo '	</tr>';
		
		echo '	<tr>';
		echo '	<td><input type="text" id="storelocator_desc" name="storelocator_desc" class="storelocator_desc_field" value="' . esc_attr__( $storelocator_desc ) . '"></td>';
		echo '	</tr>';
		
		echo '</table>';

		echo "

		<div style='display: none' id='map'></div>
		
		<script>
		
			var map;
			
			function initialize() {	
				map = new google.maps.Map(document.getElementById('map'), {
				center: {lat: 47.4464241, lng: -122.468337},
				zoom: 15
				});	  
			}
			
			function getaddress() {		
				var addressquery = document.getElementById('title').value;
				  // Search for Google's office in Australia.
				  var request = {
					location: map.getCenter(),
					radius: '30000',
					query: addressquery
				  };
				  var service = new google.maps.places.PlacesService(map);
				  service.textSearch(request, addresscallback);
			}
			
			// Checks that the PlacesServiceStatus is OK, and adds a marker
			// using the place ID and location from the PlacesService.
			function addresscallback(results, status) {	
			  if (status == google.maps.places.PlacesServiceStatus.OK) {
				console.log(results);
				document.getElementById('storelocator_address').value = results[0].formatted_address;  
				
			  }
			  geocode();

			}

			function geocode() {
				var geocoder = new google.maps.Geocoder();
				var address = document.getElementById('storelocator_address').value;
				geocoder.geocode({ 'address': address }, function (results, status) {
					if (status == google.maps.GeocoderStatus.OK) {
						var latitude = results[0].geometry.location.lat();
						var longitude = results[0].geometry.location.lng();
		
					}
					document.getElementById('storelocator_lat').value = latitude
					document.getElementById('storelocator_lng').value = longitude	
				});
			}

		</script>
		";
		
		echo '<table class="form-table">';

		echo '	<tr>';
		echo '	<td colspan="2"><label onclick="getaddress()" for="storelocator_address" class="storelocator_address_label">' . __( 'Address', 'storelocatorwp' ) . '</label></td>';
		echo '	</tr>';
		
		echo '	<tr>';		
		echo '	<td colspan="2"><textarea id="storelocator_address" name="storelocator_address" class="storelocator_address_field">' . esc_attr__( $storelocator_address ) . "</textarea>";
		echo '	</td>';
		echo '	</tr>';

		echo '	<tr>';
		echo '	<td><label onclick="geocode()" for="storelocator_lat" class="storelocator_lat_label">' . __( 'Latitude', 'storelocatorwp' ) . '</label></td>';
		echo '	<td><label onclick="geocode()" for="storelocator_lng" class="storelocator_lng_label">' . __( 'Longitude', 'storelocatorwp' ) . '</label></td>';
		echo '	</tr>';
		echo '	<tr>';		
		echo '	<td><input type="text" id="storelocator_lat" name="storelocator_lat" class="storelocator_lat_field" value="' . esc_attr__( $storelocator_lat ) . '"></td>';
		echo '	<td><input type="text" id="storelocator_lng" name="storelocator_lng" class="storelocator_lng_field" value="' . esc_attr__( $storelocator_lng ) . '"></td>';
		echo '	</tr>';

		echo '	<tr>';
		echo '	<td><label for="storelocator_lat" class="storelocator_lat_label">' . __( 'Website', 'storelocatorwp' ) . '</label></td>';
		echo '	<td><label for="storelocator_lng" class="storelocator_lng_label">' . __( 'Phone', 'storelocatorwp' ) . '</label></td>';
		echo '	</tr>';
		echo '	<tr>';
		echo '	<td><input type="text" id="storelocator_website" name="storelocator_website" class="storelocator_website_field" value="' . esc_attr__( $storelocator_website ) . '"></td>';
		echo '	<td><input type="text" id="storelocator_phone" name="storelocator_phone" class="storelocator_phone_field" value="' . esc_attr__( $storelocator_phone ) . '"></td>';
		echo '	</tr>';
		
		echo '</table>';
		
		echo '</div>';

		echo "
		
		 <script async defer src='https://maps.googleapis.com/maps/api/js?key={$GOOGLEKEY}&v=3&callback=initialize'>
		    </script>
			
			";

	} 

	public function save_metabox( $post_id, $post ) {

		// Add nonce for security and authentication.
		$nonce_name   = $_POST['storelocator_nonce'];
		$nonce_action = 'storelocator_nonce_action';

		// Check if a nonce is set.
		if ( ! isset( $nonce_name ) )
			return;

		// Check if a nonce is valid.
		if ( ! wp_verify_nonce( $nonce_name, $nonce_action ) )
			return;

		// Check if the user has permissions to save data.
		if ( ! current_user_can( 'edit_post', $post_id ) )
			return;

		// Check if it's not an autosave.
		if ( wp_is_post_autosave( $post_id ) )
			return;

		// Check if it's not a revision.
		if ( wp_is_post_revision( $post_id ) )
			return;

		// Sanitize user input.

		$storelocator_active = isset( $_POST[ 'storelocator_active' ] ) ?  $_POST[ 'storelocator_active' ][0]  : '';
		$storelocator_desc = isset( $_POST[ 'storelocator_desc' ] ) ?  $_POST[ 'storelocator_desc' ]  : '';
		
		$storelocator_lat = isset( $_POST[ 'storelocator_lat' ] ) ? $_POST[ 'storelocator_lat' ]  : '';
		$storelocator_lng = isset( $_POST[ 'storelocator_lng' ] ) ?  $_POST[ 'storelocator_lng' ]  : '';
		
		$storelocator_address = isset( $_POST[ 'storelocator_address' ] ) ?  $_POST[ 'storelocator_address' ]  : '';

		$storelocator_website = isset( $_POST[ 'storelocator_website' ] ) ?  $_POST[ 'storelocator_website' ]  : '';
		$storelocator_phone = isset( $_POST[ 'storelocator_phone' ] ) ?  $_POST[ 'storelocator_phone' ]  : '';

		// Update the meta field in the database.
		update_post_meta( $post_id, 'storelocator_active', $storelocator_active );
		update_post_meta( $post_id, 'storelocator_desc', $storelocator_desc );
		
		update_post_meta( $post_id, 'storelocator_lat', $storelocator_lat );
		update_post_meta( $post_id, 'storelocator_lng', $storelocator_lng );
		update_post_meta( $post_id, 'storelocator_address', str_replace("\r\n", "<br />", $storelocator_address ) );
		update_post_meta( $post_id, 'storelocator_website', $storelocator_website );	
		update_post_meta( $post_id, 'storelocator_phone', $storelocator_phone );	
		
	}

}

new storelocator_Meta_Box;

?>