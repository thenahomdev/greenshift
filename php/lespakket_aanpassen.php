<?php
session_start();
include __DIR__ . '/../config/connection.php';

$id = $_POST['lespakketid'];

if (!$id) {
    $_SESSION['error'] = "Ongeldig pakket ID.";
    header("Location:index.php?page=lespakket_aanpasse");
    exit();
}

try {
    $naam = $_POST['naam'];
    $prijs = $_POST['prijs'];
    $aantal = $_POST['aantal'];

//    $sql = "SELECT COUNT(*) FROM pakketten WHERE aantal_lessen = :aantal and status = :";
//    $stmtcheck = $pdo->prepare($sql);
//    $stmtcheck->bindParam(':aantal', $aantal, PDO::PARAM_INT);
//    $stmtcheck->execute();
//
//    $exists = $stmtcheck->fetchColumn();
//
//    if($exists == 0){

        $sql = 'UPDATE pakketten Set status= 0 WHERE pakkettenid = :id';
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $sql = 'INSERT INTO pakketten (naam, prijs, aantal_lessen, status) VALUES(:naam, :prijs, :aantal,1)';
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':naam', $naam, PDO::PARAM_STR);
        $stmt->bindParam(':prijs', $prijs, PDO::PARAM_STR);
        $stmt->bindParam(':aantal', $aantal, PDO::PARAM_INT);
        $stmt->execute();

        $_SESSION['success'] = "Lespakket succesvol aangepast.";
//    }else {
//        $_SESSION['error'] = "Aantal lessen bestaat al in de database.";
//    }
}catch (PDOException $e){

    $_SESSION['error'] = $e->getMessage();

}
header("Location: ../index.php?page=lespakket_overzicht");
exit();
