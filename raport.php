<?php
$conn = new mysqli("localhost", "root", "root", "kurierfirma");

if ($conn->connect_error) {
    die("Błąd połączenia z bazą danych: " . $conn->connect_error);
}

$report1 = $conn->query("
SELECT DISTINCT k.kurier_id, k.imie_nazwisko
FROM dostawy d
         JOIN paczki p ON d.paczka_id = p.paczka_id
         JOIN kurierzy k ON d.kurier_id = k.kurier_id
WHERE p.waga > 3
  AND d.czas_dostawy BETWEEN '2023-01-01' AND '2023-12-31';

");

$report2 = $conn->query("
    SELECT s.nazwa_statusu, COUNT(p.paczka_id) AS liczba_paczek
    FROM paczki p
    JOIN statusypaczek s ON p.status_id = s.status_id
    WHERE p.data_dostawy IS NOT NULL
    GROUP BY s.nazwa_statusu
");

$report3 = $conn->query("
    SELECT
        p.paczka_id,
        p.opis,
        a.miasto,
        a.wojewodztwo,
        k.imie_nazwisko AS klient_odbiorca,
        p.data_dostawy
    FROM paczki p
    JOIN adresy a ON p.adres_odbiorcy_id = a.adres_id
    JOIN klienci k ON p.klient_id = k.klient_id
    WHERE a.wojewodztwo = 'Mazowieckie'
    AND p.opis LIKE '%Documents%'
    AND p.data_dostawy IS NOT NULL
");

$report4 = $conn->query("
    SELECT
        k.klient_id,
        k.imie_nazwisko AS klient,
        k.telefon AS telefon_klienta,
        k.email AS email_klienta,
        COUNT(p.paczka_id) AS liczba_dostarczonych_paczek,
        SUM(pl.kwota) AS suma_wplaty,
        mp.nazwa_metody AS najczesciej_uzywana_metoda_oplaty
    FROM klienci k
    JOIN paczki p ON k.klient_id = p.klient_id
    LEFT JOIN platnosci pl ON p.paczka_id = pl.paczka_id
    LEFT JOIN metodyplatnosci mp ON pl.metoda_platnosci_id = mp.metoda_platnosci_id
    WHERE p.data_dostawy IS NOT NULL
    GROUP BY k.klient_id, k.imie_nazwisko, k.telefon, k.email, mp.nazwa_metody
    ORDER BY suma_wplaty DESC
");
$report5 = $conn->query("
SELECT 
    DATE_FORMAT(p.data_dostawy, '%Y-%m') AS miesiac,
    COUNT(p.paczka_id) AS liczba_dostarczonych_paczek,
    SUM(pl.kwota) AS calkowity_przychod,
    SUM(pl.kwota * 0.23) AS suma_podatku_vat,
    SUM(pl.kwota * 0.15) AS koszty_logistyczne,
    SUM(pl.kwota - (pl.kwota * 0.23) - (pl.kwota * 0.15)) AS dochod_netto,
    ROUND(SUM(pl.kwota - (pl.kwota * 0.23) - (pl.kwota * 0.15)) / COUNT(p.paczka_id), 2) AS sredni_przychod_na_paczke,
    COUNT(DISTINCT d.kurier_id) AS liczba_aktywnych_kurierow,
    (SELECT COUNT(*) 
     FROM paczki p2 
     WHERE p2.data_dostawy IS NULL 
     AND DATE_FORMAT(p2.data_dostawy, '%Y-%m') = DATE_FORMAT(p.data_dostawy, '%Y-%m')
    ) AS liczba_niedostarczonych_paczek,
    (SELECT k.imie_nazwisko 
     FROM dostawy d2 
     JOIN kurierzy k ON d2.kurier_id = k.kurier_id 
     JOIN paczki p2 ON d2.paczka_id = p2.paczka_id 
     WHERE DATE_FORMAT(p2.data_dostawy, '%Y-%m') = DATE_FORMAT(p.data_dostawy, '%Y-%m') 
     GROUP BY d2.kurier_id 
     ORDER BY COUNT(d2.paczka_id) DESC 
     LIMIT 1
    ) AS najbardziej_zajety_kurier
FROM 
    paczki p
JOIN 
    platnosci pl ON p.paczka_id = pl.paczka_id
JOIN 
    dostawy d ON p.paczka_id = d.paczka_id
WHERE 
    p.data_dostawy IS NOT NULL
    AND p.data_dostawy BETWEEN '2023-01-01' AND '2023-12-31'
GROUP BY 
    DATE_FORMAT(p.data_dostawy, '%Y-%m');
");
?>
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Raporty</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 20px;
            color: #333;
        }

        h1 {
            text-align: center;
            color: #007bff;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        table, th, td {
            border: 1px solid #ddd;
        }

        table th, table td {
            padding: 10px;
            text-align: left;
        }

        table th {
            background-color: #007bff;
            color: white;
        }

        .error {
            color: red;
        }
    </style>
</head>
<body>
    <a href="index.php">Główna strona</a>
    <h1>Raporty Kurierów</h1>

    <h2>Kurierzy, którzy dostarczyli paczki ważące ponad 3 kg w ciągu 2023 roku</h2>
    <?php if ($report1 && $report1->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>ID Kuriera</th>
                    <th>Imię i nazwisko</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $report1->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['kurier_id']; ?></td>
                        <td><?php echo $row['imie_nazwisko']; ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>Brak danych w raporcie 1.</p>
    <?php endif; ?>

    <h2>Całkowita liczba dostarczonych paczek dla każdego statusu</h2>
    <?php if ($report2 && $report2->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Status paczki</th>
                    <th>Liczba paczek</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $report2->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['nazwa_statusu']; ?></td>
                        <td><?php echo $row['liczba_paczek']; ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>Brak danych w raporcie 2.</p>
    <?php endif; ?>

    <h2>Paczki dostarczone do województwa "Mazowieckie" z opisem zawierającym słowo "Documents"</h2>
    <?php if ($report3 && $report3->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>ID Paczki</th>
                    <th>Opis</th>
                    <th>Miasto</th>
                    <th>Województwo</th>
                    <th>Klient odbiorca</th>
                    <th>Data dostawy</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $report3->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['paczka_id']; ?></td>
                        <td><?php echo $row['opis']; ?></td>
                        <td><?php echo $row['miasto']; ?></td>
                        <td><?php echo $row['wojewodztwo']; ?></td>
                        <td><?php echo $row['klient_odbiorca']; ?></td>
                        <td><?php echo $row['data_dostawy']; ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>Brak danych w raporcie 3.</p>
    <?php endif; ?>

    <h2>Płatności za dostarczone paczki oraz najczęściej używana metoda płatności</h2>
    <?php if ($report4 && $report4->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>ID Klienta</th>
                    <th>Klient</th>
                    <th>Telefon</th>
                    <th>Email</th>
                    <th>Liczba paczek</th>
                    <th>Suma wpłat</th>
                    <th>Najczęściej używana metoda</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $report4->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['klient_id']; ?></td>
                        <td><?php echo $row['klient']; ?></td>
                        <td><?php echo $row['telefon_klienta']; ?></td>
                        <td><?php echo $row['email_klienta']; ?></td>
                        <td><?php echo $row['liczba_dostarczonych_paczek']; ?></td>
                        <td><?php echo $row['suma_wplaty']; ?> zł</td>
                        <td><?php echo $row['najczesciej_uzywana_metoda_oplaty']; ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>Brak danych w raporcie 4.</p>
    <?php endif; ?>
    <h2>Miesięczny raport o przychodach, paczkach i aktywności</h2>
    <?php if ($report5 && $report5->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Miesiąc</th>
                    <th>Liczba Dostarczonych Paczek</th>
                    <th>Przychód (brutto)</th>
                    <th>Podatek VAT (23%)</th>
                    <th>Koszty Logistyczne (15%)</th>
                    <th>Dochód (netto)</th>
                    <th>Średni Przychód na Paczkę</th>
                    <th>Liczba Aktywnych Kurierów</th>
                    <th>Paczek Niedostarczonych</th>
                    <th>Najbardziej Zajęty Kurier</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $report5->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['miesiac']; ?></td>
                        <td><?php echo $row['liczba_dostarczonych_paczek']; ?></td>
                        <td><?php echo number_format($row['calkowity_przychod'], 2, ',', ' ') . ' zł'; ?></td>
                        <td><?php echo number_format($row['suma_podatku_vat'], 2, ',', ' ') . ' zł'; ?></td>
                        <td><?php echo number_format($row['koszty_logistyczne'], 2, ',', ' ') . ' zł'; ?></td>
                        <td><?php echo number_format($row['dochod_netto'], 2, ',', ' ') . ' zł'; ?></td>
                        <td><?php echo number_format($row['sredni_dochód_na_paczke'], 2, ',', ' ') . ' zł'; ?></td>
                        <td><?php echo $row['liczba_aktywnych_kurierow']; ?></td>
                        <td><?php echo $row['liczba_niedostarczonych_paczek']; ?></td>
                        <td><?php echo $row['najbardziej_zajety_kurier']; ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>Brak danych w raporcie 5.</p>
    <?php endif; ?>
</body>
</html>