<?php
$conn = new mysqli("localhost", "root", "root", "kurierfirma");

if ($conn->connect_error) {
    die("Błąd połączenia z bazą danych: " . $conn->connect_error);
}

// Курьерская авторизация
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['kurier_id'])) {
    $kurier_id = $conn->real_escape_string($_POST['kurier_id']);
    $result = $conn->query("SELECT * FROM kurierzy WHERE kurier_id = '$kurier_id'");

    if ($result && $result->num_rows > 0) {
        $kurier = $result->fetch_assoc();

        // Получение заказов курьера
        $dostawy = $conn->query("SELECT d.*, p.wymiary, p.waga, p.opis, p.status_id 
                                FROM dostawy d 
                                JOIN paczki p ON d.paczka_id = p.paczka_id 
                                WHERE d.kurier_id = '$kurier_id'");
    } else {
        $error = "Nie znaleziono kuriera z ID: $kurier_id.";
    }
}

// Обновление статуса доставки
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status']) && isset($_POST['dostawa_id'])) {
    $dostawa_id = $conn->real_escape_string($_POST['dostawa_id']);
    $new_status = $conn->real_escape_string($_POST['new_status']);

    $conn->query("UPDATE paczki 
                  SET status_id = '$new_status' 
                  WHERE paczka_id = (SELECT paczka_id FROM dostawy WHERE dostawa_id = '$dostawa_id')");

    header("Location: courier_panel.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel kuriera</title>
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
    <a href="index.php">Główna strona</a>
    </div>
    <div class="container">
        <h1>Panel kuriera</h1>
        <?php if (!isset($kurier)): ?>
            <form method="POST" action="courier_panel.php">
                <label for="kurier_id">Wpisz ID kuriera:</label>
                <input type="text" name="kurier_id" id="kurier_id" placeholder="Wpisz swoje ID" required>
                <button type="submit">Zaloguj się</button>
            </form>
            <?php if (isset($error)): ?>
                <p class="error"><?php echo $error; ?></p>
            <?php endif; ?>
        <?php else: ?>
            <h2>Witaj, <?php echo $kurier['imie_nazwisko']; ?>!</h2>
            <h3>Twoje dostawy:</h3>
            <?php if ($dostawy && $dostawy->num_rows > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID dostawy</th>
                            <th>Waga (kg)</th>
                            <th>Wymiary</th>
                            <th>Opis</th>
                            <th>Aktualny status</th>
                            <th>Akcje</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $dostawy->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row['dostawa_id']; ?></td>
                                <td><?php echo $row['waga']; ?></td>
                                <td><?php echo $row['wymiary']; ?></td>
                                <td><?php echo $row['opis']; ?></td>
                                <td><?php 
                                    switch ($row['status_id']) {
                                        case 1: echo "Oczekiwanie na odbiór"; break;
                                        case 2: echo "W drodze"; break;
                                        case 3: echo "Doręczona"; break;
                                        case 4: echo "Zwrot"; break;
                                        default: echo "Nieznany status"; break;
                                    }
                                ?></td>
                                <td>
                                    <form method="POST" action="courier_panel.php">
                                        <input type="hidden" name="dostawa_id" value="<?php echo $row['dostawa_id']; ?>">
                                        <select name="new_status" required>
                                            <option value="2">W drodze</option>
                                            <option value="3">Doręczona</option>
                                            <option value="4">Zwrot</option>
                                        </select>
                                        <button type="submit" name="update_status">Zaktualizuj</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>Nie masz przypisanych dostaw.</p>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</body>
</html>