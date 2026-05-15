<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Subir XSD de Addenda</title>
</head>
<body>

<h2>📐 Generar Addenda desde XSD</h2>

<p>
    Sube el archivo XSD de la addenda.
    Se generará una estructura base que podrás completar después.
</p>

<form action="/addendas/backend/public/analyze_xsd.php"
      method="POST"
      enctype="multipart/form-data">

    <input type="file"
           name="xsd"
           accept=".xsd"
           required>

    <br><br>

    <button type="submit">
        Generar Addenda
    </button>
</form>

</body>
</html>