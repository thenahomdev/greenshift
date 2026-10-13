<?php
session_start();
include __DIR__ . '/../config/connection.php';

// ===== VERWIJDEREN (blijft hetzelfde) =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {

    $deleteId = (int)$_POST['delete_id'];

    if ($deleteId > 0) {

        $check = $pdo->prepare("
            SELECT aantal_lessen
            FROM pakketten
            WHERE pakkettenid = :id
        ");
        $check->execute([':id' => $deleteId]);
        $pakket = $check->fetch(PDO::FETCH_ASSOC);

        if ($pakket) {

            if ((int)$pakket['aantal_lessen'] === 1) {

                $melding = "❌ Losse lessen kunnen niet verwijderd worden.";
                $melding_type = "error";
            } else {

                $stmtDel = $pdo->prepare("
                    UPDATE pakketten
                    SET status = 0
                    WHERE pakkettenid = :id
                ");
                $stmtDel->execute([':id' => $deleteId]);

                $melding = "🗑️ Lespakket succesvol verwijderd.";
                $melding_type = 'success';
            }
            $_SESSION[$melding_type] = $melding;
            header("Location: ../index.php?page=lespakket_overzicht");
            exit();
        }
    }
}


//// ===== GOEDKOOP → DUUR SORTEREN =====
//$sql = "SELECT pakkettenid, naam, prijs, aantal_lessen
//        FROM pakketten
//        WHERE status = 1
//        ORDER BY prijs ASC";  // ⭐ DIT IS HET BELANGRIJKE
//
//$stmt = $pdo->prepare($sql);
//$stmt->execute();
//
//$lespakket = $stmt->fetchAll(PDO::FETCH_ASSOC);
//
//include __DIR__ . "/../../includes/admin/lespakket_overzicht.inc.php";