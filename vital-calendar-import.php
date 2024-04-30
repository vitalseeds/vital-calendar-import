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

if (TESTING) {
    define('TEST_FILE', file(plugin_dir_path(__FILE__) . "examples/calendar_data.csv"));
}

define('PLUGIN_SLUG', 'vital-calendar-import');
define('PLUGIN_ROLE', 'manage_options');
define('PLUGIN_DOMAIN', 'vital-calendar-import');
define('PLUGIN_NAME', 'Calendar import');

// TODO: handle multiple sow/plant/harvest ranges
define('REQUIRED_CSV_HEADERS', array(
    "category",
    "sow_months_start_month",
    "sow_months_end_month",
    // "sow_months2_start_month",
    // "sow_months2_end_month",
    "plant_months_start_month",
    "plant_months_end_month",
    "harvest_months_start_month",
    "harvest_months_end_month",
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

/**
 * This function handles the import category CSV form.
 * It checks if the form has been submitted or if TESTING mode is enabled.
 * Performs the necessary steps based on the current step.
 * Step 1: processe the CSV file and validates the headers.
 * Step 2: include the upload form for step 3.
 * If none of the above conditions are met, it includes the upload form for step 1.
 */
function import_category_csv_form()
{
    if (isset($_POST['submit']) || TESTING) {
        $step = isset($_POST['step']) ? $_POST['step'] : 1;

        session_start();
        switch ($step) {
            case 1:
                $csv_file = TESTING ? TEST_FILE : file($_FILES['csv_file']['tmp_name']);

                $rows = array_map('str_getcsv', $csv_file);
                $headers = validate_csv_headers($rows);
                [$data, $errors] = process_rows($rows);

                $_SESSION['data'] = $data;
                $_SESSION['step'] = 2;

                include('includes/upload_form_2.php');
                break;
            case 2:
                $data = $_SESSION['data'];

                $results = populate_category_acf_fields($data);

                unset($_SESSION['step']);
                unset($_SESSION['data']);

                include('includes/upload_form_3.php');
                break;
            default:
                break;
        }
    } else {
        include('includes/upload_form_1.php');
    }
}

/**
 * Validates the headers of a CSV file.
 *
 * This function checks if the headers of a CSV file contain all the required fields.
 * If the headers do not match the expected headers, an error message is displayed.
 *
 * @param array $rows The rows of the CSV file.
 * @return array|false The headers of the CSV file if they are valid, false otherwise.
 */
function validate_csv_headers($rows)
{
    $headers = $rows[0];
    // check headers contain all required fields
    if (array_diff(REQUIRED_CSV_HEADERS, $headers)) {
        echo "<p>CSV headers do not match expected headers</p>";
        return false;
    }

    return $headers;
}


/**
 * Processes the rows of a CSV file.
 *
 * This function takes an array of rows from a CSV file and processes each row to create an associative array of data.
 * It validates the CSV headers, retrieves the product category term, and combines the headers with each row to create
 * the final data array. Any errors encountered during processing are collected and returned along with the data array.
 *
 * @param array $rows An array of rows from a CSV file.
 * @return array An array containing the processed data and any errors encountered.
 */
function process_rows($rows)
{
    $headers = validate_csv_headers($rows);
    if (!$headers) return;

    $data = [];
    $errors = [];

    foreach ($rows as $i => $row) {
        if ($i == 0) {
            continue;
        }

        $product_cat = get_term_by('name', $row[0], 'product_cat');
        if (!$product_cat) {
            $errors[] = "Category not found: " . $row[0];
            continue;
        }
        $product_cat->description = 'truncated..';
        $row[0] = $product_cat;
        // $data[$product_cat->term_id] = array_combine($headers, $row);
        $data[] = array_combine($headers, $row);
    }

    return [$data, $errors];
}

function populate_category_acf_fields($data)
{
    $row = $data[0];
    $results = [];

    foreach ($data as $i => $row) {
        $term_id = $row['category']->term_id;

        $sow_months_start_month = $row['sow_months_start_month'];
        $sow_months_end_month = $row['sow_months_end_month'];
        $plant_months_start_month = $row['plant_months_start_month'];
        $plant_months_end_month = $row['plant_months_end_month'];
        $harvest_months_start_month = $row['harvest_months_start_month'];
        $harvest_months_end_month = $row['harvest_months_end_month'];

        // Update ACF fields for the product category
        update_field('enable_sowing_calendar', 1, 'product_cat_' . $term_id);

        update_field('sow_months_start_month', $sow_months_start_month, 'product_cat_' . $term_id);
        update_field('sow_months_end_month', $sow_months_end_month, 'product_cat_' . $term_id);
        update_field('plant_months_start_month', $plant_months_start_month, 'product_cat_' . $term_id);
        update_field('plant_months_end_month', $plant_months_end_month, 'product_cat_' . $term_id);
        update_field('harvest_months_start_month', $harvest_months_start_month, 'product_cat_' . $term_id);
        update_field('harvest_months_end_month', $harvest_months_end_month, 'product_cat_' . $term_id);

        $results[$term_id] = $row['category']->slug;
    }

    return $results;
}
