<?php

namespace App\Controllers;

use App\Models\StyleModel;
use App\Models\WebsiteModel;

define('PLUGIN_VERSION', '1.0.0');

class SyncController extends BaseController
{
    public function index()
    {
        if (!session()->get('user_id')) {
            return redirect()->to(base_url('/'));
        }

        $styleModel = new StyleModel();
        $websiteModel = new WebsiteModel();

        $data = [
            'styles' => $styleModel->findAll(),
            'websites' => $websiteModel->findAll(),
            'plugin_version' => PLUGIN_VERSION,
        ];

        return view('sync/index', $data);
    }

    public function downloadPlugin()
    {
        if (!session()->get('user_id')) {
            return redirect()->to(base_url('/'));
        }

        $pluginPath = ROOTPATH . 'wordpress-plugin';
        
        $zipFile = WRITEPATH . 'divi-automation-' . PLUGIN_VERSION . '.zip';
        
        if (file_exists($zipFile)) {
            unlink($zipFile);
        }

        $zip = new \ZipArchive();
        if ($zip->open($zipFile, \ZipArchive::CREATE) === true) {
            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($pluginPath),
                \RecursiveIteratorIterator::SELF_FIRST
            );
            
            foreach ($files as $file) {
                if ($file->isDir()) {
                    continue;
                }
                
                $filePath = $file->getRealPath();
                $relativePath = str_replace($pluginPath . DIRECTORY_SEPARATOR, '', $filePath);
                
                $zip->addFile($filePath, 'divi-automation/' . $relativePath);
            }
            
            $zip->close();
        }

        if (!file_exists($zipFile)) {
            return redirect()->to(base_url('/sync'))->with('error', 'Erro ao criar ZIP');
        }

        return $this->response->download($zipFile, null)->setFileName('divi-automation-' . PLUGIN_VERSION . '.zip');
    }

    public function send()
    {
        if (!session()->get('user_id')) {
            return redirect()->to(base_url('/'));
        }

        $websiteId = $this->request->getPost('website_id');
        $styleIds = $this->request->getPost('style_ids');

        if (!$websiteId || empty($styleIds)) {
            return redirect()->to(base_url('/sync'))->with('error', 'Selecione um site e pelo menos um estilo.');
        }

        $websiteModel = new WebsiteModel();
        $website = $websiteModel->find($websiteId);

        if (!$website) {
            return redirect()->to(base_url('/sync'))->with('error', 'Site não encontrado.');
        }

        $styleModel = new StyleModel();
        
        $results = [];
        foreach ($styleIds as $styleId) {
            $style = $styleModel->find($styleId);
            if ($style) {
                $result = $this->sendToWordPress($website, $style);
                $results[] = [
                    'style' => $style,
                    'success' => $result['success'],
                    'message' => $result['message'],
                ];
            }
        }

        return view('sync/result', ['results' => $results, 'website' => $website]);
    }

    private function sendToWordPress($website, $style)
    {
        $mapping = $this->getCssToDiviMapping();
        
        $tipo = $style['tipo'];
        $moduleType = $this->getModuleType($tipo);
        
        $stylesJson = json_decode($style['styles'] ?? '{}', true);
        $elementInfoJson = json_decode($style['element_info'] ?? '{}', true);
        
        $normalStyles = [];
        $hoverStyles = [];
        
        if (is_array($stylesJson)) {
            $normalStyles = $stylesJson['normal'] ?? [];
            $hoverStyles = $stylesJson['hover'] ?? [];
        }
        
        $payload = [
            'module_type' => $moduleType,
            'preset_name' => $style['label'],
            'is_default' => true,
            'styles' => [
                'normal' => $normalStyles,
                'hover' => $hoverStyles,
            ],
        ];

        $url = rtrim($website['url'], '/') . '/wp-json/divi-automation/v1/update-preset';
        
        $credentials = base64_encode($website['username'] . ':' . $website['app_password']);
        
        $client = \Config\Services::curlrequest();
        
        try {
            $response = $client->setHeader('Authorization', 'Basic ' . $credentials)
                ->setHeader('Content-Type', 'application/json')
                ->post($url, json_encode($payload));

            if ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300) {
                return ['success' => true, 'message' => 'Preset enviado com sucesso!'];
            } else {
                return ['success' => false, 'message' => 'Erro: ' . $response->getStatusCode() . ' - ' . $response->getBody()];
            }
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Erro de conexão: ' . $e->getMessage()];
        }
    }

    private function getModuleType($tipo)
    {
        $map = [
            'button' => 'et_pb_button',
            'heading' => 'et_pb_text',
            'text' => 'et_pb_text',
            'paragraph' => 'et_pb_text',
            'menu_item' => 'et_pb_menu',
            'container' => 'et_pb_section',
            'section' => 'et_pb_section',
        ];
        
        return $map[$tipo] ?? 'et_pb_text';
    }

    private function getCssToDiviMapping()
    {
        return [
            'background-color' => 'background_color',
            'background-image' => 'background_image',
            'color' => 'text_color',
            'font-size' => 'font_size',
            'font-family' => 'font_family',
            'font-weight' => 'font_weight',
            'text-align' => 'text_align',
            'border-radius' => 'border_radius',
            'padding' => 'custom_padding',
            'margin' => 'custom_margin',
            'border-width' => 'border_width',
            'border-color' => 'border_color',
            'line-height' => 'line_height',
            'letter-spacing' => 'letter_spacing',
            'text-transform' => 'text_transform',
        ];
    }
}