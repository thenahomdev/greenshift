<?php
session_start();
include __DIR__ . '/../config/connection.php';

if (!isset($_GET['id'])) {
    die("Geen ID opgegeven");
}

$verwijderid = $_GET['id'];

try {
    $pdo->beginTransaction();

    $sql = "
        SELECT r.rijinstructeurid, COUNT(l.leerlingid) AS aantal
        FROM rijinstructeurs r
        LEFT JOIN leerlingen l 
            ON r.rijinstructeurid = l.rijinstructeurid
         WHERE r.status = 1
         AND r.rijinstructeurid != :id
        GROUP BY r.rijinstructeurid
        ORDER BY aantal ASC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':id', $verwijderid, PDO::PARAM_INT);
    $stmt->execute();

    $instructeurs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($instructeurs) == 0) {
        $pdo->rollBack();
        $_SESSION['error'] = "Geen andere rijinstructeurs beschikbaar";
        header("Location: ../index.php?page=rijinstructeur_overzicht");
        exit();
    }

    $sql = "
        SELECT leerlingid 
        FROM leerlingen 
        WHERE rijinstructeurid = :id
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':id', $verwijderid, PDO::PARAM_INT);
    $stmt->execute();

    $leerlingen = $stmt->fetchAll(PDO::FETCH_COLUMN);

    foreach ($leerlingen as $leerlingId) {

        usort($instructeurs, function($a, $b) {
            return $a['aantal'] <=> $b['aantal'];
        });

        $gekozen = $instructeurs[0];

        $update = $pdo->prepare("
          UPDATE leerlingen 
            SET 
                old_rijinstructeurid = rijinstructeurid,
                rijinstructeurid = :nieuwId
            WHERE leerlingid = :leerlingId
        ");

        $update->execute([
            'nieuwId' => $gekozen['rijinstructeurid'],
            'leerlingId' => $leerlingId
        ]);

        $instructeurs[0]['aantal']++;
    }


    $sql = 'SELECT * FROM rijinstructeurs AS r 
            LEFT JOIN login AS l 
                ON l.loginid = r.loginid 
         WHERE r.rijinstructeurid = :id';
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':id', $verwijderid, PDO::PARAM_INT);
    $stmt->execute();

    $login_info = $stmt->fetch(PDO::FETCH_ASSOC);

    $loginid = $login_info['loginid'];

    $sql = "
        UPDATE rijinstructeurs SET status = 0
        WHERE rijinstructeurid = :id
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':id', $verwijderid, PDO::PARAM_INT);
    $stmt->execute();

    if ($loginid) {
        $sql = 'UPDATE login SET status = 0 WHERE loginid = :loginid';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':loginid', $loginid, PDO::PARAM_INT);
        $stmt->execute();
    }

    $pdo->commit();

    $_SESSION['success'] = "Rijinstructeur succesvol verwijderd en leerlingen verdeeld";
    header("Location: ../index.php?page=rijinstructeur_overzicht");
    exit();

} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['error'] = "Fout: " . $e->getMessage();
    header("Location: ../index.php?page=rijinstructeur_overzicht");
    exit();
}