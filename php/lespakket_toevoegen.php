<?php
session_start();
include __DIR__ . '/../config/connection.php';

// ===== ZORG DAT VARIABELEN BESTAAN =====
if (!isset($melding)) $melding = "";
if (!isset($melding_type)) $melding_type = "";


// ===== DATABASE CONNECTIE VIA $pdo =====
if (!isset($pdo) || !$pdo instanceof PDO) {
    $melding = "❌ Geen database connectie.";
    $melding_type = "alert-error";
}


// ===== FORM VERWERKEN =====
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["toevoegen"])) {

    $naam = trim($_POST["naam"] ?? "");
    $prijs = str_replace(",", ".", trim($_POST["prijs"] ?? ""));
    $aantal_lessen = trim($_POST["aantal_lessen"] ?? "");
    $status = $_POST["status"] ?? "1";

    if ($naam === "" || $prijs === "" || $aantal_lessen === "") {

        $melding = "⚠️ Vul alle velden in.";
        $melding_type = "alert-error";

    } else {

        try {

            $sql = "SELECT COUNT(*) FROM pakketten WHERE aantal_lessen = :aantal";
            $stmtcheck = $pdo->prepare($sql);
            $stmtcheck->bindParam(':aantal', $aantal, PDO::PARAM_INT);
            $stmtcheck->execute();

            $exists = $stmtcheck->fetchColumn();

            if ($exists = 0) {

            $stmt = $pdo->prepare("
                INSERT INTO pakketten
                (naam, prijs, aantal_lessen, status)
                VALUES
                (:naam, :prijs, :aantal_lessen, :status)
            ");

            $stmt->execute([
                ":naam" => $naam,
                ":prijs" => $prijs,
                ":aantal_lessen" => $aantal_lessen,
                ":status" => $status
            ]);

            $melding = "✅ Lespakket toegevoegd!";
            $melding_type = "success";
            $_SESSION[$melding_type] = $melding;

            }else{
                $_SESSION['error'] = "Aantal lessen bestaat al in de database.";
            }
        } catch (PDOException $e) {

            $melding = "❌ Database fout: " . $e->getMessage();
            $melding_type = "error";
            $_SESSION[$melding_type] = $melding;

        }
    }
}
header("Location: ../index.php?page=lespakket_toevoegen");
exit();