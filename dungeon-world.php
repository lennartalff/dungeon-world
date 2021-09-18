<?php
/*
Plugin Name: Dungeon World
Description: Dice roller and utility for Dungeon World
Version: 0.1
Author: Thies Lennart Alff
*/
include(plugin_dir_path(__FILE__) . 'inc/roll-table.php');
add_action('init', 'dw_create_db_table');
add_action('wp_enqueue_scripts', "dw_enqueue_js");
function dw_enqueue_js()
{
    wp_enqueue_script('dw_attribute_roll_js', plugins_url('/js/attribute-roll.js',  __FILE__), array('jquery'));
    wp_localize_script('dw_attribute_roll_js', 'ajax', array('url' => admin_url('admin-ajax.php'), 'nonce' => wp_create_nonce('ajax-nonce')));
}
add_shortcode('dw_attribute_roll', 'dw_attribute_roll_shortcode');

function dw_attribute_roll_shortcode()
{
    return dw_attribute_table();
}

function dw_db_name()
{
    global $wpdb;
    return $wpdb->prefix . 'dw_rolls';
}

function dw_create_db_table()
{
    global $wpdb;
    if (!current_user_can('activate_plugins')) return;

    $table_name = dw_db_name();
    $check_table = $wpdb->get_var("SHOW TABLES LIKE '$table_name'");
    if ($check_table != $table_name) {
        $sql_query = "CREATE TABLE " . $table_name . " (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            name TINYTEXT NOT NULL,
            type TINYTEXT NOT NULL,
            roll TINYTEXT NOT NULL,
            comment TEXT NOT NULL,
            UNIQUE KEY id (id));";
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql_query);
    }
}

add_shortcode("dw_roll_table", "dw_roll_table_shortcode");

add_action('wp_ajax_nopriv_dw_add_roll_to_db', 'dw_add_roll_to_db');
add_action('wp_ajax_dw_add_roll_to_db', 'dw_add_roll_to_db');
function dw_roll_table_shortcode()
{
    return dw_roll_table();
}

function dw_add_roll_to_db()
{
    if (!wp_verify_nonce($_POST['nonce'], 'ajax-nonce')) {
        die("Nonce failed!");
    }
    global $wpdb;
    $roll_text = sanitize_text_field($_POST["roll_text"]);
    $roll_type = sanitize_text_field($_POST["roll_type"]);
    $roll_comment = sanitize_text_field($_POST["roll_comment"]);
    $roll_user = sanitize_text_field($_POST["roll_user"]);
    $insert_row = $wpdb->insert(
        dw_db_name(),
        array(
            'name' => $roll_user,
            'type' => $roll_type,
            'roll' => $roll_text,
            'comment' => $roll_comment
        )
    );
    if ($insert_row) {
        echo json_encode(array('res' => true, 'message' => __('Successfully added roll.')));
    } else {
        echo json_encode(array('res' => false, 'message' => __('Failed to add roll.')));
    }
    wp_die();
}

add_action('wp_ajax_nopriv_dw_get_rolls', 'dw_get_rolls');
add_action('wp_ajax_dw_get_rolls', 'dw_get_rolls');
function dw_get_rolls()
{
    if (!wp_verify_nonce($_POST['nonce'], 'ajax-nonce')) {
        die("Nonce failed!");
    }
    global $wpdb;
    $id = sanitize_text_field($_POST["dw_last_roll_id"]);
    $output = array('success' => false, 'message' => __('No new results.'), 'dw_last_roll_id' => $id);
    $results = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . dw_db_name() . " WHERE id >" . $id . " ORDER BY id"));
    if ($results) {
        $output['success'] = true;
        $last_id = intval($id);
        $rolls = array();
        foreach ($results as $r) {
            $rolls[] = array(
                'name' => sanitize_text_field($r->name),
                'type' => sanitize_text_field($r->type),
                'roll' => sanitize_text_field($r->roll),
                'comment' => sanitize_text_field($r->comment),
            );
            if (intval($r->id) > $last_id) {
                $last_id = intval($r->id);
            }
        }
        $output['rolls'] = $rolls;
        $output['dw_last_roll_id'] = $last_id;
    }
    echo json_encode($output);
    wp_die();
}
