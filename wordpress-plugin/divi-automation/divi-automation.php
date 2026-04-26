<?php
/**
 * Plugin Name: Divi Automation
 * Plugin URI: https://github.com/rodsbermudez/divi-automation
 * Description: Plugin para criar e atualizar Global Presets do Divi 5 via REST API
 * Version: 1.0.0
 * Author: Divi Patropi
 * Author URI: https://github.com/rodsbermudez
 * License: GPL v2 or later
 * Text Domain: divi-automation
 */

if (!defined('ABSPATH')) {
    exit;
}

define('DIVI_AUTOMATION_VERSION', '1.0.0');

/**
 * Registra o endpoint REST API
 */
function divi_automation_register_routes() {
    register_rest_route('divi-automation/v1', '/update-preset', array(
        'methods' => 'POST',
        'callback' => 'divi_automation_update_preset',
        'permission_callback' => '__return_true',
    ));
}

add_action('rest_api_init', 'divi_automation_register_routes');

/**
 * Callback para criar/atualizar preset
 */
function divi_automation_update_preset(WP_REST_Request $request) {
    $module_type = $request->get_param('module_type');
    $preset_name = $request->get_param('preset_name');
    $is_default = $request->get_param('is_default');
    $styles = $request->get_param('styles');

    if (!$module_type || !$preset_name) {
        return new WP_Error('missing_params', 'Module type and preset name are required', array('status' => 400));
    }

    global $wpdb;
    
    $table_name = $wpdb->prefix . 'et_pb_presets';
    
    $normal_styles = isset($styles['normal']) ? json_encode($styles['normal']) : '{}';
    $hover_styles = isset($styles['hover']) ? json_encode($styles['hover']) : '{}';
    
    $preset_data = array(
        'preset_name' => sanitize_text_field($preset_name),
        'type' => sanitize_text_field($module_type),
        'primary' => $normal_styles,
        'secondary' => $hover_styles,
        'default' => $is_default ? 1 : 0,
        'created_at' => current_time('mysql'),
    );
    
    $format = array('%s', '%s', '%s', '%s', '%d', '%s');
    
    $result = $wpdb->insert($table_name, $preset_data, $format);
    
    if ($result === false) {
        return new WP_Error('insert_failed', 'Failed to create preset: ' . $wpdb->last_error, array('status' => 500));
    }
    
    divi_automation_clear_cache();
    
    return array(
        'success' => true,
        'message' => 'Preset created successfully',
        'preset_id' => $wpdb->insert_id,
    );
}

/**
 * Limpa o cache do Divi
 */
function divi_automation_clear_cache() {
    $upload_dir = wp_upload_dir();
    $css_cache_path = $upload_dir['basedir'] . '/divi/';
    
    if (file_exists($css_cache_path)) {
        $files = glob($css_cache_path . '*.css');
        if (is_array($files)) {
            foreach ($files as $file) {
                if (is_file($file)) {
                    @unlink($file);
                }
            }
        }
    }
    
    if (function_exists('et_core_clear_global')) {
        et_core_clear_global('et_pb_layouts');
    }
}

/**
 * Adiciona link de settings no admin
 */
function divi_automation_admin_menu() {
    add_options_page(
        'Divi Automation',
        'Divi Automation',
        'manage_options',
        'divi-automation',
        'divi_automation_settings_page'
    );
}

add_action('admin_menu', 'divi_automation_admin_menu');

/**
 * Página de settings
 */
function divi_automation_settings_page() {
    ?>
    <div class="wrap">
        <h1>Divi Automation</h1>
        <p>Plugin para automatização de Global Presets do Divi 5.</p>
        <p><strong>Versão:</strong> <?php echo DIVI_AUTOMATION_VERSION; ?></p>
        
        <h2>Como usar</h2>
        <p>Envie uma requisição POST para:</p>
        <code><?php echo get_rest_url(null, 'divi-automation/v1/update-preset'); ?></code>
        
        <h3>Payload:</h3>
        <pre>{
  "module_type": "et_pb_button",
  "preset_name": "Meu Botão",
  "is_default": true,
  "styles": {
    "normal": { "backgroundColor": "#31484f", ... },
    "hover": null
  }
}</pre>
    </div>
    <?php
}