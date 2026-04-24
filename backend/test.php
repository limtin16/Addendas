$xml = file_get_contents('cfdi_prueba.xml');
$parser = new CFDIParserService();
$resultado = $parser->parse($xml);

var_dump($resultado->toArray());