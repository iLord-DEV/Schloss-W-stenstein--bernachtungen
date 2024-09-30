<?php
// Prevent direct access to this file
if (!defined('ABSPATH')) exit;

// Register Custom Post Type for Übernachtungen
function wuest_register_consumption_cpt()
{
    $labels = array(
        'name'                  => _x('Übernachtungen', 'Post Type General Name', 'wuest'),
        'singular_name'         => _x('Übernachtung', 'Post Type Singular Name', 'wuest'),
        'menu_name'             => __('Übernachtungen', 'wuest'),
        'name_admin_bar'        => __('Übernachtung', 'wuest'),
        'archives'              => __('Übernachtung Archives', 'wuest'),
        'attributes'            => __('Übernachtung Attributes', 'wuest'),
        'parent_item_colon'     => __('Parent Übernachtung:', 'wuest'),
        'all_items'             => __('All Übernachtungen', 'wuest'),
        'add_new_item'          => __('Add New Übernachtung', 'wuest'),
        'add_new'               => __('Add New', 'wuest'),
        'new_item'              => __('New Übernachtung', 'wuest'),
        'edit_item'             => __('Edit Übernachtung', 'wuest'),
        'update_item'           => __('Update Übernachtung', 'wuest'),
        'view_item'             => __('View Übernachtung', 'wuest'),
        'view_items'            => __('View Übernachtungen', 'wuest'),
        'search_items'          => __('Search Übernachtung', 'wuest'),
        'not_found'             => __('Not found', 'wuest'),
        'not_found_in_trash'    => __('Not found in Trash', 'wuest'),
        'featured_image'        => __('Featured Image', 'wuest'),
        'set_featured_image'    => __('Set featured image', 'wuest'),
        'remove_featured_image' => __('Remove featured image', 'wuest'),
        'use_featured_image'    => __('Use as featured image', 'wuest'),
        'insert_into_item'      => __('Insert into Übernachtung', 'wuest'),
        'uploaded_to_this_item' => __('Uploaded to this Übernachtung', 'wuest'),
        'items_list'            => __('Übernachtungen list', 'wuest'),
        'items_list_navigation' => __('Übernachtungen list navigation', 'wuest'),
        'filter_items_list'     => __('Filter Übernachtungen list', 'wuest'),
    );
    $args = array(
        'label'                 => __('Übernachtung', 'wuest'),
        'description'           => __('Overnight stays and costs for Schloss Wüstenstein', 'wuest'),
        'labels'                => $labels,
        'supports'              => array('title', 'author', 'custom-fields'),
        'hierarchical'          => false,
        'public'                => false,
        'show_ui'               => true,
        'show_in_menu'          => true,
        'menu_position'         => 5,
        'menu_icon'             => 'dashicons-calendar',
        'show_in_admin_bar'     => true,
        'show_in_nav_menus'     => false,
        'can_export'            => true,
        'has_archive'           => false,
        'exclude_from_search'   => true,
        'publicly_queryable'    => false,
        'capability_type'       => 'post',
        'capabilities'          => array(
            'edit_post'         => 'edit_post',
            'read_post'         => 'read_post',
            'delete_post'       => 'delete_post',
            'edit_posts'        => 'edit_posts',
            'edit_others_posts' => 'edit_others_posts',
            'publish_posts'     => 'publish_posts',
            'read_private_posts' => 'read_private_posts',
        ),
        'map_meta_cap'          => true,
    );
    register_post_type('uebernachtung', $args);
}

// Add role capabilities
function wuest_add_role_caps()
{
    $roles = array('administrator', 'editor', 'author');

    foreach ($roles as $role) {
        $role_obj = get_role($role);
        if (!$role_obj) continue;

        $role_obj->add_cap('edit_uebernachtung');
        $role_obj->add_cap('read_uebernachtung');
        $role_obj->add_cap('delete_uebernachtung');
        $role_obj->add_cap('edit_uebernachtungen');
        $role_obj->add_cap('edit_others_uebernachtungen');
        $role_obj->add_cap('publish_uebernachtungen');
        $role_obj->add_cap('read_private_uebernachtungen');
    }
}

// Add Custom Fields to the CPT using ACF
function wuest_add_custom_fields()
{
    if (function_exists('acf_add_local_field_group')):
        acf_add_local_field_group(array(
            'key' => 'group_uebernachtung',
            'title' => 'Übernachtung Details',
            'fields' => array(
                array(
                    'key' => 'field_burner_hours',
                    'label' => 'Burner Operating Hours',
                    'name' => 'burner_hours',
                    'type' => 'number',
                    'required' => 1,
                    'min' => 0,
                ),
                array(
                    'key' => 'field_arrival_date',
                    'label' => 'Arrival Date',
                    'name' => 'arrival_date',
                    'type' => 'date_picker',
                    'required' => 1,
                    'display_format' => 'd/m/Y',
                    'return_format' => 'd/m/Y',
                    'first_day' => 1,
                ),
                array(
                    'key' => 'field_departure_date',
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
    endif;
}
add_action('acf/init', 'wuest_add_custom_fields');

// Filter posts in admin to show only user's own posts (with option for admins)
function wuest_filter_uebernachtungen_for_current_user($query)
{
    global $pagenow, $post_type;

    if (is_admin() && $pagenow == 'edit.php' && $post_type == 'uebernachtung') {
        $show_all_entries = get_option('wuest_show_all_entries', false);

        if (!current_user_can('administrator') || !$show_all_entries) {
            $query->set('author', get_current_user_id());
        }
    }
}
add_action('pre_get_posts', 'wuest_filter_uebernachtungen_for_current_user');

// Set post author to current user when creating a new Übernachtung entry
function wuest_set_uebernachtung_author($data)
{
    if ($data['post_type'] == 'uebernachtung') {
        $data['post_author'] = get_current_user_id();
    }
    return $data;
}
add_filter('wp_insert_post_data', 'wuest_set_uebernachtung_author');
