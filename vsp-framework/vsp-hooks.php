<?php
if( ! defined("ABSPATH") ) {
    exit;
}

add_action("vsp_framework_init", 'vsp_init_admin_notices');
add_action("vsp_framework_loaded", 'vsp_cache_options', 99999);

if( vsp_is_admin() ) {
    add_action("admin_enqueue_scripts", 'vsp_register_assets', 1);
}


if( ! function_exists("vsp_register_assets") ) {
    /**
     * Registers Basic Framework Styles / Scripts to WP
     * @uses admin_enqueue_scripts
     */
    function vsp_register_assets() {

        $js = array(
            'blockui'   => vsp_debug_file("vendors/blockui/jquery.blockui.js", 'assets'),
            'simscroll' => vsp_debug_file('vendors/simscroll/simscroll.js', 'assets'),
            'vspajax'   => vsp_debug_file("vendors/vspajax/jquery.vsp-ajax.js", 'assets'),
            'addons'    => vsp_debug_file('vsp-addons.js', 'js'),
            'plugins'   => vsp_debug_file('vsp-plugins.js', 'js'),
            'framework' => vsp_debug_file('vsp-framework.js', 'js'),
        );


        $css = array(
            'framework' => vsp_debug_file("vsp-framework.css", 'css'),
            'plugins'   => vsp_debug_file("vsp-plugins.css", 'css'),
            'addons'    => vsp_debug_file("vsp-addons.css", 'css'),
        );

        vsp_register_script('vsp-simscroll', $js['simscroll'], array( 'jquery' ), '1.3.8', TRUE);
        vsp_register_script('vsp-blockui', $js['blockui'], array( 'jquery' ), '1.0.16', TRUE);
        vsp_register_script('vsp-ajax', $js['vspajax'], array( 'jquery' ), '1.0', TRUE);
        vsp_register_script('vsp-plugins', $js['plugins'], array( 'jquery' ), '1.0', TRUE);
        vsp_register_script('vsp-addons', $js['addons'], array( 'jquery' ), '1.0', TRUE);
        vsp_register_script('vsp-framework', $js['framework'], array( 'jquery' ), '1.0', TRUE);
        vsp_register_style('vsp-plugins', $css['plugins']);
        vsp_register_style('vsp-framework', $css['framework'], array(), '1.0');
        vsp_register_style('vsp-addons', $css['addons'], array(), '1.0');
    }
}


if( ! function_exists("vsp_init_admin_notices") ) {
    /**
     * Creats A Instance Of Admin Notice Class
     * @use vsp_framework_init
     */
    function vsp_init_admin_notices() {
        if( vsp_is_admin() || vsp_is_ajax() ) {
            vsp_notices();
        }
    }
}