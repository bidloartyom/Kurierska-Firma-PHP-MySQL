<?php
$conn = new mysqli("localhost", "root", "root", "kurierfirma");

if ($conn->connect_error) {
    die("Błąd połączenia z bazą danych: " . $conn->connect_error);
}

$statusy = [
    1 => "Oczekiwanie na odbiór",
    2 => "W drodze",
    3 => "Doręczona",
    4 => "Zwrot"
];

// Проверяем, отправлена ли форма
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['tracking_id'])) {
    $tracking_id = $_POST['tracking_id'];

    // Защита от SQL-инъекций
    $tracking_id = $conn->real_escape_string($tracking_id);

    // Ищем информацию о посылке
    $result = $conn->query("SELECT * FROM paczki WHERE paczka_id = '$tracking_id'");

    if ($result && $result->num_rows > 0) {
        $package = $result->fetch_assoc();
        $status_id = $package['status_id'];
        $status = $statusy[$status_id] ?? "Nieznany status";
    } else {
        $error = "Nie znaleźliśmy przesyłki o ID: $tracking_id.";
    }
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Śledzenie przesyłki</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f8f9fa;
            color: #333;
        }

        h1, h2, h3 {
            text-align: center;
            color: #0056b3;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            background-color: #fff;
            margin: 20px auto;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        th, td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: left;
        }

        th {
            background-color: #007bff;
            color: #fff;
            font-size: 16px;
        }

        td {
            font-size: 14px;
        }

        tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        tr:hover {
            background-color: #e9ecef;
        }

        .navbar {
            display: flex;
            justify-content: center;
            padding: 10px;
            background-color: #007bff;
        }

        .navbar a {
            margin: 0 10px;
            text-decoration: none;
            color: #fff;
            padding: 10px 20px;
            background-color: #0056b3;
            border-radius: 5px;
            font-weight: bold;
            font-size: 14px;
            transition: background-color 0.3s ease, transform 0.2s;
        }

        .navbar a:hover {
            background-color: #004099;
            transform: scale(1.05);
        }

        button, input[type="submit"] {
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 4px;
            padding: 10px 15px;
            cursor: pointer;
            font-size: 14px;
        }

        button:hover, input[type="submit"]:hover {
            background-color: #0056b3;
        }

        a {
            text-decoration: none;
        }

        .form-container {
            background-color: #fff;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            margin: 20px auto;
            padding: 20px;
            max-width: 700px;
            border-radius: 8px;
        }

        .form-container input, .form-container textarea, .form-container select {
            display: block;
            width: 100%;
            margin: 10px 0;
            padding: 10px;
            font-size: 14px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        textarea {
            resize: vertical;
            min-height: 80px;
        }

        button[type="submit"] {
            width: 100%;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <a href="index.php">Głowna strona</a>
    </div>
    <div class="form-container">
        <h1>Śledzenie przesyłki</h1>
        <form method="POST" action="tracking.php">
            <input type="text" name="tracking_id" placeholder="Wpisz ID przesyłki" required>
            <button type="submit">Sprawdź status</button>
        </form>
        <?php if (isset($error)): ?>
            <p class="error"><?php echo $error; ?></p>
        <?php elseif (isset($status)): ?>
            <p class="status">Status przesyłki: <strong><?php echo $status; ?></strong></p>
        <?php endif; ?>
    </div>
</body>
</html>