<?php
/*
Plugin Name: Autocomplete Location Field for Contact Form 7 
description: Woo Customer auto fill fields in cf7
Version: 10.0
Author: Gravity Master
Requires Plugins: contact-form-7
License: GPL2
*/

/* Stop immediately if accessed directly. */
if ( ! defined( 'ABSPATH' ) ) {
   die();
}

/* All constants should be defined in this file. */
if ( ! defined( 'GWAA_PLUGIN_DIR' ) ) {
   define( 'GWAA_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
}
if ( ! defined( 'GWAA_PLUGIN_BASENAME' ) ) {
   define( 'GWAA_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
}
if ( ! defined( 'GWAA_PLUGIN_URL' ) ) {
   define( 'GWAA_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}

/* Auto-load all the necessary classes. */
if( ! function_exists( 'GWAA_class_auto_loader' ) ) {
   
   function GWAA_class_auto_loader( $class ) {
      
      $includes = GWAA_PLUGIN_DIR . 'includes/' . $class . '.php';
      
      if( is_file( $includes ) && ! class_exists( $class ) ) {
         include_once( $includes );
         return;
      }
      
   }
}
spl_autoload_register('GWAA_class_auto_loader');

/* Initialize all modules now. */
if (!function_exists('is_plugin_active')) {
    include_once(ABSPATH . 'wp-admin/includes/plugin.php');
}

if ( ( is_plugin_active( 'contact-form-7/wp-contact-form-7.php' ) ) ) {
   new ACGWAA_Backend();
   new ACGWAA_Display();
   new ACGWAA_Frontend();
}
add_filter( 'plugin_action_links_' . GWAA_PLUGIN_BASENAME, 'gwaa_plugin_action_links' );
function gwaa_plugin_action_links( $links ) {
   $support_url = 'https://www.codesmade.com/contact-us/';
   $docs_url    = 'https://www.codesmade.com/store/autocomplete-location-field-contact-form-7-pro/';
   $pro_url     = 'https://www.codesmade.com/create-google-map-place-api-key/';
   $support_link = '<a href="' . esc_url( $support_url ) . '" target="_blank" rel="noopener">Support</a>';
   $docs_link    = '<a href="' . esc_url( $docs_url ) . '" target="_blank" rel="noopener">Docs</a>';
   $pro_link     = '<a href="' . esc_url( $pro_url ) . '" target="_blank" rel="noopener">Get Pro Version</a>';
	$settings_url  = admin_url( 'admin.php?page=autocomplete-location-field-contact-form-7' );
	$settings_link = '<a href="' . esc_url( $settings_url ) . '">Settings</a>';
	array_unshift( $links, $support_link );
	array_unshift( $links, $docs_link );
   array_unshift( $links, $pro_link );
	array_unshift( $links, $settings_link );
   return $links;
}
