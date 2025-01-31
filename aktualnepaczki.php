<?php
$conn = new mysqli("localhost", "root", "root", "kurierfirma");

if ($conn->connect_error) {
    die("Błąd połączenia z bazą danych: " . $conn->connect_error);
}

$sql = "
    SELECT *
    FROM paczki
    WHERE data_dostawy >= CURDATE() - INTERVAL 3 DAY
";
$result = $conn->query($sql);

if ($result->num_rows > 0): ?>
    <!DOCTYPE html>
    <html lang="pl">
    <head>
        <meta charset="UTF-8">
        <title>Przesyłki w ciągu 3 dni</title>
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
        <h1>Paczki w ciągu ostatnich 3 dni</h1>
        <table>
            <thead>
                <tr>
                    <th>Identyfikator pakietu</th>
                    <th>Identyfikator klienta</th>
                    <th>Adres nadawcy</th>
                    <th>Adres odbiorcy</th>
                    <th>Waga</th>
                    <th>Wymiary</th>
                    <th>Opis</th>
                    <th>Data dostawy</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['paczka_id']; ?></td>
                        <td><?php echo $row['klient_id']; ?></td>
                        <td><?php echo $row['adres_nadawcy_id']; ?></td>
                        <td><?php echo $row['adres_odbiorcy_id']; ?></td>
                        <td><?php echo $row['waga']; ?> kg</td>
                        <td><?php echo $row['wymiary']; ?></td>
                        <td><?php echo $row['opis']; ?></td>
                        <td><?php echo $row['data_dostawy']; ?></td>
                        <td><?php echo $row['status_id']; ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </body>
    </html>
<?php else: ?>
    <!DOCTYPE html>
    <html lang="pl">
    <head>
        <meta charset="UTF-8">
        <title>Paczki w ciągu ostatnich 3 dni</title>
    </head>
    <body>
        <h1>Nie ma przesyłek za ostatnie 3 dni</h1>
    </body>
    </html>
<?php endif;
$conn->close();
?>