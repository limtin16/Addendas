<?php
session_start();

require_once dirname(__DIR__) . '/config.php';

/* =======================================================
   1. Validar archivo
   ======================================================= */

if (
    !isset($_FILES['addenda_xml']) ||
    $_FILES['addenda_xml']['error'] !== UPLOAD_ERR_OK
) {
    die('❌ Error al subir el archivo XML.');
}

$xmlContent = file_get_contents($_FILES['addenda_xml']['tmp_name']);
if (!$xmlContent || trim($xmlContent) === '') {
    die('❌ El archivo XML está vacío.');
}

/* =======================================================
   2. Cargar XML original (CFDI o Addenda)
   ======================================================= */

libxml_use_internal_errors(true);
$originalDom = new DOMDocument('1.0', 'UTF-8');

if (!$originalDom->loadXML($xmlContent)) {
    die('❌ El archivo no es un XML válido.');
}

/* =======================================================
   3. EXTRAER SOLO <Addenda> y ELIMINAR TODO LO DEMÁS
   ======================================================= */

// XPath sobre el XML original
$xpath = new DOMXPath($originalDom);

/*
 * Buscar Addenda sin importar:
 * - namespace
 * - prefijo
 * - versión CFDI
 */
$addendaNode = $xpath->query('//*[local-name()="Addenda"]')->item(0);

if (!$addendaNode) {
    die('❌ El XML no contiene una Addenda.');
}

// (Opcional) Guardar CFDI original para autofill futuro
$_SESSION['original_cfdi_xml'] = $originalDom->saveXML();

/*
 * Crear un NUEVO DOM que contenga ÚNICAMENTE la Addenda
 * En este punto, CFDI, Timbre, Emisor, Receptor, etc.
 * DEJAN DE EXISTIR
 */
$addendaDom = new DOMDocument('1.0', 'UTF-8');
/*
 |========================================================
 | Normalización GENÉRICA de namespaces en Addenda
 |========================================================
 */
$xpath = new DOMXPath($addendaDom);

/** 1️⃣ Recolectar uso de prefijos reales */
$prefixUsage = [];

foreach ($xpath->query('//*[namespace-uri()]') as $node) {
    if (!$node instanceof DOMElement) continue;

    $prefix = $node->prefix;
    if ($prefix) {
        $prefixUsage[$prefix][] = $node;
    }
}

/** 2️⃣ Recolectar declaraciones xmlns existentes */
$declaredNamespaces = [];

foreach ($xpath->query('//@xmlns:*') as $attr) {
    if ($attr instanceof DOMAttr) {
        $declaredNamespaces[$attr->prefix === 'xmlns'
            ? $attr->localName
            : null] = $attr->nodeValue;
    }
}

/** 3️⃣ Limpiar declaraciones duplicadas y mal ubicadas */
foreach ($xpath->query('/descendant-or-self::*') as $node) {
    if (!$node instanceof DOMElement) continue;

    foreach (iterator_to_array($node->attributes ?? []) as $attr) {
        if ($attr->prefix === 'xmlns') {
            // ❌ Eliminamos TODAS primero (se reinsertan bien después)
            $node->removeAttributeNode($attr);
        }
    }
}

/** 4️⃣ Reinsertar xmlns:cfdi solo en <cfdi:Addenda> */
$addendaRoot = $addendaDom->documentElement;
if (isset($declaredNamespaces['cfdi'])) {
    $addendaRoot->setAttribute('xmlns:cfdi', $declaredNamespaces['cfdi']);
}

/** 5️⃣ Reinsertar namespaces de proveedor en el primer nodo que los usa */
foreach ($prefixUsage as $prefix => $nodes) {
    if ($prefix === 'cfdi') continue;

    $namespaceUri = $declaredNamespaces[$prefix] ?? null;
    if (!$namespaceUri) continue;

    // Primer nodo real que lo usa
    $firstNode = $nodes[0];
    if (!$firstNode->hasAttribute("xmlns:$prefix")) {
        $firstNode->setAttribute("xmlns:$prefix", $namespaceUri);
    }
}
$cleanAddenda = $addendaDom->importNode($addendaNode, true);
$addendaDom->appendChild($cleanAddenda);
// Guardar la Addenda EXACTA como texto (sin tocarla)
$_SESSION['addenda_instance']['addenda_xml_template'] =
    $originalDom->saveXML($addendaNode);

// A partir de aquí SOLO se trabaja con $addendaDom
$addendaRoot = $addendaDom->documentElement;
$namespaces = [];

if ($addendaRoot->hasAttributes()) {
    foreach ($addendaRoot->attributes as $attr) {
        // Capturar xmlns y xmlns:prefijo
        if (strpos($attr->nodeName, 'xmlns') === 0) {
            $namespaces[$attr->nodeName] = $attr->nodeValue;
        }
    }
}

/* =======================================================
   4. Parsear la Addenda LIMPIA a estructura abstracta
   ======================================================= */

function parseAddendaNode(DOMElement $element): array
{
    $node = [
        'type' => 'node',
        'name' => $element->nodeName,
        'children' => []
    ];

    /* =========================
       1. Capturar namespaces
       ========================= */
    $namespaces = [];
    if ($element->hasAttributes()) {
        foreach ($element->attributes as $attr) {
            if (strpos($attr->nodeName, 'xmlns') === 0) {
                $namespaces[$attr->nodeName] = $attr->nodeValue;
            }
        }
    }
    if ($namespaces) {
        $node['namespaces'] = $namespaces;
    }

    /* =========================
       2. Atributos normales
       ========================= */
    if ($element->hasAttributes()) {
        foreach ($element->attributes as $attr) {
            // ⚠️ Omitir xmlns:*
            if (strpos($attr->nodeName, 'xmlns') !== 0) {
                $node['children'][] = [
                    'type'  => 'field',
                    'name'  => '@' . $attr->name,
                    'value' => $attr->value
                ];
            }
        }
    }

    /* =========================
       3. Nodos hijos
       ========================= */
    foreach ($element->childNodes as $child) {
        if ($child instanceof DOMElement) {
            $node['children'][] = parseAddendaNode($child);
        }
    }

    return $node;
}  

/*
 * IMPORTANTE:
 * No queremos que el formulario muestre el wrapper <cfdi:Addenda>
 * sino sus hijos reales (THY:Factura, etc.)
 */
$structure = parseAddendaNode($addendaRoot);

/* =======================================================
   5. Guardar estructura en sesión (INSTANCIACIÓN)
   ======================================================= */

$_SESSION['addenda_instance']['structure']   = $structure;
$_SESSION['addenda_instance']['origin']      = 'upload';
$_SESSION['addenda_instance']['uploaded_at'] = date('c');

/* =======================================================
   6. Redirigir al formulario de instanciación
   ======================================================= */

header('Location: /addendas/frontend/render_instance_form.php');
exit;