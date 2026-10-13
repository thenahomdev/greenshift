<?php
session_start();
include __DIR__ . '/../config/connection.php';

if (!isset($_POST['id'])) {
    echo "Geen rijinstructeur gekozen.";
    return;
}

$id = $_POST['id'];

if (isset($_POST['opslaan'])) {

    $voornaam = $_POST['voornaam'];
    $tussen = $_POST['tussenvoegsel'];
    $achternaam = $_POST['achternaam'];
    $email = $_POST['email'];
    $wachtwoord = $_POST['wachtwoord'];
    $wachtwoord2 = $_POST['wachtwoord2'];

    // loginid ophalen
    $stmt = $pdo->prepare("
        SELECT loginid FROM rijinstructeurs
        WHERE rijinstructeurid= :id
    ");
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $loginid = $stmt->fetchColumn();

    // 1️⃣ Email uniek?
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM login
        WHERE email = ? AND loginid != ?
    ");
    $stmt->execute([$email, $loginid]);
    $emailBestaat = $stmt->fetchColumn();

    if ($emailBestaat > 0) {
        $melding = "Email bestaat al!";
        $melding_type = "error";
    } elseif (!empty($wachtwoord)) { // 2️⃣ Wachtwoord check (alleen als ingevuld)

        if (strlen($wachtwoord) < 8) {
            $melding = "Wachtwoord moet minimaal 8 karakters zijn.";
            $melding_type = "error";

        } elseif ($wachtwoord !== $wachtwoord2) {
            $melding = "Wachtwoorden komen niet overeen.";
            $melding_type = "melding";
            echo "b";
        } else {

            // wachtwoord hash
            $hash = password_hash($wachtwoord, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare("
                UPDATE login
                SET wachtwoord = ?
                WHERE loginid = ?
            ");
            $stmt->execute([$hash, $loginid]);

        }
    }

    if (empty($melding)) {

        try {

            // instructeur aanpassen
            $stmt = $pdo->prepare("
            UPDATE rijinstructeurs
            SET voornaam=?, tussenvoegsel=?, achternaam=?
            WHERE rijinstructeurid=?
        ");
            $stmt->execute([$voornaam, $tussen, $achternaam, $id]);

            // email aanpassen
            $stmt = $pdo->prepare("
            UPDATE login
            SET email=?
            WHERE loginid=?
        ");
            $stmt->execute([$email, $loginid]);

            $melding = "Rijinstructeur aangepast!";
            $melding_type = "success";
            $_SESSION[$melding_type] = $melding;
            header("Location: ../index.php?page=rijinstructeur_overzicht");
            exit();
        }
        catch (Exception $e)
        {
            $_SESSION['error'] = "Fout bij registratie: " . $e->getMessage();
            header("Location: ../index.php?page=rijinstructeur_overzicht");
            exit();
        }
    }
    else{
        $_SESSION[$melding_type] = $melding;
        header("Location: ../index.php?page=rijinstructeur_aanpassen&rijinstructeurid=".$id);
        exit();
    }
}