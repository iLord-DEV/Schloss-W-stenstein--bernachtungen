<?php
// Verhindert den direkten Aufruf der Datei
if (!defined('ABSPATH')) exit;

// Register Custom Post Type for Übernachtungen
function wuest_register_consumption_cpt()
{
    $labels = array(
        'name'                  => _x('Übernachtungen', 'Post Type General Name', 'wuest'),
        'singular_name'         => _x('Übernachtung', 'Post Type Singular Name', 'wuest'),
        'menu_name'             => __('Übernachtungen', 'wuest'),
        'name_admin_bar'        => __('Übernachtung', 'wuest'),
        'archives'              => __('Entry Archives', 'wuest'),
        'attributes'            => __('Entry Attributes', 'wuest'),
        'parent_item_colon'     => __('Parent Entry:', 'wuest'),
        'all_items'             => __('All Entries', 'wuest'),
        'add_new_item'          => __('Add New Entry', 'wuest'),
        'add_new'               => __('Add New', 'wuest'),
        'new_item'              => __('New Entry', 'wuest'),
        'edit_item'             => __('Edit Entry', 'wuest'),
        'update_item'           => __('Update Entry', 'wuest'),
        'view_item'             => __('View Entry', 'wuest'),
        'view_items'            => __('View Entries', 'wuest'),
        'search_items'          => __('Search Entry', 'wuest'),
        'not_found'             => __('Not found', 'wuest'),
        'not_found_in_trash'    => __('Not found in Trash', 'wuest'),
        'featured_image'        => __('Featured Image', 'wuest'),
        'set_featured_image'    => __('Set featured image', 'wuest'),
        'remove_featured_image' => __('Remove featured image', 'wuest'),
        'use_featured_image'    => __('Use as featured image', 'wuest'),
        'insert_into_item'      => __('Insert into entry', 'wuest'),
        'uploaded_to_this_item' => __('Uploaded to this entry', 'wuest'),
        'items_list'            => __('Entries list', 'wuest'),
        'items_list_navigation' => __('Entries list navigation', 'wuest'),
        'filter_items_list'     => __('Filter entries list', 'wuest'),
    );
    $args = array(
        'label'                 => __('Übernachtung', 'wuest'),
        'description'           => __('A record of oil consumption for a specific period', 'wuest'),
        'labels'                => $labels,
        'supports'              => array('title', 'author', 'custom-fields'),
        'hierarchical'          => false,
        'public'                => false,
        'show_ui'               => true,
        'show_in_menu'          => true,
        'menu_position'         => 5,
        'show_in_admin_bar'     => true,
        'show_in_nav_menus'     => false,
        'can_export'            => true,
        'has_archive'           => false,
        'exclude_from_search'   => true,
        'publicly_queryable'    => false,
        'capability_type'       => array('uebernachtung', 'consumption_entries'),
        'map_meta_cap'          => true,
    );
    register_post_type('uebernachtung', $args);
}
add_action('init', 'wuest_register_consumption_cpt', 0);

// Add role capabilities
function wuest_add_role_caps()
{
    $roles = array('administrator', 'editor', 'author', 'contributor', 'subscriber');

    foreach ($roles as $role) {
        $role_obj = get_role($role);
        if (!$role_obj) continue;

        $role_obj->add_cap('read_uebernachtung');
        $role_obj->add_cap('edit_uebernachtung');
        $role_obj->add_cap('edit_consumption_entries');
        $role_obj->add_cap('delete_uebernachtung');
        $role_obj->add_cap('delete_consumption_entries');
        $role_obj->add_cap('publish_consumption_entries');
        $role_obj->add_cap('create_consumption_entries');
    }
}
register_activation_hook(__FILE__, 'wuest_add_role_caps');

// Add Custom Fields to the CPT using ACF
function wuest_add_consumption_fields()
{
    if (function_exists('acf_add_local_field_group')):
        acf_add_local_field_group(array(
            'key' => 'group_613f3a4b8f3e4',
            'title' => 'Übernachtungen',
            'fields' => array(
                array(
                    'key' => 'field_613f3a54c2bb5',
                    'label' => 'Burner Operating Hours',
                    'name' => 'uebernachtungen',
                    'type' => 'number',
                    'required' => 1,
                    'min' => 0,
                ),
                array(
                    'key' => 'field_613f3a65c2bb6',
                    'label' => 'Arrival Date',
                    'name' => 'arrival_date',
                    'type' => 'date_picker',
                    'required' => 1,
                    'display_format' => 'd/m/Y',
                    'return_format' => 'd/m/Y',
                    'first_day' => 1,
                ),
                array(
                    'key' => 'field_613f3a77c2bb7',
                    'label' => 'Departure Date',
                    'name' => 'departure_date',
                    'type' => 'date_picker',
                    'required' => 1,
                    'display_format' => 'd/m/Y',
                    'return_format' => 'd/m/Y',
                    'first_day' => 1,
                ),
                array(
                    'key' => 'field_family_overnight_stays',
                    'label' => 'Number of Family Overnight Stays',
                    'name' => 'family_overnight_stays',
                    'type' => 'number',
                    'required' => 1,
                    'min' => 0,
                ),
                array(
                    'key' => 'field_guest_overnight_stays',
                    'label' => 'Number of Guest Overnight Stays',
                    'name' => 'guest_overnight_stays',
                    'type' => 'number',
                    'required' => 1,
                    'min' => 0,
                ),
            ),
            'location' => array(
                array(
                    array(
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'uebernachtung',
                    ),
                ),
            ),
            'menu_order' => 0,
            'position' => 'normal',
            'style' => 'default',
            'label_placement' => 'top',
            'instruction_placement' => 'label',
            'hide_on_screen' => '',
            'active' => true,
            'description' => '',
        ));

        // Add Admin Settings Fields
        acf_add_local_field_group(array(
            'key' => 'group_wuest_admin_settings',
            'title' => 'Burner Hours Admin Settings',
            'fields' => array(
                array(
                    'key' => 'field_wuest_consumption_rate',
                    'label' => 'Consumption Rate (Liters per Burner Hour)',
                    'name' => 'wuest_consumption_rate',
                    'type' => 'number',
                    'required' => 1,
                    'default_value' => 1,
                    'min' => 0,
                    'step' => 0.01,
                ),
                array(
                    'key' => 'field_wuest_show_all_entries',
                    'label' => 'Show All Entries',
                    'name' => 'wuest_show_all_entries',
                    'type' => 'true_false',
                    'instructions' => 'Show all consumption entries in the admin area',
                    'ui' => 1,
                ),
                array(
                    'key' => 'field_wuest_family_overnight_price',
                    'label' => 'Price per Family Overnight Stay (€)',
                    'name' => 'wuest_family_overnight_price',
                    'type' => 'number',
                    'required' => 1,
                    'default_value' => 0,
                    'min' => 0,
                    'step' => 0.01,
                ),
                array(
                    'key' => 'field_wuest_guest_overnight_price',
                    'label' => 'Price per Guest Overnight Stay (€)',
                    'name' => 'wuest_guest_overnight_price',
                    'type' => 'number',
                    'required' => 1,
                    'default_value' => 0,
                    'min' => 0,
                    'step' => 0.01,
                ),
            ),
            'location' => array(
                array(
                    array(
                        'param' => 'options_page',
                        'operator' => '==',
                        'value' => 'wuest_burner_hours_settings',
                    ),
                ),
            ),
            'menu_order' => 0,
            'position' => 'normal',
            'style' => 'default',
            'label_placement' => 'top',
            'instruction_placement' => 'label',
            'hide_on_screen' => '',
            'active' => true,
            'description' => '',
        ));
    endif;
}
add_action('acf/init', 'wuest_add_consumption_fields');

// Filter posts in admin to show only user's own posts (with option for admins)
function wuest_filter_consumption_entries_for_current_user($query)
{
    global $pagenow, $post_type;

    if (is_admin() && $pagenow == 'edit.php' && $post_type == 'uebernachtung') {
        $show_all_entries = get_option('wuest_show_all_entries', false);

        if (!current_user_can('administrator') || !$show_all_entries) {
            // Nur eigene Einträge anzeigen
            $query->set('author', get_current_user_id());
        }
    }
}
add_action('pre_get_posts', 'wuest_filter_consumption_entries_for_current_user');

// Set post author to current user when creating a new consumption entry
function wuest_set_uebernachtung_author($data)
{
    if ($data['post_type'] == 'uebernachtung') {
        $data['post_author'] = get_current_user_id();
    }
    return $data;
}
add_filter('wp_insert_post_data', 'wuest_set_uebernachtung_author');
