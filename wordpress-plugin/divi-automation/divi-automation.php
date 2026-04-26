<?php
/**
 * Plugin Name: Divi Automation
 * Plugin URI: https://github.com/rodsbermudez/divi-automation
 * Description: Plugin para criar e atualizar Global Presets do Divi 5 via REST API
 * Version: 1.2.0
 * Author: Divi Patropi
 * Author URI: https://github.com/rodsbermudez
 * License: GPL v2 or later
 * Text Domain: divi-automation
 */

if (!defined('ABSPATH')) {
    exit;
}

define('DIVI_AUTOMATION_VERSION', '1.2.0');

/**
 * Registra endpoints REST API
 */
function divi_automation_register_routes() {
    register_rest_route('divi-automation/v1', '/update-preset', array(
        'methods' => 'POST',
        'callback' => 'divi_automation_update_preset',
        'permission_callback' => '__return_true',
    ));
    
    register_rest_route('divi-automation/v1', '/get-presets', array(
        'methods' => 'GET',
        'callback' => 'divi_automation_get_presets',
        'permission_callback' => '__return_true',
    ));
    
    register_rest_route('divi-automation/v1', '/export-presets', array(
        'methods' => 'GET',
        'callback' => 'divi_automation_export_presets',
        'permission_callback' => '__return_true',
    ));
}

add_action('rest_api_init', 'divi_automation_register_routes');

/**
 * Mapeamento de propriedades CSS para Divi 5
 */
function divi_automation_get_mapping() {
    return array(
        'backgroundColor' => 'bg_color',
        'background_color' => 'bg_color',
        'color' => 'text_color',
        'text_color' => 'text_color',
        'fontSize' => 'font_size',
        'font_size' => 'font_size',
        'fontFamily' => 'font_family',
        'font_family' => 'font_family',
        'fontWeight' => 'font_weight',
        'font_weight' => 'font_weight',
        'borderRadius' => 'border_radius',
        'border_radius' => 'border_radius',
        'paddingTop' => 'padding_top',
        'paddingRight' => 'padding_right',
        'paddingBottom' => 'padding_bottom',
        'paddingLeft' => 'padding_left',
        'marginTop' => 'margin_top',
        'marginRight' => 'margin_right',
        'marginBottom' => 'margin_bottom',
        'marginLeft' => 'margin_left',
        'borderWidth' => 'border_width',
        'border_width' => 'border_width',
        'borderColor' => 'border_color',
        'border_color' => 'border_color',
        'borderStyle' => 'border_style',
        'border_style' => 'border_style',
        'boxShadow' => 'box_shadow',
        'box_shadow' => 'box_shadow',
        'opacity' => 'module_alignment',
        'width' => 'max_width',
        'height' => 'height',
        'gap' => 'gap',
    );
}

/**
 * Converte estilos para formato Divi 5
 */
function divi_automation_convert_styles($styles, $module_type) {
    $mapping = divi_automation_get_mapping();
    $converted = array();
    
    foreach ($styles as $key => $value) {
        $new_key = $mapping[$key] ?? $key;
        
        // Aplica prefix conforme o tipo de módulo
        if ($module_type === 'et_pb_button') {
            if (in_array($new_key, array('text_color', 'bg_color', 'font_size'))) {
                $converted['button_' . $new_key] = $value;
            } else {
                $converted[$new_key] = $value;
            }
        } else {
            $converted[$new_key] = $value;
        }
    }
    
    return $converted;
}

/**
 * Cria preset no banco (formato Divi 5)
 */
function divi_automation_update_preset(WP_REST_Request $request) {
    global $wpdb;
    
    $module_type = $request->get_param('module_type');
    $preset_name = $request->get_param('preset_name');
    $is_default = $request->get_param('is_default');
    $styles = $request->get_param('styles');

    if (!$module_type || !$preset_name) {
        return new WP_Error('missing_params', 'Module type and preset name are required', array('status' => 400));
    }
    
    // Remove prefix et_pb_ do tipo
    $divi_type = str_replace('et_pb_', '', $module_type);
    
    $normal_styles = isset($styles['normal']) ? $styles['normal'] : array();
    $hover_styles = isset($styles['hover']) ? $styles['hover'] : array();
    
    // Converter estilos para formato Divi 5
    $normal_converted = divi_automation_convert_styles($normal_styles, $module_type);
    $hover_converted = divi_automation_convert_styles($hover_styles, $module_type);
    
    // Criar preset como post do tipo divi_preset
    $post_data = array(
        'post_title' => sanitize_text_field($preset_name),
        'post_content' => json_encode(array(
            'type' => $divi_type,
            'default' => (bool) $is_default,
            'normal' => $normal_converted,
            'hover' => $hover_converted,
        )),
        'post_type' => 'divi_preset',
        'post_status' => 'publish',
    );
    
    // Verificar se já existe preset com mesmo nome e tipo
    $existing = $wpdb->get_row($wpdb->prepare(
        "SELECT ID FROM {$wpdb->posts} 
         WHERE post_type = 'divi_preset' 
         AND post_title = %s 
         AND post_content LIKE %s",
        $preset_name,
        '%' . $divi_type . '%'
    ));
    
    if ($existing) {
        $post_data['ID'] = $existing->ID;
        $result = wp_update_post($post_data);
    } else {
        $result = wp_insert_post($post_data);
    }
    
    if (is_wp_error($result)) {
        return new WP_Error('insert_failed', 'Failed to create preset: ' . $result->get_error_message(), array('status' => 500));
    }
    
    // Adicionar metadados
    update_post_meta($result, '_divi_preset_type', $divi_type);
    update_post_meta($result, '_divi_preset_default', $is_default ? 'on' : '');
    update_post_meta($result, '_divi_preset_styles_normal', json_encode($normal_converted));
    update_post_meta($result, '_divi_preset_styles_hover', json_encode($hover_converted));
    
    return array(
        'success' => true,
        'message' => 'Preset created successfully. Use Divi Visual Builder > Preset Manager to find it.',
        'preset_id' => $result,
    );
}

/**
 * Lista todos os presets
 */
function divi_automation_get_presets() {
    global $wpdb;
    
    $presets = $wpdb->get_results("
        SELECT ID, post_title as name, post_content as data 
        FROM {$wpdb->posts} 
        WHERE post_type = 'divi_preset' 
        AND post_status = 'publish'
        ORDER BY post_date DESC
    ", ARRAY_A);
    
    return array(
        'success' => true,
        'presets' => $presets,
    );
}

/**
 * Exporta presets no formato JSON do Divi 5
 */
function divi_automation_export_presets() {
    global $wpdb;
    
    $presets = $wpdb->get_results("
        SELECT post_title, post_content 
        FROM {$wpdb->posts} 
        WHERE post_type = 'divi_preset' 
        AND post_status = 'publish'
    ", ARRAY_A);
    
    // Formato de export do Divi 5
    $export_data = array(
        'version' => DIVI_AUTOMATION_VERSION,
        'presets' => array(),
    );
    
    foreach ($presets as $preset) {
        $data = json_decode($preset['post_content'], true);
        if ($data) {
            $export_data['presets'][] = array(
                'label' => $preset['post_title'],
                'type' => $data['type'] ?? 'button',
                'default' => $data['default'] ?? false,
                'styles' => array(
                    'normal' => $data['normal'] ?? array(),
                    'hover' => $data['hover'] ?? array(),
                ),
            );
        }
    }
    
    return $export_data;
}

/**
 * Menu de admin
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
    global $wpdb;
    $presets = $wpdb->get_results("
        SELECT ID, post_title, post_date 
        FROM {$wpdb->posts} 
        WHERE post_type = 'divi_preset' 
        ORDER BY post_date DESC
    ");
    ?>
    <div class="wrap">
        <h1>Divi Automation</h1>
        <p>Plugin para automatização de Global Presets do Divi 5.</p>
        <p><strong>Versão:</strong> <?php echo DIVI_AUTOMATION_VERSION; ?></p>
        
        <h2>Presets Criados</h2>
        <?php if (empty($presets)): ?>
            <p>Nenhum preset criado. Envie um preset via API.</p>
        <?php else: ?>
            <table class="widefat">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>Data</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($presets as $preset): ?>
                    <tr>
                        <td><?php echo $preset->ID; ?></td>
                        <td><?php echo esc_html($preset->post_title); ?></td>
                        <td><?php echo $preset->post_date; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
        
        <h2>Como usar</h2>
        <ol>
            <li>Abra o Visual Builder do Divi 5</li>
            <li>Clique no ícone <strong>Preset Manager</strong> na sidebar esquerda</li>
            <li>Os presets criados aparecerão na lista</li>
            <li>Clique em um preset para editá-lo ou aplicá-lo</li>
        </ol>
        
        <h2>API Endpoints</h2>
        <ul>
            <li><code><?php echo get_rest_url(null, 'divi-automation/v1/update-preset'); ?></code> - POST (criar preset)</li>
            <li><code><?php echo get_rest_url(null, 'divi-automation/v1/get-presets'); ?></code> - GET (listar presets)</li>
            <li><code><?php echo get_rest_url(null, 'divi-automation/v1/export-presets'); ?></code> - GET (export JSON)</li>
        </ul>
    </div>
    <?php
}