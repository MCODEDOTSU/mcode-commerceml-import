<?php

/*
 * Plugin Name: CommerceML 1.0 Import by MCode
 * Plugin URI: https://github.com/MCODEDOTSU/mcode-commerceml-import
 * Description: Import prices from a CommerceML 1.0 upload.
 * Version: 1.0
 * Author: Brykova Aliona
 * Author URI: http://mcode.su/
 * License: GPLv2 or later
 */

if (is_admin()) {

    add_action('admin_menu', 'mcode_commerceml_admin');
    wp_register_style('mcode_commerceml_style', plugins_url('/style.css', __FILE__), array(), date('His'));
    wp_enqueue_style('mcode_commerceml_style');
}

function mcode_commerceml_admin()
{
    add_menu_page(__('Prices Import', 'mcode-commerceml-import'), __('Prices Import', 'mcode-commerceml-import'), 'edit_posts', 'mcode-commerceml-import', 'mcode_commerceml_page', 'dashicons-images-alt2');
}

/***
 * Add Plugin's Page
 */
function mcode_commerceml_page()
{

    $result = [];

    if (isset($_POST["submit"]) && !empty($_POST['course'])) {

        $course = round((float)$_POST['course'], 2);
        $precision = empty($_POST['precision']) ? 0 : (int)$_POST['precision'];

        $uploads = wp_get_upload_dir();
        $directory = $uploads['basedir'] . '/mcode';

        if (!file_exists($directory)) {
            mkdir($directory);
        }

        $file = $directory . '/' . basename($_FILES['import']['name']);

        if (move_uploaded_file($_FILES['import']['tmp_name'], $file)) {
            $result = mcode_commerceml_import($file, $course, $precision);
        } else {
            echo __("File upload error\n", 'mcode-commerceml-import');
        }

        update_option('mcode_commerceml_course', $course);
        update_option('mcode_commerceml_precision', $precision);

    }

    $course = get_option('mcode_commerceml_course');
    $precision = get_option('mcode_commerceml_precision');

    require_once plugin_dir_path(__FILE__) . 'import-page.php';
}

/***
 * Import Start
 * @param string $file
 * @param $course
 * @param int $precision
 * @return array
 */
function mcode_commerceml_import($file = '', $course, $precision = 0)
{
    $import = simplexml_load_file($file);
    $result = [];

    foreach ($import->ПакетПредложений->Предложение as $product) {

        $id = (string)$product['ИдентификаторТовара'];
        $price = (float)$product['Цена'];
        if ($course != 0) {
            $price = round($price / $course, $precision);
        }

        $query = new WP_Query(['post_type' => 'product', 'meta_key' => 'external_id', 'meta_value' => $id]);
        while ($query->have_posts()) {

            $query->the_post();

            $old = get_post_meta(get_the_ID(), '_price', 1);
            update_post_meta(get_the_ID(), '_regular_price', (float)$price);
            update_post_meta(get_the_ID(), '_price', (float)$price);

            $result[] = ['title' => get_the_title(), 'old' => $old, 'price' => $price, 'link' => get_post_permalink()];
        }
        wp_reset_postdata();
    }

    return $result;
}

/**
 * META BOX
 */


if (is_admin()) {

    add_action('add_meta_boxes', 'mcode_commerceml_extra_fields', 1);
    add_action('save_post', 'commerceml_extra_fields_save', 0);

}

function mcode_commerceml_extra_fields()
{
    add_meta_box('commerceml_extra_fields', __('Import from CommerceML', 'mcode-commerceml-import'), 'commerceml_extra_fields_function', 'product', 'normal', 'high');
}

/***
 * Add Post Meta
 * @param $post
 */
function commerceml_extra_fields_function($post)
{

    $external_id = get_post_meta($post->ID, 'external_id', 1);

    ?>

    <table style="width:100%;">
        <tr>
            <td width="200"><label><?= __('Product ID:', 'mcode-commerceml-import') ?></label></td>
            <td>
                <input type="text" name="extra_external_id" style="width:100%;" value="<?= $external_id ?>">
            </td>
        </tr>
    </table>

    <input type="hidden" name="extra_fields_nonce" value="<?php echo wp_create_nonce(__FILE__); ?>"/>

    <?php
}

/***
 * Save Post Meta
 * @param $post_id
 * @return bool
 */
function commerceml_extra_fields_save($post_id)
{

    if (!wp_verify_nonce($_POST['extra_fields_nonce'], __FILE__) || wp_is_post_autosave($post_id) || wp_is_post_revision($post_id)) {
        return false;
    }

    $_POST['extra_external_id'] = array_map('sanitize_text_field', $_POST['extra_mcode_persons']);
    $_POST['extra_mcode_country'] = array_map('sanitize_text_field', $_POST['extra_mcode_country']);

    if (empty($_POST['extra_external_id'])) {
        delete_post_meta($post_id, 'external_id');
    } else {
        update_post_meta($post_id, 'external_id', $_POST['extra_external_id']);
    }

    return $post_id;

}

/**
 * LANGUAGES
 */

function mcode_commerceml_load_plugin_textdomain()
{
    load_plugin_textdomain('mcode-commerceml-import', false, dirname(plugin_basename(__FILE__)) . '/languages/');
}

add_action('plugins_loaded', 'mcode_commerceml_load_plugin_textdomain');