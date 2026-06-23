<?php
$path="";
$count= (substr_count(substr(getcwd(),strrpos(getcwd(),'addenda'),100),'\\'));
if ($count==0){
    $count= (substr_count(substr(getcwd(),strrpos(getcwd(),'addendafacil.com'),100),'/'));
}
for ($i=0; $i<$count; $i++){
	$path.="../";
}
$path.="backend/config.php";
require_once $path;

session_start();

require_once dirname(__DIR__) . '/backend/config.php';
require_once BACKEND_ROOT . '/src/Services/TemplateService.php';

use App\Services\TemplateService;

$templateId = $_GET['template_id'] ?? $_POST['template_id'] ?? null;

if (!$templateId) {
    header("Location: " . BASE_URL . "/frontend/wizard_step1.php");
    exit;
}

$service = new TemplateService();
$template = $service->get($templateId);
$root = $template->structure['root'] ?? [];
$groups = $root['children'] ?? [];

if (!$template) {
    header("Location: " . BASE_URL . "/frontend/wizard_step1.php");
    exit;
}

// ✅ grupos existentes SIEMPRE vienen de DB
$groups = $template->structure['root']['children'] ?? [];

?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Crear addenda – Paso 4</title>
<link rel="stylesheet" href="<?= BASE_URL ?>/frontend/assets/styles.css">
<link rel="icon" href="<?= BASE_URL?>/frontend/assets/favicon.ico" type="image/x-icon">
</head>

<body>

<?php include __DIR__ . '/partials/sidebar.php'; ?>

<div class="main">

    <div class="container">
    <div class="form-centered">
            <div class="card" data-template-id="<?= htmlspecialchars($templateId) ?>">

                <h2>Crear addenda – Paso 4</h2>
                <p>Agrega grupos repetibles y sus campos.</p>

                <?php $currentGroupJson = $_GET['current_group'] ?? $_POST['current_group'] ?? null;
                $currentGroup = $currentGroupJson
                    ? json_decode($currentGroupJson, true)
                    : null; 
                    if ($currentGroup === null): ?>

                <h3>Nuevo grupo</h3>

                <form method="post" action="<?= BASE_URL ?>/backend/public/start_group_step4.php">
                    <input type="hidden" name="template_id" value="<?= htmlspecialchars($templateId) ?>">
                    <label>
                        Nombre del grupo
                        <span class="tooltip" data-tooltip="Es el nombre del grupo donde se organizan varios elementos similares, como una lista de conceptos o productos. Se ve como una etiqueta en forma de nodo debajo de la seccion principal">
                            ℹ️
                        </span>
                    </label>
                    <input type="text" name="group_name" required>
                    <label>
                        Nombre de cada elemento
                        <span class="tooltip" data-tooltip="Es el nombre que tendrá cada elemento dentro del grupo. Por ejemplo, si el grupo es Conceptos, cada elemento puede llamarse part o item.">
                            ℹ️
                        </span>
                    </label>
                    <input type="text" name="item_name" required>
                    <button type="submit">Crear grupo</button>
                </form>

                <?php else: ?>

                <h3>Grupo: <?= htmlspecialchars($currentGroup['name']) ?></h3>

                <p>Campos del grupo:</p>
                <ul>
                <?php foreach (($currentGroup['children'] ?? []) as $field): ?>
                    <li><?= htmlspecialchars($field['name']) ?> (<?= htmlspecialchars($field['representation']) ?>)</li>
                <?php endforeach; ?>
                </ul>

                <hr>

                <h4>Agregar campo al grupo</h4>

                <form method="post" action="<?= BASE_URL ?>/backend/public/add_field_to_group_step4.php">
                    <input type="hidden" name="template_id" value="<?= htmlspecialchars($templateId) ?>">
                    <input type="hidden" name="current_group" value='<?= htmlspecialchars(json_encode($currentGroup)) ?>'>
                    <label>
                        Nombre del campo
                        <span class="tooltip" data-tooltip="Es el nombre del dato que tendrá cada elemento del grupo (por ejemplo: precio, cantidad o descripción dentro de cada producto o concepto). Son datos que tu cliente requiere">
                            ℹ️
                        </span>
                    </label>
                    <input type="text" name="field_name" required>
                    <input type="hidden" name="representation" value="attribute">
                    <button type="submit">Agregar campo</button>
                </form>

                <hr>

                <form method="post" action="<?= BASE_URL ?>/backend/public/save_group_step4.php">
                    <input type="hidden" name="template_id" value="<?= htmlspecialchars($templateId) ?>">
                    <input type="hidden" name="current_group" value='<?= htmlspecialchars(json_encode($currentGroup)) ?>'>
                    <button type="submit">Guardar grupo</button>
                </form>

                <?php endif; ?>

                <hr>

                <form method="post" action="<?= BASE_URL ?>/backend/public/save_group_step4.php">
                    <input type="hidden" name="template_id" value="<?= htmlspecialchars($templateId) ?>">
                    <input type="hidden" name="redirect_done" value="1">
                    <input type="hidden" name="current_group" value='<?= htmlspecialchars(json_encode($currentGroup)) ?>'>
                    <button type="submit">Finalizar addenda ✅</button>
                </form>

                <hr>
                <h3>📦 Grupos ya guardados</h3>

                <ul>
                <?php foreach ($groups as $group): ?>
                    <?php if (($group['type'] ?? '') === 'group'): ?>
                        <li>
                            <?= htmlspecialchars($group['name']) ?>
                            (<?= htmlspecialchars($group['item_name'] ?? '') ?>)
                        </li>
                    <?php endif; ?>
                <?php endforeach; ?>
                </ul>
            </div> <!-- fin card formulario -->
    </div>
            <!-- 🔥 PREVIEW SEPARADO -->
            <div class="preview-full">

                <div class="card">
                    <h3>👁 Vista previa de la addenda</h3>

                    <div class="preview-box">
                        <pre id="xmlStructure" class="xml-preview">Cargando…</pre>
                    </div>
                </div>

            </div>
    
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {

    /* ==================================================
       PREVIEW (SIEMPRE ACTIVO)
       ================================================== */

    function decodeHtml(html) {
        var t = document.createElement('textarea');
        t.innerHTML = html;
        return t.value;
    }

    function updatePreview() {
        var structure = document.getElementById('xmlStructure');
        if (!structure) return;

        var container = document.querySelector('.card');
        if (!container) return;

        var templateId = container.dataset.templateId;
        if (!templateId) return;
        fetch(
            '<?= BASE_URL ?>/backend/public/preview_addenda_combined.php?template_id=' +
            encodeURIComponent(templateId) +
            '&_=' + Date.now()
        )
        .then(function (r) {
            return r.json();
        })
        .then(function (d) {
            if (d.structurePreview) {
             structure.textContent = decodeHtml(d.structurePreview);
            } else {
                structure.textContent = '⚠️ Sin estructura aún';
            }
        })
        .catch(function () {
            structure.textContent = 'Error cargando preview';
        });
    }

    // 🔥 SIEMPRE ejecuta el preview
    updatePreview();

    /* ==================================================
       FORMULARIO DE GRUPO (SOLO SI EXISTE)
       ================================================== */

    var groupForm = document.querySelector(
        'form[action*="add_field_to_group_step4"]'
    );

    if (!groupForm) {
        // No hay grupo activo: solo preview
        return;
    }

});
</script>

</body>
</html>