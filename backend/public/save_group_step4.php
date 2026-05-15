<?php
session_start();

require_once dirname(__DIR__) . '/config.php';

// ✅ CARGAR CLASES NECESARIAS
require_once BACKEND_ROOT . '/src/DTO/Template.php';
require_once BACKEND_ROOT . '/src/Services/TemplateService.php';

require_once BACKEND_ROOT . '/src/Services/AddendaXmlBuilder.php';

use App\Services\AddendaXmlBuilder;

use App\Services\TemplateService;

// ✅ Validar template_id
$templateId = $_POST['template_id'] ?? null;
if (!$templateId) {
    die('template_id no proporcionado');
}

// ✅ Validar grupo en sesión
if (
    !isset($_SESSION['current_group']) ||
    empty($_SESSION['current_group'])
) {
    die('No hay grupo activo para guardar');
}

// ✅ Obtener template
$service = new TemplateService();
$template = $service->get($templateId);

if (!$template) {
    die('Template no encontrado');
}

// ✅ Agregar grupo al root
$template->structure['root']['children'][] = $_SESSION['current_group'];

// ✅ Guardar template actualizado
$service->update($templateId, $template->structure);

// ✅ Limpiar grupo actual
$_SESSION['current_group'] = null;

require_once BACKEND_ROOT . '/src/Services/AddendaXmlBuilder.php';

use App\Services\AddendaXmlBuilder;

// ===============================
// SI FINALIZA → preparar instance
// ===============================
if (!empty($_POST['redirect_done'])) {

    $builder = new AddendaXmlBuilder();

    // Generar XML base
    $addendaXmlTemplate = $builder->build($template->structure);

    // Convertir a instance (copia de lógica previa)
    function convertNode($node) {
        if (($node['type'] ?? '') === 'field') {
            return [
                'type' => 'field',
                'name' => $node['name']
            ];
        }

        if (($node['type'] ?? '') === 'group') {
            return [
                'type' => 'node',
                'name' => $node['name'],
                'children' => array_map('convertNode', $node['children'] ?? [])
            ];
        }

        return null;
    }

    $root = $template->structure['root'] ?? [];

    $instanceStructure = [
        'type' => 'node',
        'name' => $root['name'] ?? 'Addenda',
        'children' => array_values(array_filter(
            array_map('convertNode', $root['children'] ?? [])
        ))
    ];

    // Guardar en sesión
    $_SESSION['addenda_instance'] = [
        'structure' => $instanceStructure,
        'addenda_xml_template' => $addendaXmlTemplate
    ];

    // ✅ REDIRECCIÓN DIRECTA (SIN wizard_done)
    header('Location: /addendas/frontend/render_instance_form.php');
    exit;
}

