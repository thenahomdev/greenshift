<?php

// Debug tijdelijk
ini_set('display_errors', 1);
error_reporting(E_ALL);


// ===============================
// RIJINSTRUCTEURS OPHALEN
// ===============================

$sql = "
    SELECT
        r.rijinstructeurid,
        r.voornaam,
        r.tussenvoegsel,
        r.achternaam,
        l.email
    FROM rijinstructeurs r
    LEFT JOIN login l
        ON r.loginid = l.loginid
    ORDER BY r.achternaam
";

$stmt = $pdo->prepare($sql);
$stmt->execute();

$rijinstructeurs = $stmt->fetchAll(PDO::FETCH_ASSOC);