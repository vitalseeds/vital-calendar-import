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
        'Dashboard',
        PLUGIN_ROLE,
        PLUGIN_SLUG,
        'import_csv_form',
    );
}

function import_csv_form()
{
    if (TESTING) {
        $example_csv = file(plugin_dir_path(__FILE__) . "examples/calendar_data.csv");
        $rows = array_map('str_getcsv', $example_csv);
        $data = process_rows($rows);
        echo "<h1>TESTING</h1>";
        echo "<p>Using <code>examples/calendar_data.csv</code></p>";
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

function process_rows($rows)
{
    $data = [];

    foreach ($rows as $i => $value) {
        if ($i == 0) {
            $headers = $value;
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
