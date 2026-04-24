<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Crear plantilla – Paso 2</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            padding: 40px;
        }
        .card {
            background: white;
            padding: 25px;
            max-width: 500px;
            margin: auto;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        label {
            font-weight: bold;
            margin-top: 15px;
            display: block;
        }
        input {
            width: 100%;
            padding: 8px;
            margin-top: 6px;
        }
        .hint {
            font-size: 13px;
            color: #666;
        }
        button {
            margin-top: 20px;
            padding: 10px;
            width: 100%;
            font-size: 16px;
        }
    </style>
</head>
<body>

<div class="card">
    <h2>Crear plantilla – Paso 2</h2>
    <p>Define la estructura principal solicitada por tu cliente.</p>

    <form method="post" action="/addendas/backend/public/save_template_step2.php">
        <!-- Id de la plantilla creada en el paso 1 -->
        <input type="hidden" name="template_id" value="<?php echo $_GET['template_id'] ?? ''; ?>">

        <label for="root">Nombre de la sección principal</label>
        <input type="text" id="root_name" name="root_name" required>
        <div class="hint">
            Normalmente lo especifica tu cliente.  
            Ejemplos: <b>Factura</b>, <b>AddendaDCG</b>, <b>Invoice</b>
        </div>

        <label for="prefix">Prefijo del formato (opcional)</label>
        <input type="text" id="prefix" name="prefix">
        <div class="hint">
            Ejemplos: <b>THY</b>, <b>mabe</b>.  
            Si tu cliente no indicó ninguno, puedes dejarlo vacío.
        </div>
		<label for="namespace">Namespace del formato</label>
		<input
			type="text"
			id="namespace"
			name="namespace"
			required
		>
		<div class="hint">
			Este valor normalmente lo proporciona el cliente o viene en el XML/XSD.<br>
			Ejemplo: <code>http://www.thyssenkrupp.com/schema</code>
		</div>

        <button type="submit">Continuar al Paso 3</button>
    </form>
</div>

</body>
</html>
</html>