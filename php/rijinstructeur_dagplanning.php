<?php
date_default_timezone_set("Europe/Amsterdam");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['loginid'])) {
    header("Location: index.php?page=login");
    exit();
}

$loginid = (int) $_SESSION['loginid'];

/* INSTRUCTEUR OPHALEN */
$stmt = $pdo->prepare("
SELECT rijinstructeurid
FROM rijinstructeurs
WHERE loginid = :loginid
LIMIT 1
");
$stmt->execute(['loginid' => $loginid]);
$instructeur = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$instructeur) {
    die("Rijinstructeur niet gevonden.");
}

$rijinstructeurId = (int)$instructeur['rijinstructeurid'];

/* DATUM */
$selectedDate = $_GET['datum'] ?? date("Y-m-d");

try {
    $datumObject = new DateTime($selectedDate);
} catch (Exception $e) {
    $datumObject = new DateTime();
}

/* LESSEN OPHALEN */
$stmt = $pdo->prepare("
SELECT 
    r.datum,
    r.tijd, 
    l.voornaam,
    l.tussenvoegsel,
    l.achternaam,
    l.telefoonnummer,
    a.straatnaam,
    a.postcode,
    a.huisnummer,
    a.plaats
FROM rijlessen r
JOIN leerlingen l ON r.leerlingid = l.leerlingid
LEFT JOIN adres a ON l.adresid = a.adresid
WHERE r.rijinstructeurid = :rijinstructeurid
AND r.status = 1
AND r.datum = :datum
ORDER BY r.tijd ASC
");
$stmt->execute([
    'rijinstructeurid' => $rijinstructeurId,
    'datum' => $selectedDate
]);
$lessen = $stmt->fetchAll(PDO::FETCH_ASSOC);