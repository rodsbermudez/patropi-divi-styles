<?php
/**
 * Plugin Name: Divi Automation
 * Plugin URI: https://github.com/rodsbermudez/divi-automation
 * Description: Plugin para criar e atualizar Global Presets do Divi 5 via REST API
 * Version: 2.0.0
 * Author: Divi Patropi
 * Author URI: https://github.com/rodsbermudez
 * License: GPL v2 or later
 * Text Domain: divi-automation
 */

if (!defined('ABSPATH')) {
    exit;
}

define('DIVI_AUTOMATION_VERSION', '2.0.0');

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
}

add_action('rest_api_init', 'divi_automation_register_routes');

/**
 * Gera ID único no formato do Divi 5
 */
function divi_automation_generate_id() {
    return 'i' . substr(md5(uniqid()), 0, 10);
}

/**
 * Converte CSS do Figma para estrutura Divi 5 (botão)
 */
function divi_automation_css_to_divi5_button($css_styles) {
    $divi_attrs = array();
    
    // BACKGROUND COLOR
    if (isset($css_styles['backgroundColor'])) {
        $divi_attrs['decoration']['background']['desktop']['value']['color'] = $css_styles['backgroundColor'];
    }
    
    // BORDER RADIUS
    if (isset($css_styles['borderRadius'])) {
        $divi_attrs['decoration']['border']['desktop']['value']['radius'] = array(
            'topLeft' => $css_styles['borderRadius'] . 'px',
            'topRight' => $css_styles['borderRadius'] . 'px',
            'bottomLeft' => $css_styles['borderRadius'] . 'px',
            'bottomRight' => $css_styles['borderRadius'] . 'px',
            'sync' => 'on'
        );
    }
    
    // BORDER WIDTH
    if (isset($css_styles['borderWidth'])) {
        $divi_attrs['decoration']['border']['desktop']['value']['styles']['all']['width'] = $css_styles['borderWidth'];
    }
    
    // BORDER COLOR
    if (isset($css_styles['borderColor'])) {
        $divi_attrs['decoration']['border']['desktop']['value']['styles']['all']['color'] = $css_styles['borderColor'];
    }
    
    // BORDER STYLE
    if (isset($css_styles['borderStyle'])) {
        $divi_attrs['decoration']['border']['desktop']['value']['styles']['all']['style'] = $css_styles['borderStyle'];
    }
    
    // PADDING (aplicado no module wrapper)
    $has_padding = false;
    $padding = array();
    if (isset($css_styles['paddingTop'])) {
        $padding['top'] = is_numeric($css_styles['paddingTop']) ? $css_styles['paddingTop'] . 'px' : $css_styles['paddingTop'];
        $has_padding = true;
    }
    if (isset($css_styles['paddingRight'])) {
        $padding['right'] = is_numeric($css_styles['paddingRight']) ? $css_styles['paddingRight'] . 'px' : $css_styles['paddingRight'];
        $has_padding = true;
    }
    if (isset($css_styles['paddingBottom'])) {
        $padding['bottom'] = is_numeric($css_styles['paddingBottom']) ? $css_styles['paddingBottom'] . 'px' : $css_styles['paddingBottom'];
        $has_padding = true;
    }
    if (isset($css_styles['paddingLeft'])) {
        $padding['left'] = is_numeric($css_styles['paddingLeft']) ? $css_styles['paddingLeft'] . 'px' : $css_styles['paddingLeft'];
        $has_padding = true;
    }
    
    if ($has_padding) {
        $padding['syncVertical'] = 'off';
        $padding['syncHorizontal'] = 'off';
        $divi_attrs['spacing']['desktop']['value']['padding'] = $padding;
    }
    
    return $divi_attrs;
}

/**
 * Cria preset no formato correto do Divi 5 (wp_options)
 */
function divi_automation_update_preset(WP_REST_Request $request) {
    $module_type = $request->get_param('module_type');
    $preset_name = $request->get_param('preset_name');
    $is_default = $request->get_param('is_default');
    $styles = $request->get_param('styles');

    if (!$module_type || !$preset_name) {
        return new WP_Error('missing_params', 'Module type and preset name are required', array('status' => 400));
    }
    
    // Mapeia tipo Divi 4 para Divi 5
    $divi5_module = str_replace('et_pb_', 'divi/', $module_type);
    
    $normal_styles = isset($styles['normal']) ? $styles['normal'] : array();
    $hover_styles = isset($styles['hover']) ? $styles['hover'] : array();
    
    // Gera ID único
    $preset_id = divi_automation_generate_id();
    $timestamp = (int)(microtime(true) * 1000);
    
    // Converte estilos CSS para formato Divi 5
    $normal_converted = divi_automation_css_to_divi5_button($normal_styles);
    
    // Monta estrutura completa do preset
    $preset_data = array(
        'id' => $preset_id,
        'name' => sanitize_text_field($preset_name),
        'moduleName' => $divi5_module,
        'version' => '5.3.3',
        'type' => 'module',
        'created' => $timestamp,
        'updated' => $timestamp,
        'attrs' => array(
            'button' => $normal_converted
        ),
        'styleAttrs' => array(
            'button' => $normal_converted
        ),
        'renderAttrs' => array()
    );
    
    // Obtém opção existente
    $option_name = 'et_divi_builder_global_presets_d5';
    $existing_data = get_option($option_name, array());
    
    // Inicializa estrutura se não existir
    if (empty($existing_data)) {
        $existing_data = array(
            'module' => array()
        );
    }
    
    // Inicializa módulo se não existir
    if (!isset($existing_data['module'])) {
        $existing_data['module'] = array();
    }
    
    if (!isset($existing_data['module'][$divi5_module])) {
        $existing_data['module'][$divi5_module] = array(
            'default' => $preset_id,
            'items' => array()
        );
    }
    
    // Remove preset existente com mesmo nome
    foreach ($existing_data['module'][$divi5_module]['items'] as $key => $item) {
        if ($item['name'] === $preset_name) {
            unset($existing_data['module'][$divi5_module]['items'][$key]);
        }
    }
    
    // Adiciona novo preset
    $existing_data['module'][$divi5_module]['items'][$preset_id] = $preset_data;
    
    // Se for default, atualiza
    if ($is_default) {
        $existing_data['module'][$divi5_module]['default'] = $preset_id;
    }
    
    // Reindex array
    $existing_data['module'][$divi5_module]['items'] = array_values($existing_data['module'][$divi5_module]['items']);
    $keys = array_keys($existing_data['module'][$divi5_module]['items']);
    $existing_data['module'][$divi5_module]['items'] = array_combine($keys, $existing_data['module'][$divi5_module]['items']);
    $existing_data['module'][$divi5_module]['default'] = $keys[0] ?? $preset_id;
    
    // Salva opção
    update_option($option_name, $existing_data);
    
    return array(
        'success' => true,
        'message' => 'Preset criado no formato Divi 5. Acesse Visual Builder > Preset Manager para visualizar.',
        'preset_id' => $preset_id,
        'module_type' => $divi5_module
    );
}

/**
 * Lista presets existentes
 */
function divi_automation_get_presets() {
    $option_name = 'et_divi_builder_global_presets_d5';
    $data = get_option($option_name, array());
    
    $presets = array();
    
    if (isset($data['module'])) {
        foreach ($data['module'] as $module => $items) {
            if (isset($items['items'])) {
                foreach ($items['items'] as $preset) {
                    $presets[] = array(
                        'id' => $preset['id'],
                        'name' => $preset['name'],
                        'module' => $preset['moduleName'],
                        'created' => $preset['created'],
                        'updated' => $preset['updated']
                    );
                }
            }
        }
    }
    
    return array(
        'success' => true,
        'presets' => $presets
    );
}

/**
 * Menu admin
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
    $option_name = 'et_divi_builder_global_presets_d5';
    $data = get_option($option_name, array());
    $presets = array();
    
    if (isset($data['module'])) {
        foreach ($data['module'] as $module => $items) {
            if (isset($items['items'])) {
                foreach ($items['items'] as $preset) {
                    $presets[] = $preset;
                }
            }
        }
    }
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
                        <th>Módulo</th>
                        <th>Criado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($presets as $preset): ?>
                    <tr>
                        <td><?php echo esc_html($preset['id']); ?></td>
                        <td><?php echo esc_html($preset['name']); ?></td>
                        <td><?php echo esc_html($preset['moduleName']); ?></td>
                        <td><?php echo date('d/m/Y H:i', $preset['created'] / 1000); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
        
        <h2>Como usar</h2>
        <ol>
            <li>Abra o Visual Builder do Divi 5</li>
            <li>Clique no ícone <strong>Preset Manager</strong> na sidebar esquerda</li>
            <li>Os presets aparecerão na lista</li>
            <li>Aplique o preset aos módulos correspondentes</li>
        </ol>
        
        <h2>API Endpoint</h2>
        <p><code><?php echo get_rest_url(null, 'divi-automation/v1/update-preset'); ?></code></p>
        
        <h3>Payload esperado:</h3>
        <pre>{
  "module_type": "et_pb_button",
  "preset_name": "Meu Botão",
  "is_default": true,
  "styles": {
    "normal": {
      "backgroundColor": "#d82929",
      "borderRadius": "10",
      "paddingTop": 16,
      "paddingRight": 16,
      "paddingBottom": 16,
      "paddingLeft": 16,
      "borderWidth": "2px",
      "borderColor": "#5c1616",
      "borderStyle": "solid"
    },
    "hover": null
  }
}</pre>
    </div>
    <?php
}