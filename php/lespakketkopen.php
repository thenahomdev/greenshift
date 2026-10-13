<?php
session_start();
include __DIR__ . '/../config/connection.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

// 🔐 Check login
if (!isset($_SESSION['loginid'])) {
    die("Niet ingelogd");
}

$loginid = $_SESSION['loginid'];
$pakketid = $_GET['id'];

// 🔴 Check ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Ongeldig pakket ID");
}

$pakketid = $_GET['id'];

// 🔍 Leerling ophalen
$sql = 'SELECT * 
        FROM leerlingen AS ll 
        LEFT JOIN login AS l 
            ON l.loginid = ll.loginid 
        WHERE l.loginid = :loginid';

$stmt = $pdo->prepare($sql);
$stmt->execute([':loginid' => $loginid]);

$leerling = $stmt->fetch();

if (!$leerling) {
    die("Leerling niet gevonden");
}

$leerlingid = $leerling['leerlingid'];

// 🔍 PAKKET OPHALEN (VOOR PRIJS!)
$sql = "SELECT prijs FROM pakketten WHERE pakkettenid = :pakketid";
$stmt = $pdo->prepare($sql);
$stmt->execute([':pakketid' => $pakketid]);

$pakket = $stmt->fetch();

if (!$pakket) {
    die("Pakket niet gevonden");
}

$prijs = $pakket['prijs'];

// 🔴 Check of al gekocht
//$sql = "SELECT * FROM pakket_geschiedenis
//        WHERE pakketid = :pakketid AND leerlingid = :leerlingid";
//
//$stmt = $pdo->prepare($sql);
//$stmt->execute([
//    ':pakketid' => $pakketid,
//    ':leerlingid' => $leerlingid
//]);
//
//if ($stmt->rowCount() > 0) {
//    die("Je hebt dit pakket al gekocht");
//}

// 📅 Datum
$datum = date("Y-m-d");

// 💾 Insert MET PRIJS
$sql = "INSERT INTO pakket_geschiedenis (pakketid, leerlingid, prijs, datum) 
        VALUES (:pakketid, :leerlingid, :prijs, :datum)";

$stmt = $pdo->prepare($sql);
$stmt->execute([
    ':pakketid' => $pakketid,
    ':leerlingid' => $leerlingid,
    ':prijs' => $prijs,
    ':datum' => $datum
]);

$_SESSION['success'] = "Succesvol gekocht";

header("Location: ../index.php?page=lespakketkopen");
exit();