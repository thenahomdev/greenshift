<?php

date_default_timezone_set("Europe/Amsterdam");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$bookedSlots = [];
$availableSlots = [];

if (!isset($_SESSION['loginid'])) {
    header("Location: index.php?page=login");
    exit();
}

$loginid = (int) $_SESSION['loginid'];


/* LEERLING + INSTRUCTEUR */

$stmt = $pdo->prepare("
SELECT leerlingid, rijinstructeurid
FROM leerlingen
WHERE loginid = :loginid
LIMIT 1
");

$stmt->execute([
    'loginid' => $loginid
]);

$leerling = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$leerling) {
    die("Leerling niet gevonden.");
}

$leerlingId = (int) $leerling['leerlingid'];
$rijinstructeurId = (int) $leerling['rijinstructeurid'];


/* WEEK */

$weekNumber = isset($_GET['week']) ? (int)$_GET['week'] : (int)date("W");

if ($weekNumber < 1) $weekNumber = 1;
if ($weekNumber > 53) $weekNumber = 53;

$year = (int) date("o");


/* DATUMS */

$monday = new DateTime();
$monday->setISODate($year, $weekNumber);

$tuesday = clone $monday;
$tuesday->modify("+1 day");

$wednesday = clone $monday;
$wednesday->modify("+2 day");

$thursday = clone $monday;
$thursday->modify("+3 day");

$friday = clone $monday;
$friday->modify("+4 day");


/* WEEK NAVIGATIE */

$prevWeek = max(1, $weekNumber - 1);
$nextWeek = min(53, $weekNumber + 1);


/* HUIDIGE WEEK CHECK */

$today = new DateTime();
$currentWeek = (int)$today->format("W");

/* BLOKKEREN ALS HET NIET DE HUIDIGE WEEK IS */

$volgendeWeekGeblokkeerd = false;

if ($weekNumber != $currentWeek) {
    $volgendeWeekGeblokkeerd = true;
}


/* RELEASE DATUM = ZATERDAG VOOR DIE WEEK */

$releaseDate = clone $monday;
$releaseDate->modify('-2 day'); // zaterdag vóór maandag


$startDate = $monday->format("Y-m-d");
$endDate = $friday->format("Y-m-d");


/* ALLEEN HUIDIGE WEEK BOEKEN TOEGESTAAN */

$boekenToegestaan = ($weekNumber == $currentWeek);


/* LES BOEKEN */

if (isset($_POST['boek_les'])) {

    if (!$boekenToegestaan) {
        $_SESSION['error'] = "Deze week is nog niet vrijgegeven.";
        header("Location:index.php?page=rijles_inplannen&week=".$weekNumber);
        exit();
    }

    $datum = $_POST['datum'];
    $tijd = $_POST['tijd'];

    /* MAX 2 LESSEN PER WEEK */

    $stmt = $pdo->prepare("
    SELECT COUNT(*)
    FROM rijlessen
    WHERE leerlingid = :leerlingid
    AND status = 1
    AND datum BETWEEN :start AND :end
    ");

    $stmt->execute([
        'leerlingid' => $leerlingId,
        'start' => $startDate,
        'end' => $endDate
    ]);

    $aantalLessenDezeWeek = $stmt->fetchColumn();

    if ($aantalLessenDezeWeek >= 2) {

        $_SESSION['error'] = "Je mag maximaal 2 rijlessen per week plannen.";

        header("Location:index.php?page=rijles_inplannen&week=".$weekNumber);
        exit();
    }


    /* LESTEGOED */

    $stmt = $pdo->prepare("
    SELECT COALESCE(SUM(p.aantal_lessen),0)
    FROM pakket_geschiedenis ph
    JOIN pakketten p ON ph.pakketid = p.pakkettenid
    WHERE ph.leerlingid = :leerlingid
    ");

    $stmt->execute([
        'leerlingid'=>$leerlingId
    ]);

    $totaalLessen = $stmt->fetchColumn();

    $stmt = $pdo->prepare("
    SELECT COUNT(*)
    FROM rijlessen
    WHERE leerlingid = :leerlingid
    AND status = 1
    ");

    $stmt->execute([
        'leerlingid'=>$leerlingId
    ]);

    $actieveBoekingen = $stmt->fetchColumn();

    $huidigLestegoed = $totaalLessen - $actieveBoekingen;

    if ($huidigLestegoed <= 0) {

        $_SESSION['error'] = "Je hebt geen lessen meer.";
        header("Location:index.php?page=rijles_inplannen&week=".$weekNumber);
        exit();

    }

    /* BESCHIKBAARHEID */

    $stmt = $pdo->prepare("
    SELECT id
    FROM instructeur_beschikbaarheid
    WHERE rijinstructeurid = :rijinstructeurid
    AND datum = :datum
    AND tijd = :tijd
    LIMIT 1
    ");

    $stmt->execute([
        'rijinstructeurid'=>$rijinstructeurId,
        'datum'=>$datum,
        'tijd'=>$tijd
    ]);

    if ($stmt->rowCount() === 0) {

        $_SESSION['error'] = "Dit tijdslot is niet beschikbaar.";
        header("Location:index.php?page=rijles_inplannen&week=".$weekNumber);
        exit();

    }

    /* CHECK BEZET */

    $stmt = $pdo->prepare("
    SELECT rijlesid
    FROM rijlessen
    WHERE datum=:datum
    AND tijd=:tijd
    AND rijinstructeurid=:rijinstructeurid
    AND status=1
    LIMIT 1
    ");

    $stmt->execute([
        'datum'=>$datum,
        'tijd'=>$tijd,
        'rijinstructeurid'=>$rijinstructeurId
    ]);

    if ($stmt->rowCount() > 0) {

        $_SESSION['error'] = "Dit tijdslot is al bezet.";
        header("Location:index.php?page=rijles_inplannen&week=".$weekNumber);
        exit();

    }

    /* LES OPSLAAN */

    $stmt = $pdo->prepare("
    INSERT INTO rijlessen
    (leerlingid,rijinstructeurid,datum,tijd,status)
    VALUES
    (:leerlingid,:rijinstructeurid,:datum,:tijd,1)
    ");

    $stmt->execute([
        'leerlingid'=>$leerlingId,
        'rijinstructeurid'=>$rijinstructeurId,
        'datum'=>$datum,
        'tijd'=>$tijd
    ]);

    $_SESSION['success'] = "Je rijles is succesvol geboekt.";

    header("Location:index.php?page=rijles_inplannen&week=".$weekNumber);
    exit();
}


/* LES ANNULEREN */

if (isset($_POST['annuleer_les'])) {

    $datum = $_POST['datum'];
    $tijd = $_POST['tijd'];

    $stmt = $pdo->prepare("
    UPDATE rijlessen
    SET status=0
    WHERE datum=:datum
    AND tijd=:tijd
    AND leerlingid=:leerlingid
    AND rijinstructeurid=:rijinstructeurid
    AND status=1
    LIMIT 1
    ");

    $stmt->execute([
        'datum'=>$datum,
        'tijd'=>$tijd,
        'leerlingid'=>$leerlingId,
        'rijinstructeurid'=>$rijinstructeurId
    ]);

    $_SESSION['success'] = "Je rijles is geannuleerd.";

    header("Location:index.php?page=rijles_inplannen&week=".$weekNumber);
    exit();
}


/* BESCHIKBAARHEID */

$stmt = $pdo->prepare("
SELECT datum,tijd
FROM instructeur_beschikbaarheid
WHERE rijinstructeurid=:rijinstructeurid
AND datum BETWEEN :start AND :end
");

$stmt->execute([
    'rijinstructeurid'=>$rijinstructeurId,
    'start'=>$startDate,
    'end'=>$endDate
]);

while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
    $key = $row['datum']."_".date("H:i",strtotime($row['tijd']));
    $availableSlots[$key] = true;
}


/* GEBOEKTE LESSEN */

$stmt = $pdo->prepare("
SELECT datum,tijd,leerlingid
FROM rijlessen
WHERE rijinstructeurid=:rijinstructeurid
AND status=1
AND datum BETWEEN :start AND :end
");

$stmt->execute([
    'rijinstructeurid'=>$rijinstructeurId,
    'start'=>$startDate,
    'end'=>$endDate
]);

while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
    $key = $row['datum']."_".date("H:i",strtotime($row['tijd']));
    $bookedSlots[$key] = (int)$row['leerlingid'];
}