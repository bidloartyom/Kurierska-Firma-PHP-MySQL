<?php

$conn = new mysqli("localhost", "root", "root", "kurierfirma");

if ($conn->connect_error) {
    die("Błąd połączenia z bazą danych: " . $conn->connect_error);
}

if (isset($_POST['save'])) {
    $status_id = $_POST['status_id'];
    $dostawa_id = $_POST['dostawa_id'];
    $paczka_id = $_POST['paczka_id'];
    $klient_id = $_POST['klient_id'];
    $adres_nadawcy_id = $_POST['adres_nadawcy_id'];
    $adres_odbiorcy_id = $_POST['adres_odbiorcy_id'];
    $waga = $_POST['waga'];
    $wymiary = $_POST['wymiary'];
    $opis = $_POST['opis'];

    $kurier_id = $_POST['kurier_id'];
    $czas_odbioru = $_POST['czas_odbioru'];
    $czas_dostawy = $_POST['czas_dostawy'];
    $uwagi = $_POST['uwagi'];

    $conn->query("INSERT INTO paczki (paczka_id, klient_id, adres_nadawcy_id, adres_odbiorcy_id, waga, wymiary, opis, data_odbioru, data_dostawy, status_id) 
                  VALUES ('$paczka_id', '$klient_id', '$adres_nadawcy_id', '$adres_odbiorcy_id', '$waga', '$wymiary', '$opis','$czas_odbioru','$czas_dostawy','')") or die($conn->error);
    
    $new_paczka_id = $conn->insert_id;


    $conn->query("INSERT INTO dostawy (dostawa_id, paczka_id, kurier_id, czas_odbioru, czas_dostawy, uwagi) 
                  VALUES ('$dostawa_id', '$paczka_id','$kurier_id', '$czas_odbioru', '$czas_dostawy', '$uwagi')") or die($conn->error);

    header("Location: index.php");
}
?>