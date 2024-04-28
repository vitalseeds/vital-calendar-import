<?php

/*
Plugin Name:  Vital Sowing Calendar import
Plugin URI:   https://github.com/vitalseeds/vital-sowing-calendar
Description:  Import calendar from CSV. Requires Advanced Custom Fields (ACF).
Version:      2.0
Author:       tombola
Author URI:   https://github.com/tombola
License:      GPL2
License URI:  https://github.com/vitalseeds/vital-sowing-calendar/blob/main/LICENSE
Text Domain:  vital-sowing-calendar
Domain Path:  /languages
*/


// Define constants
define('TESTING', true);

define('PLUGIN_SLUG', 'vital-calendar-import');
define('PLUGIN_ROLE', 'manage_options');
define('PLUGIN_DOMAIN', 'vital-calendar-import');
define('PLUGIN_NAME', 'Calendar import');

define('REQUIRED_CSV_HEADERS', array(
    "category",
    "sow_months_start_month",
    "sow_months_end_month",
    // "sow_months2_start_month",
    // "sow_months2_end_month",
    "plant_months_start_month",
    "plant_sow_months_end_month",
    "harvest_months_start_month",
    "harvest_sow_months_end_month",
    // "note",
));

add_action('admin_menu', 'register_admin_import_page', 9);

function register_admin_import_page()
{
    add_menu_page(
        __(PLUGIN_NAME, PLUGIN_DOMAIN),
        PLUGIN_NAME,
        PLUGIN_ROLE,
        PLUGIN_SLUG,
        false,
        'dashicons-admin-generic',
        ''
    );

    add_submenu_page(
        PLUGIN_SLUG,
        PLUGIN_NAME,
        'Category Import',
        PLUGIN_ROLE,
        PLUGIN_SLUG,
        'import_category_csv_form',
    );
    add_submenu_page(
        PLUGIN_SLUG,
        PLUGIN_NAME,
        'Product Import',
        PLUGIN_ROLE,
        PLUGIN_SLUG,
        'import_product_csv_form',
    );
}

function import_product_csv_form()
{
    return;
}

function import_category_csv_form()
{
    if (TESTING) {
        $example_csv = file(plugin_dir_path(__FILE__) . "examples/calendar_data.csv");
        $rows = array_map('str_getcsv', $example_csv);
        echo "<h1>TESTING</h1>";
        echo "<p>Using <code>examples/calendar_data.csv</code></p>";
        $data = process_rows($rows);
        echo "<pre>";
        print_r($data);
        echo "</pre>";

        return;
    }

    if (isset($_POST['submit'])) {
        $csv_file = $_FILES['csv_file'];
        $rows = array_map('str_getcsv', file($csv_file['tmp_name']));
        $data = process_rows($rows);
    } else {
        include('includes/upload_form.php');
    }
}

function validate_csv_headers($rows)
{
    $headers = $rows[0];
    // check headers contain all required fields
    if (array_diff(REQUIRED_CSV_HEADERS, $headers)) {
        echo "<p>CSV headers do not match expected headers</p>";
        return false;
    }
    echo "<pre>";
    print_r($headers);
    echo "</pre>";
    return $headers;
}

function process_rows($rows)
{
    $headers = validate_csv_headers($rows);
    if (!$headers) return;

    $data = [];

    foreach ($rows as $i => $value) {
        if ($i == 0) {
            continue;
        }
    }

    return $data;
}


// function load_my_plugin_scripts($hook)
// {

//     // Load only on ?page=sample-page
//     if ($hook != 'toplevel_page_sample-page') {
//         return;
//     }
//     // Load style & scripts.
//     wp_enqueue_style(PLUGIN_SLUG);
//     wp_enqueue_script(PLUGIN_SLUG);
// }

// add_action('admin_enqueue_scripts', 'load_my_plugin_scripts');
