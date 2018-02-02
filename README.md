# StoreLocatorWP

WordPress implementation of Google Maps JavaScript API with location custom post type management. Read more about Maps API: https://developers.google.com/maps/documentation/javascript/tutorial

This is a WordPress plugin for creating location post types and displaying them using Google Maps. It is a work in progress, 
and is meant to be a simple starting point for a custom Google Maps plugin, instead of relying on complicated third party solutions. The goal is to provide a solution focused on store locations, for either a list of places to find a product, or a list of related store locations like a chain or franchise.

To use this plugin as is, you will need to edit the PHP files and insert your Google Maps API key. Use find to search $GOOGLEKEY and
enter your key there.

Features so far:

* Create custom post type named Location: Includes extra fields for address, phone, latitude, longitude, short description, and active listing toggle
    
* Automatic latitude and longitude: Fill address field and click Latitude or Longitude label for automatic GPS lookup and filling fields with values
    
* Example template for displaying a Google Map: Displays interactive map with the Google Maps API populated by a category of the location custom post type, includes sample style customization

* Auto zoom and center to bounds of all visible markers on map
