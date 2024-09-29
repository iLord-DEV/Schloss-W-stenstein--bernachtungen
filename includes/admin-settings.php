<?php
// Verhindert den direkten Aufruf der Datei
if (!defined('ABSPATH')) exit;

function wuest_burner_hours_admin_menu()
{
    add_menu_page(
        __('Wüstenstein', 'wuest'),
        __('Wüstenstein', 'wuest'),
        'manage_options',
        'wuest_burner_hours_settings',
        'wuest_burner_hours_settings_page',
        'dashicons-admin-generic'
    );
}
add_action('admin_menu', 'wuest_burner_hours_admin_menu');

function wuest_burner_hours_settings_page()
{
    // Nonce für die Sicherheit
    $nonce_action = 'wuest_burner_hours_save_settings';
    $nonce_name = 'wuest_nonce';

    // Werte aus der Datenbank abrufen
    $yearly_prices = get_option('wuest_yearly_prices', array());
    $consumption_rate = get_option('wuest_consumption_rate', '1');
    $show_all_entries = get_option('wuest_show_all_entries', false);
    $family_overnight_price = get_option('wuest_family_overnight_price', '0');
    $guest_overnight_price = get_option('wuest_guest_overnight_price', '0');

    if (!is_array($yearly_prices)) {
        $yearly_prices = array();
    }

    // Jahre ohne Preis abrufen
    $years_without_price = wuest_get_years_without_price($yearly_prices);

    // Tailwind CSS einbinden
    wp_enqueue_style('tailwind', 'https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css');
?>
    <div class="wrap">
        <h1 class="text-2xl font-bold mb-6"><?php echo __('Manage Yearly Oil Prices and Overnight Stay Prices', 'wuest'); ?></h1>

        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
            <?php wp_nonce_field($nonce_action, $nonce_name); ?>
            <input type="hidden" name="action" value="wuest_burner_hours_save_settings">

            <h2 class="text-xl font-semibold mb-4"><?php echo __('Global Settings', 'wuest'); ?></h2>
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="wuest_consumption_rate">
                    <?php echo __('Consumption Rate (Liters per Burner Hour)', 'wuest'); ?>
                </label>
                <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="wuest_consumption_rate" type="text" name="wuest_consumption_rate" value="<?php echo esc_attr($consumption_rate); ?>">
            </div>
            <div class="mb-4">
                <label class="inline-flex items-center">
                    <input type="checkbox" class="form-checkbox" name="wuest_show_all_entries" <?php checked($show_all_entries, true); ?>>
                    <span class="ml-2"><?php echo __('Show all consumption entries in the admin area', 'wuest'); ?></span>
                </label>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="wuest_family_overnight_price">
                    <?php echo __('Price per Family Overnight Stay (€)', 'wuest'); ?>
                </label>
                <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="wuest_family_overnight_price" type="text" name="wuest_family_overnight_price" value="<?php echo esc_attr($family_overnight_price); ?>">
            </div>
            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="wuest_guest_overnight_price">
                    <?php echo __('Price per Guest Overnight Stay (€)', 'wuest'); ?>
                </label>
                <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="wuest_guest_overnight_price" type="text" name="wuest_guest_overnight_price" value="<?php echo esc_attr($guest_overnight_price); ?>">
            </div>

            <?php if (!empty($years_without_price)) : ?>
                <h2 class="text-xl font-semibold mb-4"><?php echo __('Add Price for Year', 'wuest'); ?></h2>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="new_year">
                        <?php echo __('Year', 'wuest'); ?>
                    </label>
                    <select class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="new_year" name="new_year">
                        <option value=""><?php echo __('Select Year', 'wuest'); ?></option>
                        <?php foreach ($years_without_price as $year) : ?>
                            <option value="<?php echo esc_attr($year); ?>"><?php echo esc_html($year); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-6">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="new_price">
                        <?php echo __('Price (€ per Liter)', 'wuest'); ?>
                    </label>
                    <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="new_price" type="text" name="new_price" value="">
                </div>
            <?php endif; ?>

            <?php if (!empty($yearly_prices)) : ?>
                <h2 class="text-xl font-semibold mb-4"><?php echo __('Existing Prices', 'wuest'); ?></h2>
                <div class="overflow-x-auto">
                    <table class="w-full mb-6">
                        <thead>
                            <tr>
                                <th class="px-4 py-2 text-left"><?php echo __('Year', 'wuest'); ?></th>
                                <th class="px-4 py-2 text-left"><?php echo __('Price (€ per Liter)', 'wuest'); ?></th>
                                <th class="px-4 py-2 text-left"><?php echo __('Actions', 'wuest'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($yearly_prices as $year => $price) : ?>
                                <tr>
                                    <td class="border px-4 py-2">
                                        <input type="text" class="w-full bg-gray-100" name="wuest_years[<?php echo esc_attr($year); ?>]" value="<?php echo esc_attr($year); ?>" readonly>
                                    </td>
                                    <td class="border px-4 py-2">
                                        <input type="text" class="w-full" name="wuest_prices[<?php echo esc_attr($year); ?>]" value="<?php echo esc_attr($price); ?>">
                                    </td>
                                    <td class="border px-4 py-2">
                                        <button type="submit" name="wuest_remove_year" value="<?php echo esc_attr($year); ?>" class="bg-red-500 hover:bg-red-700 text-white font-bold py-1 px-2 rounded focus:outline-none focus:shadow-outline">
                                            <?php echo __('Remove', 'wuest'); ?>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

            <?php submit_button(__('Save Changes', 'wuest'), 'primary', 'submit', false, array('class' => 'bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline')); ?>
        </form>
    </div>
<?php
}

function wuest_burner_hours_save_settings()
{
    // Überprüfen der Nonce
    if (!isset($_POST['wuest_nonce']) || !wp_verify_nonce($_POST['wuest_nonce'], 'wuest_burner_hours_save_settings')) {
        wp_die('Nonce verification failed.');
    }

    $yearly_prices = get_option('wuest_yearly_prices', array());

    if (!is_array($yearly_prices)) {
        $yearly_prices = array();
    }

    // Verbrauchsrate speichern
    if (isset($_POST['wuest_consumption_rate'])) {
        $consumption_rate = sanitize_text_field($_POST['wuest_consumption_rate']);
        update_option('wuest_consumption_rate', $consumption_rate);
    }

    // Option für das Anzeigen aller Einträge speichern
    $show_all_entries = isset($_POST['wuest_show_all_entries']) ? true : false;
    update_option('wuest_show_all_entries', $show_all_entries);

    // Preise für Übernachtungen speichern
    if (isset($_POST['wuest_family_overnight_price'])) {
        $family_overnight_price = sanitize_text_field($_POST['wuest_family_overnight_price']);
        $current_family_price = get_option('wuest_family_overnight_price', '0');
        if ($family_overnight_price !== $current_family_price) {
            $family_price_history = get_option('wuest_family_overnight_price_history', array());
            $family_price_history[current_time('Y-m-d')] = $family_overnight_price;
            update_option('wuest_family_overnight_price_history', $family_price_history);
        }
        update_option('wuest_family_overnight_price', $family_overnight_price);
    }
    if (isset($_POST['wuest_guest_overnight_price'])) {
        $guest_overnight_price = sanitize_text_field($_POST['wuest_guest_overnight_price']);
        $current_guest_price = get_option('wuest_guest_overnight_price', '0');
        if ($guest_overnight_price !== $current_guest_price) {
            $guest_price_history = get_option('wuest_guest_overnight_price_history', array());
            $guest_price_history[current_time('Y-m-d')] = $guest_overnight_price;
            update_option('wuest_guest_overnight_price_history', $guest_price_history);
        }
        update_option('wuest_guest_overnight_price', $guest_overnight_price);
    }

    // Jahr und Preis hinzufügen, nur wenn beide Felder ausgefüllt sind
    if (!empty($_POST['new_year']) && !empty($_POST['new_price'])) {
        $new_year = sanitize_text_field($_POST['new_year']);
        $new_price = sanitize_text_field($_POST['new_price']);

        // Fehlerüberprüfung
        if (ctype_digit($new_year) && is_numeric($new_price)) {
            // Jahr hinzufügen, wenn es noch nicht existiert
            if (!isset($yearly_prices[$new_year])) {
                $yearly_prices[$new_year] = $new_price;
                update_option('wuest_yearly_prices', $yearly_prices);
            } else {
                wp_die('Error: Year already exists.');
            }
        } else {
            wp_die('Error: Year and Price must be numeric.');
        }
    }

    // Entfernen eines Jahres
    if (isset($_POST['wuest_remove_year'])) {
        $remove_year = sanitize_text_field($_POST['wuest_remove_year']);
        if (isset($yearly_prices[$remove_year])) {
            unset($yearly_prices[$remove_year]);
            update_option('wuest_yearly_prices', $yearly_prices);

            wp_redirect(admin_url('admin.php?page=wuest_burner_hours_settings&removed=true'));
            exit;
        } else {
            wp_die('Error: Year to remove not found.');
        }
    }

    // Speichern der Änderungen
    if (isset($_POST['wuest_prices'])) {
        foreach ($_POST['wuest_prices'] as $year => $price) {
            $year = sanitize_text_field($year);
            $price = sanitize_text_field($price);

            if (isset($yearly_prices[$year])) {
                $yearly_prices[$year] = $price;
            } else {
                wp_die('Error: Year for price update not found.');
            }
        }

        update_option('wuest_yearly_prices', $yearly_prices);
    }

    wp_redirect(admin_url('admin.php?page=wuest_burner_hours_settings&updated=true'));
    exit;
}
add_action('admin_post_wuest_burner_hours_save_settings', 'wuest_burner_hours_save_settings');

function wuest_get_years_without_price($yearly_prices)
{
    global $wpdb;

    // Caching der Jahre ohne Preis
    $cache_key = 'wuest_years_without_price';
    $years_without_price = wp_cache_get($cache_key);

    if ($years_without_price === false) {
        $years = $wpdb->get_col("
            SELECT DISTINCT YEAR(meta_value) 
            FROM $wpdb->postmeta 
            WHERE meta_key = 'arrival_date' 
            AND post_id IN (
                SELECT ID FROM $wpdb->posts WHERE post_type = 'consumption_entry'
            )
            ORDER BY meta_value ASC
        ");

        $years_without_price = array_diff($years, array_keys($yearly_prices));
        wp_cache_set($cache_key, $years_without_price);
    }

    return $years_without_price;
}
