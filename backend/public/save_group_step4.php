<?php
session_start();

require_once dirname(__DIR__) . '/config.php';

// ✅ CARGAR CLASES NECESARIAS
require_once BACKEND_ROOT . '/src/DTO/Template.php';
require_once BACKEND_ROOT . '/src/Services/TemplateService.php';

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

// ✅ Volver al wizard step 4 (para agregar otro grupo)
header('Location: /addendas/frontend/wizard_step4.php?template_id=' . urlencode($templateId));
exit;
