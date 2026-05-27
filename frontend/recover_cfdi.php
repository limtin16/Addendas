<!DOCTYPE html>
<html>
<head>
    <title>Recuperar CFDI</title>

    <style>
        body {
            font-family: Arial;
            background: #f4f6f9;
            display: flex;
            justify-content: center;
            padding-top: 80px;
        }

        .box {
            background: white;
            padding: 30px;
            border-radius: 10px;
            width: 350px;
            text-align: center;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        input {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border-radius: 6px;
            border: 1px solid #ccc;
        }

        button {
            padding: 10px;
            width: 100%;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }

        button:hover {
            background: #0056b3;
        }
    </style>
</head>

<body>

<div class="box">

    <h2>Recuperar CFDI</h2>

    <form method="GET" action="/backend/public/recover_cfdi.php">
        <input type="text" name="token" placeholder="Ingresa tu ID" required>
        <button>Descargar CFDI</button>
    </form>

</div>

</body>
</html>