<?php
/**
 * Created by PhpStorm.
 * User: varun
 * Date: 21-02-2018
 * Time: 03:03 PM
 */
if( ! defined("ABSPATH") ) {
    die;
}

if( ! function_exists('vc_owl_remap_settings') ) {
    function vc_owl_remap_settings($atts) {
        $orginal = WPBakeryShortCode_vc_owlcarousel::set();
        $return  = array();
        foreach( $orginal as $key => $value ) {
            $k = strtolower($key);
            if( isset($atts[$k]) ) {
                $return[$key] = $atts[$k];
            }
        }
        return $return;
    }
}

if( ! class_exists('Visual_Composer_OWL') ) {
    class Visual_Composer_OWL {
        private static $_instance = NULL;

        public static function instance() {
            if( self::$_instance === NULL ) {
                self::$_instance = new self;
            }
            return self::$_instance;
        }

        public function __construct() {
            add_action('init', array( &$this, 'wp_init' ),1);
        }



        public function wp_init() {
            vsp_load_file(VC_OWL_PATH . 'includes/class-*.php');
            $mapper = Visual_Composer_Owl_Mapper::instance();

            vc_map($mapper->shortcode_args());



            vc_map(array(
                "name"                    => __("VC Owl Carousel Item"),
                "base"                    => "vc_owlcarousel_item",
                'is_container'            => TRUE,
                'content_element'         => TRUE,
                'as_child'                => array( 'only' => 'vc_owlcarousel' ),
                "show_settings_on_create" => FALSE,
                "params"                  => array(
                    array(
                        'type'       => 'css_editor',
                        'heading'    => __('CSS box'),
                        'param_name' => 'css',
                        'group'      => __('Design Options'),
                    ),
                ),

            ));


            wp_register_script('vc_owlcarousel_js', VC_OWL_URL . 'assets/owl.carousel.js', array( 'jquery' ), FALSE, TRUE);
            wp_enqueue_script('vc_owlcarousel_init', VC_OWL_URL . 'assets/owl.carousel.init.js', array( 'vc_owlcarousel_js' ), FALSE, TRUE);
            wp_enqueue_style('vc_owlcarousel_css', VC_OWL_URL . 'assets/owl.carousel.min.css');
            wp_enqueue_style('vc_owlcarousel_css_theme', VC_OWL_URL . 'assets/owl.theme.default.min.css', array( 'vc_owlcarousel_css' ));

        }
    }
}