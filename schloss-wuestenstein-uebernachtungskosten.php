<?php
/*
Plugin Name: Schloss Wüstenstein - Übernachtungskosten
PLUGIN URI: https://christoph-heim.de
Description: A plugin to manage overnight stays and costs for Schloss Wüstenstein.
Version: 1.1
Author: Christoph Heim
Author URI: https://christoph-heim.de
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Domain Path: /languages/
Text Domain: wuest
*/

// Prevent direct access to this file
if (!defined('ABSPATH')) exit;

// Include helper functions
require_once plugin_dir_path(__FILE__) . 'includes/helpers.php';

// Load text domain for translations
function wuest_load_textdomain()
{
    load_plugin_textdomain('wuest', false, dirname(plugin_basename(__FILE__)) . '/languages/');
}
add_action('plugins_loaded', 'wuest_load_textdomain');

// Enqueue scripts
function wuest_enqueue_scripts()
{
    $screen = get_current_screen();
    if ($screen && $screen->post_type === 'uebernachtung') {
        wp_enqueue_script(
            'wuest-burner-costs',
            plugin_dir_url(__FILE__) . 'js/uebernachtungskosten.js',
            array('jquery', 'acf-input'),
            '1.0.1',
            true
        );
    }
}
add_action('admin_enqueue_scripts', 'wuest_enqueue_scripts');

// Check plugin dependencies
function wuest_plugin_dependencies()
{
    if (!class_exists('ACF')) {
        add_action('admin_notices', function () {
            echo '<div class="error"><p>' . __('Schloss Wüstenstein - Übernachtungskosten requires Advanced Custom Fields to be installed and activated.', 'wuest') . '</p></div>';
        });
        return false;
    }
    return true;
}

// Initialize plugin
function wuest_init()
{
    if (wuest_plugin_dependencies()) {
        wuest_register_consumption_cpt();
        wuest_add_role_caps();
    }
}
add_action('init', 'wuest_init');

// Activation hook
function wuest_activate()
{
    wuest_register_consumption_cpt();
    wuest_add_role_caps();
    flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'wuest_activate');

// Include module files
require_once plugin_dir_path(__FILE__) . 'includes/custom-post-type.php';
require_once plugin_dir_path(__FILE__) . 'includes/admin-settings.php';
require_once plugin_dir_path(__FILE__) . 'includes/dashboard-widget.php';

// CSV Export action
add_action('admin_post_wuest_export_csv', 'wuest_export_csv');
