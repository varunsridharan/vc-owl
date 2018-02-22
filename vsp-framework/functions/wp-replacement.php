<?php
if( ! defined("ABSPATH") ) {
    exit;
}

if( ! function_exists("vsp_slashit") ) {
    /**
     * @param $path
     * @return string
     */
    function vsp_slashit($path) {
        return trailingslashit($path);
    }
}

if( ! function_exists("vsp_unslashit") ) {
    /**
     * @param $path
     * @return string
     */
    function vsp_unslashit($path) {
        return untrailingslashit($path);
    }
}

if( ! function_exists("vsp_register_script") ) {
    /**
     * @param string $handle
     * @param string $src
     * @param array  $deps
     * @param string $ver
     * @param bool   $footer
     * @return bool
     */
    function vsp_register_script($handle = '', $src = '', $deps = array(), $ver = '1.0', $footer = TRUE) {
        $src = vsp_debug_file($src);
        return wp_register_script($handle, $src, $deps, $ver, $footer);
    }
}

if( ! function_exists("vsp_register_style") ) {
    /**
     * @param string $handle
     * @param string $src
     * @param array  $deps
     * @param string $ver
     * @param string $media
     * @return bool
     */
    function vsp_register_style($handle = '', $src = '', $deps = array(), $ver = '1.0', $media = 'all') {
        $src = vsp_debug_file($src);
        return wp_register_style($handle, $src, $deps, $ver, $media);
    }
}

if( ! function_exists("vsp_load_script") ) {
    /**
     * @param string $handle
     * @param string $src
     * @param array  $deps
     * @param string $ver
     * @param bool   $in_footer
     */
    function vsp_load_script($handle = '', $src = '', $deps = array(), $ver = '', $in_footer = FALSE) {
        $src = vsp_debug_file($src);
        return wp_enqueue_script($handle, $src, $deps, $ver, $in_footer);
    }
}

if( ! function_exists("vsp_load_style") ) {
    /**
     * @param string $handle
     * @param string $src
     * @param array  $deps
     * @param string $ver
     * @param bool   $in_footer
     */
    function vsp_load_style($handle = '', $src = '', $deps = array(), $ver = '', $in_footer = FALSE) {
        $src = vsp_debug_file($src);
        return wp_enqueue_style($handle, $src, $deps, $ver, $in_footer);
    }
}

if( ! function_exists("vsp_addon_data_markup") ) {
    /**
     * @param      $plugin_file
     * @param      $plugin_data
     * @param bool $markup
     * @param bool $translate
     * @return mixed
     */
    function vsp_addon_data_markup($plugin_file, $plugin_data, $markup = TRUE, $translate = TRUE) {
        if( function_exists('_get_plugin_data_markup_translate') ) {
            return _get_plugin_data_markup_translate($plugin_file, $plugin_data, $markup, $translate);
        }

        // Sanitize the plugin filename to a WP_PLUGIN_DIR relative path
        $plugin_file = plugin_basename($plugin_file);

        // Translate fields
        if( $translate ) {
            if( $textdomain = $plugin_data['TextDomain'] ) {
                if( ! is_textdomain_loaded($textdomain) ) {
                    if( $plugin_data['DomainPath'] ) {
                        load_plugin_textdomain($textdomain, FALSE, dirname($plugin_file) . $plugin_data['DomainPath']);
                    } else {
                        load_plugin_textdomain($textdomain, FALSE, dirname($plugin_file));
                    }
                }
            } else if( 'hello.php' == basename($plugin_file) ) {
                $textdomain = 'default';
            }
            if( $textdomain ) {
                foreach( array( 'Name', 'PluginURI', 'Description', 'Author', 'AuthorURI', 'Version' ) as $field )
                    $plugin_data[$field] = translate($plugin_data[$field], $textdomain);
            }
        }

        // Sanitize fields
        $allowed_tags = $allowed_tags_in_links = array(
            'abbr'    => array( 'title' => TRUE ),
            'acronym' => array( 'title' => TRUE ),
            'code'    => TRUE,
            'em'      => TRUE,
            'strong'  => TRUE,
        );
        $allowed_tags['a'] = array( 'href' => TRUE, 'title' => TRUE );

        // Name is marked up inside <a> tags. Don't allow these.
        // Author is too, but some plugins have used <a> here (omitting Author URI).
        $plugin_data['Name'] = wp_kses($plugin_data['Name'], $allowed_tags_in_links);
        $plugin_data['Author'] = wp_kses($plugin_data['Author'], $allowed_tags);

        $plugin_data['Description'] = wp_kses($plugin_data['Description'], $allowed_tags);
        $plugin_data['Version'] = wp_kses($plugin_data['Version'], $allowed_tags);

        $plugin_data['PluginURI'] = esc_url($plugin_data['PluginURI']);
        $plugin_data['AuthorURI'] = esc_url($plugin_data['AuthorURI']);

        $plugin_data['Title'] = $plugin_data['Name'];
        $plugin_data['AuthorName'] = $plugin_data['Author'];

        // Apply markup
        if( $markup ) {
            if( $plugin_data['PluginURI'] && $plugin_data['Name'] )
                $plugin_data['Title'] = '<a href="' . $plugin_data['PluginURI'] . '">' . $plugin_data['Name'] . '</a>';

            if( $plugin_data['AuthorURI'] && $plugin_data['Author'] )
                $plugin_data['Author'] = '<a href="' . $plugin_data['AuthorURI'] . '">' . $plugin_data['Author'] . '</a>';

            $plugin_data['Description'] = wptexturize($plugin_data['Description']);

            if( $plugin_data['Author'] )
                $plugin_data['Description'] .= ' <cite>' . sprintf(__('By %s.', 'vsp-framework'), $plugin_data['Author']) . '</cite>';
        }

        return $plugin_data;
    }
}

if( ! function_exists("vsp_get_shortcode_regex") ) {

    /**
     * @param null $tagnames
     * @param bool $is_addon
     * @return string
     */
    function vsp_get_shortcode_regex($tagnames = NULL, $is_addon = FALSE) {
        global $shortcode_tags;

        if( empty($tagnames) ) {
            $tagnames = array_keys($shortcode_tags);
        }

        if( $is_addon === FALSE ) {
            $tagnames = array_map('preg_quote', $tagnames);
        }

        $tagregexp = join('|', $tagnames);

        return '\\['                              // Opening bracket
            . '(\\[?)'                           // 1: Optional second opening bracket for escaping shortcodes: [[tag]]
            . "($tagregexp)"                     // 2: Shortcode name
            . '(?![\\w-])'                       // Not followed by word character or hyphen
            . '('                                // 3: Unroll the loop: Inside the opening shortcode tag
            . '[^\\]\\/]*'                   // Not a closing bracket or forward slash
            . '(?:' . '\\/(?!\\])'               // A forward slash not followed by a closing bracket
            . '[^\\]\\/]*'               // Not a closing bracket or forward slash
            . ')*?' . ')' . '(?:' . '(\\/)'                        // 4: Self closing tag ...
            . '\\]'                          // ... and closing bracket
            . '|' . '\\]'                          // Closing bracket
            . '(?:' . '('                        // 5: Unroll the loop: Optionally, anything between the opening and closing shortcode tags
            . '[^\\[]*+'             // Not an opening bracket
            . '(?:' . '\\[(?!\\/\\2\\])' // An opening bracket not followed by the closing shortcode tag
            . '[^\\[]*+'         // Not an opening bracket
            . ')*+' . ')' . '\\[\\/\\2\\]'             // Closing shortcode tag
            . ')?' . ')' . '(\\]?)';                          // 6: Optional second closing brocket for escaping shortcodes: [[tag]]
    }
}

if( ! function_exists("vsp_set_cache") ) {
    /**
     * @param     $cache_name
     * @param     $data
     * @param int $expiry
     * @return bool
     */
    function vsp_set_cache($cache_name, $data, $expiry = 0) {
        $expiry = vsp_get_time_in_seconds($expiry);
        return set_transient($cache_name, $data, $expiry);
    }
}

if( ! function_exists("vsp_get_cache") ) {
    /**
     * @param $cache_name
     * @return mixed
     */
    function vsp_get_cache($cache_name) {
        return get_transient($cache_name);
    }
}

if( ! function_exists("vsp_delete_cache") ) {
    /**
     * @param $cache_name
     * @return bool
     */
    function vsp_delete_cache($cache_name) {
        return delete_transient($cache_name);
    }
}

if( ! function_exists("vsp_fix_title") ) {
    /**
     * @param $title
     * @return string
     */
    function vsp_fix_title($title) {
        return sanitize_title($title);
    }
}

if( ! function_exists("vsp_update_term_meta") ) {
    /**
     * @param        $term_id
     * @param        $meta_key
     * @param        $meta_value
     * @param string $prev_value
     * @return bool|int|\WP_Error
     */
    function vsp_update_term_meta($term_id, $meta_key, $meta_value, $prev_value = '') {
        return function_exists('update_term_meta') ? update_term_meta($term_id, $meta_key, $meta_value, $prev_value) : update_option('vsp_tm_' . $term_id . '_' . $meta_key, $meta_value);
    }
}

if( ! function_exists("vsp_add_term_meta") ) {
    /**
     * @param      $term_id
     * @param      $meta_key
     * @param      $meta_value
     * @param bool $unique
     * @return bool|int|\WP_Error
     */
    function vsp_add_term_meta($term_id, $meta_key, $meta_value, $unique = FALSE) {
        return function_exists('add_term_meta') ? add_term_meta($term_id, $meta_key, $meta_value, $unique) : add_option('vsp_tm_' . $term_id . '_' . $meta_key, $meta_value);
    }
}

if( ! function_exists("vsp_delete_term_meta") ) {
    /**
     * @param        $term_id
     * @param        $meta_key
     * @param string $meta_value
     * @param bool   $deprecated
     * @return bool
     */
    function vsp_delete_term_meta($term_id, $meta_key, $meta_value = '', $deprecated = FALSE) {
        return function_exists('delete_term_meta') ? delete_term_meta($term_id, $meta_key, $meta_value) : delete_option('vsp_tm_' . $term_id . '_' . $meta_key);
    }
}

if( ! function_exists("vsp_get_term_meta") ) {
    /**
     * @param      $term_id
     * @param      $key
     * @param bool $single
     * @return mixed|void
     */
    function vsp_get_term_meta($term_id, $key, $single = TRUE) {
        return function_exists('get_term_meta') ? get_term_meta($term_id, $key, $single) : get_option('vsp_tm_' . $term_id . '_' . $key);
    }
}