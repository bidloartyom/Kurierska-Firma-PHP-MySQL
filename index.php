<?php
$conn = new mysqli("localhost", "root", "root", "kurierfirma");

if ($conn->connect_error) {
    die("Błąd połączenia z bazą danych: " . $conn->connect_error);
}

if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM dostawy WHERE dostawa_id = $id") or die($conn->error);
    header("Location: index.php");
}

$result = $conn->query("SELECT * FROM dostawy") or die($conn->error);
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Strona administratora</title>
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
        <a href="update.php">Edytowanie Rekordów</a>
        <a href="aktualnepaczki.php">Ostatnie paczki</a>
        <a href="tracking.php">Tracking przesyłki</a>
        <a href="courier_panel.php">System kuriera</a>
        <a href="raport.php">raporty</a>
    </div>

    <div class="form-container">
    <h2>Dodaj nową dostawę i paczkę</h2>
    <form action="save.php" method="POST">
        <h3>Dane paczki</h3>
        <input type="number" name="dostawa_id" placeholder="ID dostawy" required>
        <input type="number" name="paczka_id" placeholder="ID paczki" required>
        <input type="number" name="klient_id" placeholder="ID klienta" required>
        <input type="number" name="adres_nadawcy_id" placeholder="ID adresu nadawcy" required>
        <input type="number" name="adres_odbiorcy_id" placeholder="ID adresu odbiorcy" required>
        <input type="number" step="0.01" name="waga" placeholder="Waga (kg)" required>
        <input type="text" name="wymiary" placeholder="Wymiary (np. 30x20x15)" required>
        <textarea name="opis" placeholder="Opis paczki"></textarea>
        
        <label for="status_id"></label>
        <select name="status_id" id="status_id" required>
        <option value="">-- Wybierz status --</option>
        <option value="1">oczekuję na odbior</option>
        <option value="2">w drodze</option>
        <option value="3">doręczona</option>
        <option value="4">zwrot</option>
        <br>
        <h3>Dane dostawy</h3>
        <input type="number" name="kurier_id" placeholder="ID kuriera" required>
        <input type="date" name="czas_odbioru" required>
        <input type="date" name="czas_dostawy" required>
        <textarea name="uwagi" placeholder="Uwagi"></textarea>

        <button type="submit" name="save">Zapisz</button>
    </form>
</div>
    <h1>Lista dostaw</h1>
    <table>
        <thead>
            <tr>
                <th>ID dostawy</th>
                <th>ID paczki</th>
                <th>ID kuriera</th>
                <th>Czas odbioru</th>
                <th>Czas dostawy</th>
                <th>Uwagi</th>
                <th>Akcje</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo $row['dostawa_id']; ?></td>
                <td><?php echo $row['paczka_id']; ?></td>
                <td><?php echo $row['kurier_id']; ?></td>
                <td><?php echo $row['czas_odbioru']; ?></td>
                <td><?php echo $row['czas_dostawy']; ?></td>
                <td><?php echo $row['uwagi']; ?></td>
                <td>
                    <a href="update.php?edit=<?php echo $row['dostawa_id']; ?>">Edytuj</a>
                    <a href="index.php?delete=<?php echo $row['dostawa_id']; ?>" onclick="return confirm('Czy na pewno chcesz usunąć ten rekord?')">Usuń</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</body>
</html>