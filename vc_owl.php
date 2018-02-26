<?php
/*
 * Plugin Name: OWL Carousel2 For Visual Composer
 * Plugin URI: http://wordpress.org/plugins/vc-owl
 * Description: Add a carousel to Visual Composer
 * Version: 1.0
 * Author: Varun Sridharan
 * Author URI: http://varunsridharan.in
*/


if( ! function_exists('vc_owl') ) {
    define("VC_OWL_NAME", __("Visual Composer"));
    define("VC_OWL_VERSION", '1.0');
    define('VC_OWL_FILE', plugin_basename(__FILE__));
    define('VC_OWL_PATH', plugin_dir_path(__FILE__)); # Plugin DIR
    define('VC_OWL_URL', plugins_url('', __FILE__) . '/');  # Plugin URL

    require_once( VC_OWL_PATH . 'vsp-framework/vsp-init.php' );

    if( function_exists("vsp_mayby_framework_loader") ) {
        vsp_mayby_framework_loader(VC_OWL_PATH);
    }

    /*
     * Returns Instance of OWL
     */
    require_once( VC_OWL_PATH . 'bootstrap.php' );

    function vc_owl() {
        return Visual_Composer_OWL::instance();
    }

    add_action("vsp_framework_loaded", 'vc_owl');
}