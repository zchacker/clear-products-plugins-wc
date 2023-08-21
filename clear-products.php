<?php
/*
 * Plugin Name: clear products & category
 * Description: This plugin will help you clear all products in one click
 * Version: 1.0.9
 * Author: Brown Diamond Tech Ltd.
 * Author URI: https://browndiamondstech.com/
 * License: GPL v2 or later
 */

function my_plugin_add_admin_page() {
    add_submenu_page(
        'edit.php?post_type=product',     // Parent slug (replace with appropriate WooCommerce submenu)
        'حذف جميع المنتجات',  // Page title
        'حذف جميع المنتجات',  // Menu title
        'manage_options',  // Capability required to access the page
        'clear-products',  // Menu slug
        'clear_products_render_admin_page'  // Callback function to render the page content
    );
}
add_action( 'admin_menu', 'my_plugin_add_admin_page' );

function clear_products_render_admin_page() {
    ?>
    <div class="wrap">
        <h1>حذف جميع المنتجات</h1>
        <p>يمكنك بكل بساطة, حذف جميع المنتجات والاقسام بضغطة زر</p>
        <button id="my-plugin-confirm">البدء بحذف جميع المنتجات الان</button>
        <div id="my-plugin-message"></div>
    </div>
    <?php
}


function my_plugin_enqueue_scripts() {
    wp_enqueue_script( 'my-plugin-script', plugin_dir_url( __FILE__ ) . 'js/my-plugin-script.js?v=1.0.8', array( 'jquery' ), '1.0.0', true );
}
add_action( 'admin_enqueue_scripts', 'my_plugin_enqueue_scripts' );


function my_plugin_run_sql_command() {
    global $wpdb;

    // Run your SQL command here
    $result = $wpdb->query("
            DELETE relations.*, taxes.*, terms.*
            FROM wp_term_relationships AS relations
            INNER JOIN wp_term_taxonomy AS taxes
            ON relations.term_taxonomy_id = taxes.term_taxonomy_id
            INNER JOIN wp_terms AS terms
            ON taxes.term_id = terms.term_id
            WHERE object_id IN (
            SELECT ID FROM wp_posts WHERE post_type IN ('product','product_variation')
            )
            AND object_id NOT IN (127000028192,127000028195,127000028198,127000028200,127000028202,127000028204);
    ");

    $wpdb->query("
        DELETE FROM wp_postmeta WHERE post_id IN (
            SELECT ID FROM wp_posts WHERE post_type IN ('product','product_variation')
            AND ID NOT IN (127000028192,127000028195,127000028198,127000028200,127000028202,127000028204) 
        );
    ");
    $wpdb->query("
        DELETE FROM wp_posts WHERE post_type IN ('product','product_variation')
        AND ID NOT IN (127000028192,127000028195,127000028198,127000028200,127000028202,127000028204);
    ");

    //if ($result !== false) {
    if (!$wpdb->last_error) {
        wp_send_json_success('تم حذف المنتجات والاقسام بنجاح, المقاسات لم يتم حذفها!!');
    } else {
        wp_send_json_error('حدث خطأ غير متوقع أثناء الحذف, حاول مرة أخرى ');
    }
}
add_action( 'wp_ajax_my_plugin_run_sql_command', 'my_plugin_run_sql_command' );


?>