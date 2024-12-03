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
define('TESTING', false);

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

define('VITAL_MONTH_CHOICE_VALUES', array(
    'jan1',
    'jan2',
    'feb1',
    'feb2',
    'mar1',
    'mar2',
    'apr1',
    'apr2',
    'may1',
    'may2',
    'jun1',
    'jun2',
    'jul1',
    'jul2',
    'aug1',
    'aug2',
    'sep1',
    'sep2',
    'oct1',
    'oct2',
    'nov1',
    'nov2',
    'dec1',
    'dec2',
));

// define('VITAL_CALENDAR_FIELDS', array(
//     'enable_sowing_calendar',
//     'vs_calendar_sow_month_parts',
//     'vs_calendar_plant_month_parts',
//     'vs_calendar_harvest_month_parts',
//     'vs_calendar_other1_month_parts',
//     'vs_calendar_other1_label',
//     'vs_calendar_other2_month_parts',
//     'vs_calendar_other2_label',
// ));


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
        29
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
 * Step 1: processes the CSV file and validates the headers.
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


/**
 * Maps a range of months to an array of corresponding scalar values (ACF choices).
 *
 * This function takes a start month and an end month as parameters and returns an array
 * containing the corresponding scalar values for each month in the range. The start month
 * and end month are inclusive.
 *
 * @param float $start_month The start month of the range.
 * @param float $end_month The end month of the range.
 * @return array An array of scalar values corresponding to the months in the range.
 */

function map_month__range_to_scalar($start_month, $end_month)
{
    $start_month = floatval($start_month);
    $end_month = floatval($end_month);

    // array index starts at 0, so minus 1
    // there are 2 choices per month so multiply by 2
    $start_month_choice = floor(($start_month - 1) * 2);
    $end_month_choice = ceil(($end_month - 1) * 2);

    $scalar_values = array_slice(VITAL_MONTH_CHOICE_VALUES, $start_month_choice, $end_month_choice - $start_month_choice);
    return $scalar_values;
}

function get_month_parts_from_multiple_ranges($row, $start_field_name, $end_field_name)
{
    $start_field_names = array_filter(
        $row,
        fn($key) => strpos($key, $start_field_name) === 0,
        ARRAY_FILTER_USE_KEY
    );
    ksort($start_field_names);

    $end_field_names = array_filter(
        $row,
        fn($key) => strpos($key, $end_field_name) === 0,
        ARRAY_FILTER_USE_KEY
    );
    ksort($end_field_names);

    if (count($start_field_names) !== count($end_field_names)) {
        return;
    }
    $month_parts = [];
    for ($i = 0; $i < count($start_field_names); $i++) {
        $range_start = current(array_slice($start_field_names, $i, 1, false));
        $range_end = current(array_slice($end_field_names, $i, 1, true));

        $month_parts = array_merge($month_parts, map_month__range_to_scalar($range_start, $range_end));
    }
    return $month_parts;
}

/**
 * Populates the ACF fields for each product category with calendar data.
 *
 * This function takes an array of data (from CSV) containing calendar information for each category
 * and updates the corresponding ACF fields for each category with the provided data.
 *
 * @param array $data An array of data containing calendar information for each category.
 * @return array An array containing the updated ACF fields for each category.
 */
function populate_category_acf_fields($data)
{
    $row = $data[0];
    $results = [];

    foreach ($data as $i => $row) {
        $term_id = $row['category']->term_id;

        $sow_month_parts = get_month_parts_from_multiple_ranges($row, 'sow_months_start_month', 'sow_months_end_month');
        $plant_month_parts = get_month_parts_from_multiple_ranges($row, 'plant_months_start_month', 'plant_months_end_month');
        $harvest_month_parts = get_month_parts_from_multiple_ranges($row, 'harvest_months_start_month', 'harvest_months_end_month');

        // Update ACF fields for the product category
        update_field('enable_sowing_calendar', 1, 'product_cat_' . $term_id);

        update_field('vs_calendar_sow_month_parts', $sow_month_parts, 'product_cat_' . $term_id);
        update_field('vs_calendar_plant_month_parts', $plant_month_parts, 'product_cat_' . $term_id);
        update_field('vs_calendar_harvest_month_parts', $harvest_month_parts, 'product_cat_' . $term_id);

        $results[$term_id] = $row['category']->slug;
    }
    return $results;
}
