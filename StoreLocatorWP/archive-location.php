<?php get_header(); ?>

<body>

<?php wp_nav_menu(); ?>
	
<div class='table-wrapper'>

<?php

$markerlist = "";
$markerlist .= "var locations = [";		

while ( have_posts() ) : the_post();			
	if (get_post_meta(get_the_ID(), "storelocator_lat", true) != "" && get_post_meta(get_the_ID(), "storelocator_lng", true) != "") {
		$markerlist .= "{title: '";
		$markerlist .= esc_attr(get_the_title(get_the_ID()));
		$markerlist .= "', location: {lat: ";
		$markerlist .= get_post_meta(get_the_ID(), "storelocator_lat", true);
		$markerlist .= ", lng: ";
		$markerlist .= get_post_meta(get_the_ID(), "storelocator_lng", true);
		$markerlist .= "}, content: '";
		$markerlist .= esc_attr(get_post_meta(get_the_ID(), "storelocator_desc", true));
		$markerlist .= "', permalink: '";
		$markerlist .= get_permalink(get_the_ID());
		$markerlist .= "', image: '";
		$markerlist .= get_the_post_thumbnail( get_the_ID(), 'thumbnail' );
		$markerlist .= "'},";
	}
endwhile;

$markerlist .= "];";

?>

<script  type="text/javascript">

	var map;
	var largeInfoWindow; 
	var bounds;
	var markers = [];
	var container = [];

	<?php echo $markerlist; //locations[] array ?>
	
	function initMap() {
		
		largeInfoWindow = new google.maps.InfoWindow({maxWidth: 250});
		bounds = new google.maps.LatLngBounds();
			
		map = new google.maps.Map(document.getElementById('map'), {
			center: {lat: 41.954387, lng: -87.721742},
			zoom: 13,
			disableDefaultUI: true
		});
			
		// TODO settings page with style selection interface
		
		// Start map styleoptions
		var noPoi = [
			{
				"elementType": "geometry",
				"stylers": [
				{
					"color": "#212121"
				}
				]
			},
			{
				"elementType": "labels.icon",
				"stylers": [
				{
					"visibility": "off"
				}
				]
			},
			{
				"elementType": "labels.text.fill",
				"stylers": [
				{
					"color": "#757575"
				}
				]
			},
			{
				"elementType": "labels.text.stroke",
				"stylers": [
				{
					"color": "#212121"
				}
				]
			},
			{
				"featureType": "administrative",
				"elementType": "geometry",
				"stylers": [
				{
					"color": "#292929"
				}
				]
			},
			{
				"featureType": "administrative.country",
				"elementType": "labels.text.fill",
				"stylers": [
				{
					"color": "#9e9e9e"
				}
				]
			},
			{
				"featureType": "administrative.land_parcel",
				"stylers": [
				{
					"visibility": "off"
				}
				]
			},
			{
				"featureType": "administrative.locality",
				"elementType": "labels.text.fill",
				"stylers": [
				{
					"color": "#bdbdbd"
				}
				]
			},
			{
				"featureType": "poi",
				"elementType": "labels.text.fill",
				"stylers": [
				{
					"color": "#757575"
				}
				]
			},
			{
				"featureType": "poi.park",
				"elementType": "geometry",
				"stylers": [
				{
					"color": "#181818"
				}
				]
			},
			{
				"featureType": "poi.park",
				"elementType": "labels.text.fill",
				"stylers": [
				{
					"color": "#616161"
				}
				]
			},
			{
				"featureType": "poi.park",
				"elementType": "labels.text.stroke",
				"stylers": [
				{
					"color": "#1b1b1b"
				}
				]
			},
			{
				"featureType": "road",
				"elementType": "geometry.fill",
				"stylers": [
				{
					"color": "#2c2c2c"
				}
				]
			},
			{
				"featureType": "road",
				"elementType": "labels.text.fill",
				"stylers": [
				{
					"color": "#8a8a8a"
				}
				]
			},
			{
				"featureType": "road.arterial",
				"elementType": "geometry",
				"stylers": [
				{
					"color": "#373737"
				}
				]
			},
			{
				"featureType": "road.highway",
				"elementType": "geometry",
				"stylers": [
				{
					"color": "#3c3c3c"
				}
				]
			},
			{
				"featureType": "road.highway.controlled_access",
				"elementType": "geometry",
				"stylers": [
				{
					"color": "#4e4e4e"
				}
				]
			},
			{
				"featureType": "road.local",
				"elementType": "labels.text.fill",
				"stylers": [
				{
					"color": "#616161"
				}
				]
			},
			{
				"featureType": "transit",
				"elementType": "labels.text.fill",
				"stylers": [
				{
					"color": "#757575"
				}
				]
			},
			{
				"featureType": "water",
				"elementType": "geometry",
				"stylers": [
				{
					"color": "#000000"
				}
				]
			},
			{
				"featureType": "water",
				"elementType": "labels.text.fill",
				"stylers": [
				{
					"color": "#3d3d3d"
				}
				]
			}
			];
		
		map.setOptions({styles: noPoi});	
		// End map style options
			
		drawMarkers();
		
	} // End initMap()
		
	function drawMarkers() {
		
		for (var i = 0; i < markers.length; i++) {
				markers[i].setMap(null);
		}
		markers = [];

		var totalmarkers = 0;

		for (var i=0; i<locations.length; i++) {
			var position = locations[i].location; 
			var title = locations[i].title;
				totalmarkers++;
				container[title] = locations[i];
				var marker = new google.maps.Marker({
					map: map,
					position: position,
					title: title
					//icon: 'http://www.example.com/image.png'
					// TODO settings page with custom map icon selection field
				});
				marker.addListener('click', function() { 
					populateInfoWindow(this, largeInfoWindow); 
				});
				markers.push(marker);
				bounds.extend(marker.position);
		} 

		if (totalmarkers == 1) {
			map.center = marker.position;
		}
		if (totalmarkers > 1) {
			map.fitBounds(bounds);
		}
	
	}
	
	// Popup info div on marker click
	function populateInfoWindow(marker, infowindow) {
		infowindow.marker = marker;
		infowindow.setContent('<div class="infobox"><a class="infoboxtitle" href="' + container[marker.title].permalink + '">' + marker.title + '</a><a href="' + container[marker.title].permalink + '">' + container[marker.title].image + '</a><p>' + container[marker.title].content + '</p><p><a href="' + container[marker.title].permalink + '">Show all</a></p></div>');
		infowindow.open(map, marker);
	}
	
</script>

<?php
$GOOGLEKEY = ""; // Google Maps API key goes here
// TODO settings page with API key field
print("<script async defer
src='https://maps.googleapis.com/maps/api/js?key={$GOOGLEKEY}&v=3&callback=initMap'>
</script>");
?>

<div id="map"> </div>

</div>

<?php get_footer(); ?>


