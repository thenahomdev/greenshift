<?php
session_start();
include __DIR__ . '/../config/connection.php';

//echo '<pre>';
//print_r($_POST);
//echo '</pre>';

if ($_SERVER['REQUEST_METHOD'] === 'POST' ) {

    $beschikbaarheid = $_POST['beschikbaarheid'];
    $loginid = $_SESSION['loginid'];
    $weekNumber = $_POST['week'];
    $monday = $_POST['monday'];
    $friday = $_POST['friday'];

    $sql = "SELECT * FROM rijinstructeurs WHERE loginid = '" . $loginid . "'";
    $result = $pdo->query($sql);
    $user = $result->fetch();

    $rijinstructeurid = $user['rijinstructeurid'];

    try {
        $actief = 1;
        $startDatum = $monday;
        $endDatum   = $friday;

        $sqlUpdate = "UPDATE instructeur_beschikbaarheid SET status = 0  WHERE rijinstructeurid = :rijinstructeurid AND datum BETWEEN :start AND :eind";
        $stmtUpdate = $pdo->prepare($sqlUpdate);
        $stmtUpdate->execute([
            ':rijinstructeurid' => $rijinstructeurid,
            ':start' => $startDatum,
            ':eind' => $endDatum
        ]);

        $sqlInsert = "INSERT INTO instructeur_beschikbaarheid (rijinstructeurid, datum, tijd, status) VALUES (:rid, :datum, :tijd, :status)";
        $stmtInsert = $pdo->prepare($sqlInsert);

        foreach ($beschikbaarheid as $datum => $uren)
        {
            foreach ($uren as $uur => $waarde)
            {
                $tijd = str_pad($uur, 2, "0", STR_PAD_LEFT) . ":00:00";

                $stmtInsert->execute([
                    ':rid' => $rijinstructeurid,
                    ':datum' => $datum,
                    ':tijd' => $tijd,
                    ':status' => $actief,
                ]);
            }
        }

        $_SESSION['success'] = "beschikbaarheid instellen geslaagd!";
        header("Location: ../index.php?page=beschikbaarheid_rijinstructeur_instellen&week=" . $weekNumber);
        exit();
    }
    catch (Exception $e)
    {
        $_SESSION['error'] = "Fout bij beschikbaarheid instellen.";

        header("Location: ../index.php?page=beschikbaarheid_rijinstructeur_instellen&week=" . $weekNumber);
        exit();
    }
}
?>
