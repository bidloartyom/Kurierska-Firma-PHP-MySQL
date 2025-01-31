<?php
$conn = new mysqli("localhost", "root", "root", "kurierfirma");

if ($conn->connect_error) {
    die("Błąd połączenia z bazą danych: " . $conn->connect_error);
}

if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $result = $conn->query("SELECT * FROM dostawy WHERE dostawa_id = $id") or die($conn->error);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
    }
}

if (isset($_POST['update'])) {
    $dostawa_id = intval($_POST['id']); 
    $paczka_id = intval($_POST['paczka_id']); 
    $kurier_id = intval($_POST['kurier_id']); 
    $czas_odbioru = $_POST['czas_odbioru']; 
    $czas_dostawy = $_POST['czas_dostawy']; 
    $uwagi = $_POST['uwagi']; 

    $result = $conn->query("SELECT * FROM paczki WHERE paczka_id = $paczka_id");
    if ($result->num_rows == 0) {
        $conn->query("INSERT INTO paczki (paczka_id, klient_id, adres_nadawcy_id, adres_odbiorcy_id, waga, wymiary, opis, data_odbioru, data_dostawy, status_id) 
                      VALUES ('$paczka_id', 1, 1, 1, 0.0, '30x20x15', 'Описание по умолчанию', NULL, NULL, 1)")
              or die("Ошибка при вставке новой пачки: " . $conn->error);
    }

    $conn->query("UPDATE dostawy 
                  SET paczka_id = '$paczka_id', kurier_id = '$kurier_id', czas_odbioru = '$czas_odbioru', czas_dostawy = '$czas_dostawy', uwagi = '$uwagi' 
                  WHERE dostawa_id = $dostawa_id") or die("Ошибка обновления доставки: " . $conn->error);

    header("Location: index.php");
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Edytuj dostawę</title>
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
    <a href="index.php"> Głowna strona</a>
    </div>
    <h1>Edytuj dostawę</h1>
    <form action="update.php" method="POST">
        <input type="hidden" name="id" value="<?php echo $row['dostawa_id']; ?>">
        <input type="number" name="paczka_id" value="<?php echo $row['paczka_id']; ?>" required>
        <input type="number" name="kurier_id" value="<?php echo $row['kurier_id']; ?>" required>
        <input type="datetime-local" name="czas_odbioru" value="<?php echo $row['czas_odbioru']; ?>" required>
        <input type="datetime-local" name="czas_dostawy" value="<?php echo $row['czas_dostawy']; ?>" required>
        <textarea name="uwagi"><?php echo $row['uwagi']; ?></textarea>
        <button type="submit" name="update">Zaktualizuj</button>
    </form>
</body>
</html>