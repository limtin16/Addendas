<?php

namespace App\Services;

require_once dirname(__DIR__) . '/DTO/Template.php';

use App\DTO\Template;
use RuntimeException;


class TemplateService
{
    /**
     * Crea y guarda una nueva plantilla
     */
public function save(string $name, string $location, array $structure, ?string $id = null): Template
{
    $template = new Template();

    // ✅ SI viene ID, se reutiliza
    if ($id) {
        $template->id = $id;
    } else {
        $template->id = $this->generateId();
    }

    $template->name = $name;
    $template->location = $location;
    $template->structure = $this->normalizeStructure($structure);

    $this->persist($template);

    return $template;
}

/**
 * Crea una nueva plantilla a partir de una Addenda analizada
 *
 * @param array $structure Estructura abstracta de la Addenda
 * @return string template_id generado
 */
public function createFromAddenda(array $structure): string
{
    // 1. Definir nombre base del template
    $templateName = $structure['name'];

    // 2. Generar ID (opcional, save() también puede hacerlo)
    $templateId = $this->generateId();

    // 3. Ruta donde se guardará el template
    $location = BACKEND_ROOT . "/src/storage/templates/{$templateId}.json";

    if (!is_dir(dirname($location))) {
        mkdir(dirname($location), 0777, true);
    }

    // 4. Construir estructura compatible con el wizard
    $templateStructure = [
        'root' => [
            'type' => 'root',
            'name' => $structure['name'],
            'children' => []
        ]
    ];

    foreach ($structure['children'] as $child) {
        $templateStructure['root']['children'][] =
            $this->normalizeNode($child);
    }

    // 5. Delegar guardado al servicio existente
    $template = $this->save(
        $templateName,   // name
        $location,       // location (string)
        $templateStructure, // structure (array)
        $templateId      // id
    );

    return $template->id;
}


/**
 * Normaliza nodos/fields detectados para el wizard
 */
private function normalizeNode(array $node): array
{
    if ($node['type'] === 'field') {
        return [
            'type' => 'field',
            'name' => $node['name'],
            'representation' => 'node',
            'origin' => [
                'type' => 'fixed',
                'value' => null
            ]
        ];
    }

    // Nodo con hijos (grupo)
    $children = [];
    foreach ($node['children'] as $child) {
        $children[] = $this->normalizeNode($child);
    }

    return [
        'type' => 'group',
        'name' => $node['name'],
        'repeatable' => true,
        'children' => $children
    ];
}


	public function update(string $id, array $structure): Template
	{
		$template = $this->get($id);

		if (!$template) {
			throw new RuntimeException('Template no encontrado');
		}

		$template->structure = $this->normalizeStructure($structure);
		$this->persist($template);

		return $template;
	}
    /**
     * Obtiene una plantilla por ID
     */
    public function get(string $id): ?Template
    {
        $path = $this->getPath($id);

        if (!file_exists($path)) {
            return null;
        }

        $data = json_decode(file_get_contents($path), true);

        if (!is_array($data)) {
            throw new RuntimeException("Template corrupto: {$id}");
        }

        $template = new Template();
        $template->id = $data['id'] ?? $id;
        $template->name = $data['name'] ?? '';
        $template->location = $data['location'] ?? '';
        $template->structure = $this->normalizeStructure($data['structure'] ?? []);

        return $template;
    }

    /**
     * GUARDA el template (UN SOLO CAMINO)
     */
    private function persist(Template $template): void
    {
        file_put_contents(
            $this->getPath($template->id),
            json_encode(
                $template->toArray(),
                JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
            )
        );
    }

    /**
     * Verifica y completa estructura mínima obligatoria
     */
private function normalizeStructure(array $structure): array
{
    // ✅ Si no existe root, lo creamos
    if (!isset($structure['root']) || !is_array($structure['root'])) {
        $structure['root'] = [
            'name' => null,
            'prefix' => null,
            'namespace' => null,
            'children' => [],
        ];
        return ['root' => $structure['root']];
    }

    // ✅ Preservar valores existentes
    $root = $structure['root'];

    $normalizedRoot = [
        'name' => $root['name'] ?? null,
        'prefix' => $root['prefix'] ?? null,
        'namespace' => $root['namespace'] ?? null,
        'children' => [],
    ];

    // ✅ Preservar children existentes
    if (isset($root['children']) && is_array($root['children'])) {
        $normalizedRoot['children'] = $root['children'];
    }

    // ✅ RETORNAR SOLO root (nada más)
    return [
        'root' => $normalizedRoot
    ];
}

    /**
     * Genera ID único
     */
    private function generateId(): string
    {
        return 'tpl_' . uniqid('', true);
    }

    /**
     * Ruta del archivo JSON
     */
    private function getPath(string $id): string
    {
        return TEMPLATE_STORAGE_PATH . '/' . $id . '.json';
    }
}