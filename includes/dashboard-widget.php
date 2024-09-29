<?php
// Sicherstellen, dass kein direkter Zugriff auf diese Datei möglich ist
if (!defined('ABSPATH')) exit;

function wuest_add_dashboard_widgets()
{
    wp_add_dashboard_widget(
        'wuest_dashboard_widget',
        __('Oil Consumption Summary', 'wuest'),
        'wuest_display_dashboard_widget'
    );

    // Verschieben des Widgets an die erste Position und auf volle Breite setzen
    global $wp_meta_boxes;
    $normal_dashboard = $wp_meta_boxes['dashboard']['normal']['core'];
    $widget_backup = array('wuest_dashboard_widget' => $normal_dashboard['wuest_dashboard_widget']);
    unset($normal_dashboard['wuest_dashboard_widget']);
    $sorted_dashboard = array_merge($widget_backup, $normal_dashboard);
    $wp_meta_boxes['dashboard']['normal']['core'] = $sorted_dashboard;
}
add_action('wp_dashboard_setup', 'wuest_add_dashboard_widgets');

function wuest_add_dashboard_widget_styles()
{
    echo '
    <style>
        #wuest_dashboard_widget {
            width: 100% !important;
            margin-right: 0 !important;
        }
        .postbox-container:first-child {
            width: 100% !important;
        }
        #wuest_dashboard_widget .inside {
            padding: 0;
            margin: 0;
        }
        .wuest-table-container {
            width: 100%;
            overflow-x: auto;
        }
        .wuest-table {
            width: min-content;
            min-width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }
        .wuest-table th,
        .wuest-table td {
            padding: 8px;
            border: 1px solid #e2e8f0;
            white-space: nowrap;
        }
        .wuest-table th {
            background-color: #f8fafc;
            font-weight: bold;
        }
        .wuest-table td.number {
            text-align: right;
        }
        .wuest-table tfoot td {
            background-color: #f8fafc;
            font-weight: bold;
        }
        @media screen and (max-width: 782px) {
            .wuest-table, .wuest-table thead, .wuest-table tbody, .wuest-table tfoot, .wuest-table th, .wuest-table td, .wuest-table tr {
                display: block;
            }
            .wuest-table thead tr {
                position: absolute;
                top: -9999px;
                left: -9999px;
            }
            .wuest-table tr {
                border: 1px solid #ccc;
                margin-bottom: 10px;
            }
            .wuest-table td {
                border: none;
                position: relative;
                padding-left: 50%;
                text-align: left;
                white-space: normal;
            }
            .wuest-table td:before {
                content: attr(data-label);
                position: absolute;
                left: 6px;
                width: 45%;
                padding-right: 10px;
                white-space: nowrap;
                font-weight: bold;
            }
            .wuest-table tfoot td:before {
                content: none;
            }
            .wuest-table tfoot td {
                text-align: right;
                padding: 8px;
            }
        }
    </style>
    ';
}
add_action('admin_head', 'wuest_add_dashboard_widget_styles');

function wuest_display_dashboard_widget()
{
    $current_user = wp_get_current_user();

    // Tailwind CSS einbinden
    wp_enqueue_style('tailwind', 'https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css');

    echo '<div class="p-4">';
    echo '<h3 class="text-xl font-bold mb-2">' . __('Hello', 'wuest') . ' ' . esc_html($current_user->display_name) . '!</h3>';
    echo '<p class="mb-4">' . __('Here you can see an overview of your oil consumption entries.', 'wuest') . '</p>';

    $show_all_users = isset($_GET['wuest_show_all_users']) && sanitize_text_field($_GET['wuest_show_all_users']) === 'on';
    $years_with_entries = wuest_get_years_with_posts($show_all_users ? null : $current_user->ID);

    if (empty($years_with_entries)) {
        echo '<p class="text-lg font-semibold">' . __('No entries found.', 'wuest') . '</p>';
    } else {
        $selected_year = isset($_GET['wuest_year']) ? intval($_GET['wuest_year']) : max($years_with_entries);

        echo '<form method="GET" class="mb-6">';
        echo '<select name="wuest_year" onchange="this.form.submit();" class="mr-4 p-2 border rounded">';
        foreach ($years_with_entries as $year) {
            $selected = selected($year, $selected_year, false);
            echo "<option value=\"" . esc_attr($year) . "\" $selected>$year</option>";
        }
        echo '</select>';

        if (current_user_can('administrator')) {
            echo '<label class="inline-flex items-center mr-4">';
            echo '<input type="checkbox" name="wuest_show_all_users" onchange="this.form.submit();" ' . checked($show_all_users, true, false) . ' class="form-checkbox h-5 w-5 text-blue-600">';
            echo '<span class="ml-2">' . __('Show All Users Data', 'wuest') . '</span>';
            echo '</label>';

            echo '<a href="' . admin_url('admin-post.php?action=wuest_export_csv&year=' . $selected_year) . '" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">' . __('Export CSV', 'wuest') . '</a>';
        }
        echo '</form>';

        if ($show_all_users && current_user_can('administrator')) {
            echo '<h4 class="text-lg font-semibold mb-4">' . __('All Users Data', 'wuest') . '</h4>';
            wuest_display_all_users_data($selected_year);
        } else {
            echo '<h4 class="text-lg font-semibold mb-4">' . __('My Data', 'wuest') . '</h4>';
            wuest_display_user_data($current_user->ID, $selected_year);
        }
    }

    echo '</div>';
}

function wuest_get_price_for_date($date, $price_history)
{
    $price = end($price_history); // Neuester Preis als Standardwert
    foreach ($price_history as $change_date => $change_price) {
        if ($date >= $change_date) {
            $price = $change_price;
            break;
        }
    }
    return (float)$price;
}

function wuest_display_user_data($user_id, $year)
{
    global $wpdb;

    $cache_key = "wuest_user_data_{$user_id}_{$year}";
    $user_data = wp_cache_get($cache_key);

    if ($user_data === false) {
        $query_args = array(
            'post_type' => 'consumption_entry',
            'author' => $user_id,
            'posts_per_page' => -1,
            'orderby' => 'date',
            'order' => 'DESC',
            'meta_query' => array(
                array(
                    'key' => 'arrival_date',
                    'value' => array($year . '0101', $year . '1231'),
                    'compare' => 'BETWEEN',
                    'type' => 'NUMERIC'
                )
            )
        );

        $user_data = new WP_Query($query_args);
        wp_cache_set($cache_key, $user_data);
    }

    if (!$user_data->have_posts()) {
        echo '<p>' . __('No entries found for this year.', 'wuest') . '</p>';
        return;
    }

    $yearly_prices = get_option('wuest_yearly_prices', array());
    $consumption_rate = get_option('wuest_consumption_rate', '1');
    $price = wuest_get_price_for_year($year, $yearly_prices);
    $rate = (float)$consumption_rate;

    $family_overnight_price_history = get_option('wuest_family_overnight_price_history', array());
    $guest_overnight_price_history = get_option('wuest_guest_overnight_price_history', array());

    // Sortiere die Preishistorien nach Datum (neueste zuerst)
    krsort($family_overnight_price_history);
    krsort($guest_overnight_price_history);

    echo '<div class="wuest-table-container">';
    echo '<table class="wuest-table">';
    echo '<thead>';
    echo '<tr>';
    echo '<th>' . __('Title', 'wuest') . '</th>';
    echo '<th>' . __('Arrival', 'wuest') . '</th>';
    echo '<th>' . __('Departure', 'wuest') . '</th>';
    echo '<th>' . __('Oil Costs (€)', 'wuest') . '</th>';
    echo '<th>' . __('Family Stay Costs (€)', 'wuest') . '</th>';
    echo '<th>' . __('Guest Stay Costs (€)', 'wuest') . '</th>';
    echo '<th>' . __('Total Costs (€)', 'wuest') . '</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';

    $total_costs = 0.0;

    while ($user_data->have_posts()) {
        $user_data->the_post();

        $burner_hours = get_field('burner_hours');
        $family_stays = get_field('family_overnight_stays');
        $guest_stays = get_field('guest_overnight_stays');
        $arrival_date_raw = get_field('arrival_date');
        $departure_date_raw = get_field('departure_date');

        $arrival_date = DateTime::createFromFormat('d/m/Y', $arrival_date_raw);
        $departure_date = DateTime::createFromFormat('d/m/Y', $departure_date_raw);

        $formatted_arrival_date = $arrival_date ? $arrival_date->format('d. F') : __('Invalid Date', 'wuest');
        $formatted_departure_date = $departure_date ? $departure_date->format('d. F') : __('Invalid Date', 'wuest');

        if ($arrival_date && $departure_date && $arrival_date->format('Y') !== $departure_date->format('Y')) {
            $formatted_arrival_date .= ' ' . $arrival_date->format('Y');
            $formatted_departure_date .= ' ' . $departure_date->format('Y');
        }

        $family_overnight_price = wuest_get_price_for_date($arrival_date->format('Y-m-d'), $family_overnight_price_history);
        $guest_overnight_price = wuest_get_price_for_date($arrival_date->format('Y-m-d'), $guest_overnight_price_history);

        $oil_costs = (float)$burner_hours * $rate * $price;
        $family_costs = (float)$family_stays * $family_overnight_price;
        $guest_costs = (float)$guest_stays * $guest_overnight_price;
        $entry_total = $oil_costs + $family_costs + $guest_costs;
        $total_costs += $entry_total;

        echo '<tr>';
        echo '<td data-label="' . __('Title', 'wuest') . '">' . get_the_title() . '</td>';
        echo '<td data-label="' . __('Arrival', 'wuest') . '">' . esc_html($formatted_arrival_date) . '</td>';
        echo '<td data-label="' . __('Departure', 'wuest') . '">' . esc_html($formatted_departure_date) . '</td>';
        echo '<td class="number" data-label="' . __('Oil Costs (€)', 'wuest') . '">' . number_format($oil_costs, 2, ',', '.') . ' €</td>';
        echo '<td class="number" data-label="' . __('Family Stay Costs (€)', 'wuest') . '">' . number_format($family_costs, 2, ',', '.') . ' €</td>';
        echo '<td class="number" data-label="' . __('Guest Stay Costs (€)', 'wuest') . '">' . number_format($guest_costs, 2, ',', '.') . ' €</td>';
        echo '<td class="number" data-label="' . __('Total Costs (€)', 'wuest') . '">' . number_format($entry_total, 2, ',', '.') . ' €</td>';
        echo '</tr>';
    }

    echo '</tbody>';
    echo '<tfoot>';
    echo '<tr>';
    echo '<td colspan="6">' . __('Total Costs', 'wuest') . '</td>';
    echo '<td class="number">' . number_format($total_costs, 2, ',', '.') . ' €</td>';
    echo '</tr>';
    echo '</tfoot>';
    echo '</table>';
    echo '</div>';

    wp_reset_postdata();
}

function wuest_display_all_users_data($year)
{
    global $wpdb;

    $cache_key = "wuest_all_users_data_{$year}";
    $all_users_data = wp_cache_get($cache_key);

    if ($all_users_data === false) {
        $query_args = array(
            'post_type' => 'consumption_entry',
            'posts_per_page' => -1,
            'orderby' => 'date',
            'order' => 'DESC',
            'meta_query' => array(
                array(
                    'key' => 'arrival_date',
                    'value' => array($year . '0101', $year . '1231'),
                    'compare' => 'BETWEEN',
                    'type' => 'NUMERIC'
                )
            )
        );

        $all_users_data = new WP_Query($query_args);
        wp_cache_set($cache_key, $all_users_data);
    }

    if (!$all_users_data->have_posts()) {
        echo '<p>' . __('No entries found for this year.', 'wuest') . '</p>';
        return;
    }

    $yearly_prices = get_option('wuest_yearly_prices', array());
    $consumption_rate = get_option('wuest_consumption_rate', '1');
    $price = wuest_get_price_for_year($year, $yearly_prices);
    $rate = (float)$consumption_rate;

    $family_overnight_price_history = get_option('wuest_family_overnight_price_history', array());
    $guest_overnight_price_history = get_option('wuest_guest_overnight_price_history', array());

    // Sortiere die Preishistorien nach Datum (neueste zuerst)
    krsort($family_overnight_price_history);
    krsort($guest_overnight_price_history);

    echo '<div class="wuest-table-container">';
    echo '<table class="wuest-table">';
    echo '<thead>';
    echo '<tr>';
    echo '<th>' . __('User', 'wuest') . '</th>';
    echo '<th>' . __('Title', 'wuest') . '</th>';
    echo '<th>' . __('Arrival', 'wuest') . '</th>';
    echo '<th>' . __('Departure', 'wuest') . '</th>';
    echo '<th>' . __('Oil Costs (€)', 'wuest') . '</th>';
    echo '<th>' . __('Family Stay Costs (€)', 'wuest') . '</th>';
    echo '<th>' . __('Guest Stay Costs (€)', 'wuest') . '</th>';
    echo '<th>' . __('Total Costs (€)', 'wuest') . '</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';

    $total_costs = 0.0;

    while ($all_users_data->have_posts()) {
        $all_users_data->the_post();

        $user = get_the_author();
        $burner_hours = get_field('burner_hours');
        $family_stays = get_field('family_overnight_stays');
        $guest_stays = get_field('guest_overnight_stays');
        $arrival_date_raw = get_field('arrival_date');
        $departure_date_raw = get_field('departure_date');

        $arrival_date = DateTime::createFromFormat('d/m/Y', $arrival_date_raw);
        $departure_date = DateTime::createFromFormat('d/m/Y', $departure_date_raw);

        $formatted_arrival_date = $arrival_date ? $arrival_date->format('d. F') : __('Invalid Date', 'wuest');
        $formatted_departure_date = $departure_date ? $departure_date->format('d. F') : __('Invalid Date', 'wuest');

        if ($arrival_date && $departure_date && $arrival_date->format('Y') !== $departure_date->format('Y')) {
            $formatted_arrival_date .= ' ' . $arrival_date->format('Y');
            $formatted_departure_date .= ' ' . $departure_date->format('Y');
        }

        $family_overnight_price = wuest_get_price_for_date($arrival_date->format('Y-m-d'), $family_overnight_price_history);
        $guest_overnight_price = wuest_get_price_for_date($arrival_date->format('Y-m-d'), $guest_overnight_price_history);

        $oil_costs = (float)$burner_hours * $rate * $price;
        $family_costs = (float)$family_stays * $family_overnight_price;
        $guest_costs = (float)$guest_stays * $guest_overnight_price;
        $entry_total = $oil_costs + $family_costs + $guest_costs;
        $total_costs += $entry_total;

        echo '<tr>';
        echo '<td data-label="' . __('User', 'wuest') . '">' . esc_html($user) . '</td>';
        echo '<td data-label="' . __('Title', 'wuest') . '">' . get_the_title() . '</td>';
        echo '<td data-label="' . __('Arrival', 'wuest') . '">' . esc_html($formatted_arrival_date) . '</td>';
        echo '<td data-label="' . __('Departure', 'wuest') . '">' . esc_html($formatted_departure_date) . '</td>';
        echo '<td class="number" data-label="' . __('Oil Costs (€)', 'wuest') . '">' . number_format($oil_costs, 2, ',', '.') . ' €</td>';
        echo '<td class="number" data-label="' . __('Family Stay Costs (€)', 'wuest') . '">' . number_format($family_costs, 2, ',', '.') . ' €</td>';
        echo '<td class="number" data-label="' . __('Guest Stay Costs (€)', 'wuest') . '">' . number_format($guest_costs, 2, ',', '.') . ' €</td>';
        echo '<td class="number" data-label="' . __('Total Costs (€)', 'wuest') . '">' . number_format($entry_total, 2, ',', '.') . ' €</td>';
        echo '</tr>';
    }

    echo '</tbody>';
    echo '<tfoot>';
    echo '<tr>';
    echo '<td colspan="7">' . __('Total Costs', 'wuest') . '</td>';
    echo '<td class="number">' . number_format($total_costs, 2, ',', '.') . ' €</td>';
    echo '</tr>';
    echo '</tfoot>';
    echo '</table>';
    echo '</div>';

    wp_reset_postdata();
}

function wuest_get_years_with_posts($user_id = null)
{
    global $wpdb;

    $user_clause = '';
    if ($user_id !== null) {
        $user_clause = $wpdb->prepare("AND post_author = %d", $user_id);
    }

    $cache_key = "wuest_years_with_posts_{$user_id}";
    $years = wp_cache_get($cache_key);

    if ($years === false) {
        $years = $wpdb->get_col("
            SELECT DISTINCT YEAR(meta_value) 
            FROM $wpdb->postmeta 
            WHERE meta_key = 'arrival_date' 
            AND post_id IN (
                SELECT ID FROM $wpdb->posts WHERE post_type = 'consumption_entry' $user_clause
            )
            ORDER BY meta_value ASC
        ");

        wp_cache_set($cache_key, $years);
    }

    return $years;
}

function wuest_get_price_for_year($year, $yearly_prices)
{
    $default_price = 1.0;

    $cache_key = "wuest_price_for_year_{$year}";
    $price = wp_cache_get($cache_key);

    if ($price === false) {
        if (empty($yearly_prices)) {
            $price = $default_price;
        } else {
            while ($year > 0) {
                if (isset($yearly_prices[$year])) {
                    $price = (float)$yearly_prices[$year];
                    break;
                }
                $year--;
            }
            $price = $price ?? $default_price;
        }

        wp_cache_set($cache_key, $price);
    }

    return $price;
}

function wuest_export_csv()
{
    if (!current_user_can('administrator')) {
        wp_die(__('You do not have sufficient permissions to access this page.', 'wuest'));
    }

    $year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=consumption_data_' . $year . '.csv');

    $output = fopen('php://output', 'w');

    fputcsv($output, array(
        __('User', 'wuest'),
        __('Title', 'wuest'),
        __('Arrival', 'wuest'),
        __('Departure', 'wuest'),
        __('Burner Hours', 'wuest'),
        __('Family Overnight Stays', 'wuest'),
        __('Guest Overnight Stays', 'wuest'),
        __('Oil Costs (€)', 'wuest'),
        __('Family Stay Costs (€)', 'wuest'),
        __('Guest Stay Costs (€)', 'wuest'),
        __('Total Costs (€)', 'wuest')
    ));

    $query_args = array(
        'post_type' => 'consumption_entry',
        'posts_per_page' => -1,
        'orderby' => 'date',
        'order' => 'DESC',
        'meta_query' => array(
            array(
                'key' => 'arrival_date',
                'value' => array($year . '0101', $year . '1231'),
                'compare' => 'BETWEEN',
                'type' => 'NUMERIC'
            )
        )
    );

    $entries = new WP_Query($query_args);

    $yearly_prices = get_option('wuest_yearly_prices', array());
    $consumption_rate = get_option('wuest_consumption_rate', '1');
    $price = wuest_get_price_for_year($year, $yearly_prices);
    $rate = (float)$consumption_rate;

    $family_overnight_price_history = get_option('wuest_family_overnight_price_history', array());
    $guest_overnight_price_history = get_option('wuest_guest_overnight_price_history', array());

    // Sortiere die Preishistorien nach Datum (neueste zuerst)
    krsort($family_overnight_price_history);
    krsort($guest_overnight_price_history);

    if ($entries->have_posts()) {
        while ($entries->have_posts()) {
            $entries->the_post();

            $user = get_the_author();
            $burner_hours = get_field('burner_hours');
            $family_stays = get_field('family_overnight_stays');
            $guest_stays = get_field('guest_overnight_stays');
            $arrival_date = get_field('arrival_date');
            $departure_date = get_field('departure_date');

            $arrival_date_obj = DateTime::createFromFormat('d/m/Y', $arrival_date);
            $family_overnight_price = wuest_get_price_for_date($arrival_date_obj->format('Y-m-d'), $family_overnight_price_history);
            $guest_overnight_price = wuest_get_price_for_date($arrival_date_obj->format('Y-m-d'), $guest_overnight_price_history);

            $oil_costs = (float)$burner_hours * $rate * $price;
            $family_costs = (float)$family_stays * $family_overnight_price;
            $guest_costs = (float)$guest_stays * $guest_overnight_price;
            $entry_total = $oil_costs + $family_costs + $guest_costs;

            fputcsv($output, array(
                $user,
                get_the_title(),
                $arrival_date,
                $departure_date,
                $burner_hours,
                $family_stays,
                $guest_stays,
                number_format($oil_costs, 2, ',', ''),
                number_format($family_costs, 2, ',', ''),
                number_format($guest_costs, 2, ',', ''),
                number_format($entry_total, 2, ',', '')
            ));
        }
    }

    wp_reset_postdata();
    fclose($output);
    exit;
}
add_action('admin_post_wuest_export_csv', 'wuest_export_csv');
