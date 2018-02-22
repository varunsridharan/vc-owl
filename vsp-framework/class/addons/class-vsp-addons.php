<?php
if( ! defined("ABSPATH") ) {
    exit;
}

if( ! class_exists("VSP_Addons") ) {
    /**
     * Class VSP_Addons
     */
    class VSP_Addons extends VSP_Addons_Admin {

        protected $user_options = array();

        protected $default_options = array(
            'hook_slug'               => '',
            'db_slug'                 => '',
            'plugin_slug'             => '',
            'base_path'               => '',
            'base_url'                => '',
            'addon_listing_tab_name'  => 'addons',
            'addon_listing_tab_title' => "Addons",
            'addon_listing_tab_icon'  => 'fa fa-plus',
            'file_headers'            => array(),
            'show_category_count'     => TRUE,
        );

        /**
         * VSP_Addons constructor.
         * @param array $options
         */
        public function __construct($options = array()) {
            $this->active_addons = array();
            $this->user_options = $options;
            parent::__construct();

            $this->plugin_slug = $this->option('plugin_slug');
            $this->hook_slug = $this->option('hook_slug');
            $this->db_slug = $this->option('plugin_db_slug');

            if( vsp_is_admin() ) {
                //add_action($this->hook_slug.'_settings_section',array($this,'set_settings_section'),99,100);
                add_action($this->hook_slug . '_settings_pages', array( $this, 'set_settings_page' ), 99, 100);
                add_action('vsp_render_' . $this->hook_slug . '_addons_list', array(
                    $this,
                    'render_addons_page',
                ), 10, 2);
            }

            if( vsp_is_ajax() ) {
                add_action($this->option("hook_slug") . "_handle_addon_request", array( $this, 'handle_ajax_request' ));
            }

            $this->load_active_addons();
        }

        public function load_active_addons() {
            $active_addons = $this->get_active_addons();

            $msg = sprintf(__("%s Has deactivated the following addons because its required plugins are deactivated", 'vsp-framework'), '<strong>' . $this->option('plugin_name') . '</strong>');
            $deactivated_plugins = '';

            if( ! empty($active_addons) ) {
                foreach( $active_addons as $pathid => $addon_slug ) {
                    $is_active = $this->is_active($pathid, TRUE);
                    if( $is_active !== FALSE ) {
                        $addon_data = $this->search_get_addon($addon_slug, $pathid);
                        if( empty($addon_data) ) {
                            $deactivated_plugins .= '<li>' . $addon_slug . '</li>';
                            $this->deactivate_addon($addon_slug, $pathid);
                            continue;
                        }

                        if( isset($addon_data['required_plugins']) && is_array($addon_data['required_plugins']) ) {
                            if( $addon_data['required_plugins']['fulfilled'] !== TRUE ) {
                                $deactivated_plugins .= '<li>' . $addon_data['Name'] . '</li>';
                                $this->deactivate_addon($addon_slug, $pathid);
                                continue;
                            }
                        }

                        $full_path = $addon_data['addon_path'] . $addon_data['addon_file'];
                        require_once( $full_path );
                    }
                }
            }

            if( ! empty($deactivated_plugins) ) {
                $msg = $msg . '<ul>' . $deactivated_plugins . '</ul>';
                vsp_notice_error($msg);
            }
        }

        /**
         * @param string $request
         * @param        $msg
         * @return bool
         */
        public function handle_ajax_params($request = '', $msg) {
            if( isset($_REQUEST[$request]) ) {
                return $_REQUEST[$request];
            }

            wp_send_json_error(array( 'msg' => $msg ));
            return FALSE;
        }

        public function handle_ajax_request() {
            if( isset($_REQUEST['addon_action']) ) {
                $action = $this->handle_ajax_params("addon_action", __("Addon Action Not Provided", 'vsp-framework'));
                $addon = urldecode($this->handle_ajax_params('addon_slug', __("No Addon Selected", 'vsp-framework')));
                $pathid = $this->handle_ajax_params("addon_pathid", __("Unable To Process Your Request", 'vsp-framework'));


                if( empty($addon) ) {
                    wp_send_json_error(array( "msg" => __("Invalid Addon", 'vsp-framework') ));
                }

                if( $action == 'activate' ) {
                    if( ! $this->is_active($addon) ) {
                        $addon_data = $this->search_get_addon($addon, $pathid);

                        if( isset($addon_data['required_plugins']) && is_array($addon_data['required_plugins']) ) {
                            if( $addon_data['required_plugins']['fulfilled'] !== TRUE ) {
                                wp_send_json_error(array( 'msg' => __("Addon's Requried Plugins Not Active / Installed", 'vsp-framework') ));
                            }
                        }

                        $slug = $this->activate_addon($addon, $pathid);

                        if( $slug ) {
                            wp_send_json_success(array( 'msg' => __("Addon Activated", 'vsp-framework') ));
                        }

                    } else {
                        wp_send_json_error(array( 'msg' => __("Addon Already Active", 'vsp-framework') ));
                    }
                }

                if( $action == 'deactivate' ) {
                    if( $this->is_active($addon) ) {
                        $slug = $this->deactivate_addon($addon, $pathid);

                        if( $slug ) {
                            wp_send_json_success(array( 'msg' => __("Addon De-Activated", 'vsp-framework') ));
                        }

                    } else {
                        wp_send_json_error(array( 'msg' => __("Addon Is Not Active", 'vsp-framework') ));
                    }
                }
            }
            wp_die();
        }


        /**
         * @return array|mixed|void
         */
        public function get_active_addons() {
            if( empty($this->active_addons) ) {
                $this->active_addons = get_option($this->option("db_slug") . '_active_addons', array());
            }

            $this->active_addons = is_array($this->active_addons) ? $this->active_addons : array();
            return $this->active_addons;
        }

        /**
         * @param $addons
         * @return array
         */
        public function update_active_addons($addons) {
            update_option($this->option("db_slug") . '_active_addons', $addons);
            $this->active_addons = $addons;
            return $this->active_addons;
        }

        /**
         * @param string $addons_slug
         * @param string $pathid
         * @return bool
         */
        public function activate_addon($addons_slug = '', $pathid = '') {
            $active_addons = $this->get_active_addons();
            if( ! isset($active_addons[$pathid]) ) {
                $active_addons[$pathid] = $addons_slug;
                $this->update_active_addons($active_addons);
                return TRUE;
            }

            return FALSE;
        }

        /**
         * @param string $addons_slug
         * @param string $pathid
         * @return bool
         */
        public function deactivate_addon($addons_slug = '', $pathid = '') {
            $active_addons = $this->get_active_addons();
            if( isset($active_addons[$pathid]) ) {
                if( $active_addons[$pathid] == $addons_slug ) {
                    unset($active_addons[$pathid]);
                    $this->update_active_addons($active_addons);
                    return TRUE;
                }
            }

            return FALSE;
        }

        /**
         * @param      $slug
         * @param bool $is_pathid
         * @return bool|mixed
         */
        public function is_active($slug, $is_pathid = FALSE) {
            $addons = $this->get_active_addons();

            if( $is_pathid === TRUE ) {
                if( isset($addons[$slug]) ) {
                    return $addons[$slug];
                }
            } else {
                if( in_array($slug, $addons) ) {
                    return $slug;
                }
            }

            return FALSE;
        }
    }
}
