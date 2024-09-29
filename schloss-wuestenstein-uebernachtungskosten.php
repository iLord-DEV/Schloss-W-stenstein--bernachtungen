<?php
/*
Plugin Name: Schloss Wüstenstein - Übernachtungskosten
PLUGIN URI: https://christoph-heim.de
Description: A plugin to manage oil consumption based on burner operating hours. Allows users to enter multiple consumption records per year and view them in a table.
Version: 1.0
Author: Christoph Heim
Author URI: https://christoph-heim.de
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Domain Path: /languages/
Text Domain: wuest
*/

// Verhindert den direkten Aufruf der Datei
if (!defined('ABSPATH')) exit;

// Inkludiere die Hilfsfunktionen
require_once plugin_dir_path(__FILE__) . 'includes/helpers.php';

// textdomain für Übersetzungen
function wuest_load_textdomain_manual()
{
    $result = load_plugin_textdomain('wuest', false, dirname(plugin_basename(__FILE__)) . '/languages/');
}
add_action('plugins_loaded', 'wuest_load_textdomain_manual');

//  Enqueue the script that validates the date fields
function wuest_enqueue_scripts()
{
    $screen = get_current_screen();
    if ($screen && $screen->post_type === 'uebernachtung') {
        wp_enqueue_script(
            'wuest-burner-costs',
            plugin_dir_url(__FILE__) . 'js/uebernachtungskosten.js',
            array('jquery', 'acf-input'), // Abhängigkeiten hinzufügen
            '1.0.0',
            true
        );
    }
}
add_action('admin_enqueue_scripts', 'wuest_enqueue_scripts');



// Register Custom Post Type for Consumption Entries
// if (function_exists('acf_add_options_page')) {
//     acf_add_options_page(array(
//         'page_title'    => 'Wüstenstein Settings',
//         'menu_title'    => 'Wüstenstein Settings',
//         'menu_slug'     => 'wuest_burner_hours_settings',
//         'capability'    => 'manage_options',
//         'redirect'      => false
//     ));
// }

add_action('admin_post_wuest_export_csv', 'wuest_export_csv');

// function wuest_validate_consumption_entry($post_id)
// {
//     // Prüfen, ob es sich um einen Auto-Save handelt
//     if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

//     // Prüfen, ob es sich um den richtigen Post-Typ handelt
//     if (get_post_type($post_id) !== 'consumption_entry') return;

//     // Arrival und Departure Dates abrufen
//     $arrival_date = get_field('arrival_date', $post_id);
//     $departure_date = get_field('departure_date', $post_id);

//     // Datumsformat konvertieren (angenommen, das Format ist 'd/m/Y')
//     $arrival = DateTime::createFromFormat('d/m/Y', $arrival_date);
//     $departure = DateTime::createFromFormat('d/m/Y', $departure_date);

//     // Prüfen, ob das Abreisedatum nach dem Ankunftsdatum liegt
//     if ($departure < $arrival) {
//         // Fehlermeldung hinzufügen
//         acf_add_admin_notice('Departure date cannot be earlier than arrival date.', 'error');

//         // Optional: Verhindern des Speicherns
//         // remove_action('save_post', 'wuest_validate_consumption_entry');
//         // wp_update_post(array('ID' => $post_id, 'post_status' => 'draft'));
//         // add_action('save_post', 'wuest_validate_consumption_entry');
//     }
// }
// add_action('acf/save_post', 'wuest_validate_consumption_entry', 20);

// Inkludiere die verschiedenen Moduldateien
require_once plugin_dir_path(__FILE__) . 'includes/custom-post-type.php';
require_once plugin_dir_path(__FILE__) . 'includes/admin-settings.php';
// require_once plugin_dir_path(__FILE__) . 'includes/widgets.php';
require_once plugin_dir_path(__FILE__) . 'includes/dashboard-widget.php';
