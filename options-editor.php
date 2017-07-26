<?php
/*
Plugin Name: Advanced Options Editor
Plugin URI: http://wordpress.org/plugins/options-editor/
Description: Edit WP Options from the WordPress Dashboard
Author: Jules Colle
Version: 1.0
Author URI: http://bdwm.be
*/

if ( ! defined( 'ABSPATH' ) ) exit;

define('BDWMOE_PLUGIN_DIR_URL', untrailingslashit(plugin_dir_url(__FILE__)));
define('BDWMOE_VERSION', '1.0');

add_action('admin_menu', 'bdwm_admin_add_page');
function bdwm_admin_add_page(){
    global $bdwm_options;
    add_submenu_page('options-general.php', 'Advanced Options Editor', 'Advanced Options Editor', 'manage_options', 'bdwmoe', 'options_editor_page');
}

function options_editor_page() {
    include 'options-editor-form.php';
}

function bdwmoe_enqueue_scripts() {
    wp_enqueue_style('font-awesome', '//maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css');
    wp_enqueue_style( 'bdwmoe_admin_css', BDWMOE_PLUGIN_DIR_URL . '/admin-style.css', false, BDWMOE_VERSION );
}
add_action( 'admin_enqueue_scripts', 'bdwmoe_enqueue_scripts' );

add_action( 'wp_ajax_bdwmoe_update_option', 'bdwmoe_update_option' );

function bdwmoe_update_option() {

    $name = stripslashes_deep(sanitize_text_field($_POST['name']));

    if (!wp_verify_nonce($_POST['nonce'], 'bdwmoe-edit-option-'.$name)) {
        echo 'nonce could not be verified. Please refresh the page and try again.';
        wp_die();
    }

    if (!current_user_can('manage_options')) {
        echo 'you are not allowed to manage options';
        wp_die();
    }

    global $wpdb;

    if (array_key_exists('jsonstring',$_POST)) {
        $json_data = json_decode(stripslashes_deep(sanitize_textarea_field($_POST['jsonstring'])));
        if (is_null($json_data)) {
            echo 'bad json';
            wp_die();
        }
        if ($_POST['is_object'] == 'true') {
            $data = serialize((object)$json_data);
        } else {
            $data = serialize((array)$json_data);
        }
    } else if (array_key_exists('serializeddata',$_POST)) {
        $data = stripslashes_deep(sanitize_text_field($_POST['serializeddata']));
    } else {
        $data = '';
    }

    $result = $wpdb->update($wpdb->options, array('option_value' => $data), array('option_name' => $name));

    echo $data;

    wp_die(); // this is required to terminate immediately and return a proper response
}

add_action( 'wp_ajax_bdwmoe_add_option', 'bdwmoe_add_option' );

function bdwmoe_add_option() {

    if (!wp_verify_nonce($_POST['nonce'], 'bdwmoe-add-option')) {
        echo 'nonce_err';
        wp_die();
    }

    if (!current_user_can('manage_options')) {
        echo 'error';
        wp_die();
    }

    if (add_option(stripslashes_deep(sanitize_text_field($_POST['name'])))) {
        echo '1';
    } else {
        echo '0';
    }

    wp_die();
}

add_action( 'wp_ajax_bdwmoe_delete_option', 'bdwmoe_delete_option' );

function bdwmoe_delete_option() {

    $name = stripslashes_deep(sanitize_text_field($_POST['name']));

    if (!wp_verify_nonce($_POST['nonce'], 'bdwmoe-edit-option-'.$name)) {
        echo 'nonce_err';
        wp_die();
    }

    if (!current_user_can('manage_options')) {
        echo 'error';
        wp_die();
    }

    if (delete_option($name)) {
        echo '1';
    } else {
        echo '0';
    }

    wp_die();
}

add_action( 'wp_ajax_bdwmoe_edit_serialized_data', 'bdwmoe_edit_serialized_data' );

function bdwmoe_edit_serialized_data() {

    include 'edit-serialized-data.php';

    exit();
}

