<?php
/*
Plugin Name: Botowski for Woocommerce
Version: 1.1.1
Description: A plugin to rewrite Woocommerce product titles and descriptions using Botowski API.
Author URI: https://www.botowski.com/
Author: Botowski
Tags: woocommerce, chatbot, AI, Botowski
Requires at least: 5.0
Tested up to: 6.2
Stable tag: 1.1.1
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html
*/

add_action('admin_menu', 'botowski_add_menu_page');
add_action('admin_init', 'botowski_register_settings');
add_action('admin_enqueue_scripts', 'botowski_enqueue_scripts');
add_action('wp_ajax_botowski_rewrite', 'botowski_rewrite_product_short_desc');
add_action('woocommerce_product_short_description', 'botowski_add_rewrite_button', 15);
add_action('wp_ajax_botowski_rewrite_title', 'botowski_rewrite_product_title_only');
add_action('wp_ajax_botowski_rewrite_main_desc', 'botowski_rewrite_product_main_desc');



function botowski_add_menu_page()
{
    add_options_page('Botowski Settings', 'Botowski', 'manage_options', 'botowski-settings', 'botowski_render_settings_page');
}

function botowski_register_settings()
{
    register_setting('botowski_options', 'botowski_api_token', 'sanitize_text_field');
}

function botowski_enqueue_scripts()
{
    wp_enqueue_script('jquery-ui-dialog');
    wp_enqueue_style('wp-jquery-ui-dialog');
    wp_enqueue_style('botowski-style', plugin_dir_url(__FILE__) . 'botowski-style.css');


    wp_enqueue_script('botowski-script', plugin_dir_url(__FILE__) . 'botowski-script.js', array('jquery'), '1.0', true);

    wp_localize_script('botowski-script', 'botowski_params', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('botowski-nonce'),
    ));
}


function botowski_add_rewrite_button()
{
    echo '<div class="options_group">';
    echo '<button type="button" class="button botowski-rewrite-btn" id="botowski_rewrite">Rewrite Short Description</button>';
    echo '</div>';
}


function botowski_rewrite_product_title_only()
{
    check_ajax_referer('botowski-nonce', 'nonce');

    $title = sanitize_text_field($_POST['title']);
	$product_id = absint($_POST['product_id']);
    $api_token = get_option('botowski_api_token');

    // Call the Botowski API to rewrite the title
    $botowski_response = wp_remote_get('https://www.botowski.com/api_1/prodname', array(
        'headers' => array(
            'Authorization' => 'Bearer ' . $api_token,
        ),
        'body' => array(
            'prod_name' => $title,
            'tone' => 'creative',
        ),
        'timeout' => 15, // Increase the timeout to 15 seconds
    ));

    if (is_wp_error($botowski_response)) {
		echo 'Error: ' . esc_html($botowski_response->get_error_message());

    } else {
        $response_body = wp_remote_retrieve_body($botowski_response);
        $response_data = json_decode($response_body, true);

        if (json_last_error() === JSON_ERROR_NONE) {
            if (isset($response_data['error'])) {
                echo 'Error: ' . esc_html($response_data['error']);
            } else {
                $new_title = $response_data['description_1'];
                // Remove leading white space in the new title
                $new_title = ltrim($new_title);
				echo esc_html($new_title);
            }
        } else {
    echo esc_html($response_body);
}
    }

    wp_die();
}



function botowski_rewrite_product_short_desc()
{
    check_ajax_referer('botowski-nonce', 'nonce');

    $title = sanitize_text_field($_POST['title']);
	$product_id = absint($_POST['product_id']);
    $api_token = get_option('botowski_api_token');

    $product = wc_get_product($product_id);
    $short_description = $product->get_short_description();

    $description_to_rewrite = !empty($short_description) ? $short_description : $title;

    // Call the Botowski API to rewrite the title
    $botowski_response = wp_remote_get('https://www.botowski.com/api_1', array(
        'headers' => array(
            'Authorization' => 'Bearer ' . $api_token,
        ),
        'body' => array(
            'prod_name' => $title,
            'prod_description' => $description_to_rewrite,
            'tone' => 'full',
        ),
        'timeout' => 15, // Increase the timeout to 15 seconds
    ));

    if (is_wp_error($botowski_response)) {
		echo 'Error: ' . esc_html($botowski_response->get_error_message());
    } else {
        $response_body = wp_remote_retrieve_body($botowski_response);
        $response_data = json_decode($response_body, true);

        if (json_last_error() === JSON_ERROR_NONE) {
            if (isset($response_data['error'])) {
				echo 'Error: ' . esc_html($response_data['error']);
            } else {
                $new_title = $response_data['description_1'];
				echo esc_html($new_title);
            }
        } else {
            echo esc_html($response_body);
        }
    }

    wp_die();
}



function botowski_rewrite_product_main_desc()
{
    check_ajax_referer('botowski-nonce', 'nonce');

    $title = sanitize_text_field($_POST['title']);
    $product_id = absint($_POST['product_id']);
    $api_token = get_option('botowski_api_token');

    $product = wc_get_product($product_id);
    $main_description = $product->get_description();

    $description_to_rewrite = !empty($main_description) ? $main_description : $title;

    // Call the Botowski API to rewrite the main description
    $botowski_response = wp_remote_get('https://www.botowski.com/api_1', array(
        'headers' => array(
            'Authorization' => 'Bearer ' . $api_token,
        ),
        'body' => array(
            'prod_name' => $title,
            'prod_description' => $description_to_rewrite,
            'tone' => 'professional',
        ),
        'timeout' => 15, // Increase the timeout to 15 seconds
    ));

    if (is_wp_error($botowski_response)) {
        echo 'Error: ' . esc_html($botowski_response->get_error_message());
    } else {
        $response_body = wp_remote_retrieve_body($botowski_response);
        $response_data = json_decode($response_body, true);

        if (json_last_error() === JSON_ERROR_NONE) {
            if (isset($response_data['error'])) {
                echo 'Error: ' . esc_html($response_data['error']);
            } else {
                $new_main_desc = $response_data['description_1'];
                echo esc_html($new_main_desc);
            }
        } else {
            echo esc_html($response_body);
        }
    }

    wp_die();
}



function botowski_render_settings_page()
{
?>
    <div class="wrap">
        <h1><?php esc_html_e('Botowski Settings', 'botowski'); ?></h1>

        <form method="post" action="options.php">
            <?php
            settings_fields('botowski_options');
            do_settings_sections('botowski_options');
            ?>

            <table class="form-table">
                <tr>
                    <th scope="row"><?php esc_html_e('Botowski API Token', 'botowski'); ?></th>
                    <td>
                        <input type="text" name="botowski_api_token" value="<?php echo esc_attr(get_option('botowski_api_token')); ?>" />
                    </td>
                </tr>
            </table>

            <?php submit_button(); ?>
        </form>
    </div>
<?php
}