<?php
session_start();
include __DIR__ . '/../config/connection.php';

$_SESSION['data'] = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST')
{
    $_SESSION['data'] = $_POST;

    $firstName = htmlspecialchars($_POST['firstName']);
    $tussenvoegsel = $_POST['tussenvoegsel'];
    $lastName = $_POST['lastName'];
    $straatnaam  = $_POST['straatnaam'];
    $huisnummer = $_POST['huisnummer'];
    $postcode = $_POST['postcode'];
    $plaats = $_POST['plaats'];
    $phone = $_POST['phone'];
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $passwordRepeat = $_POST['passwordRepeat'];
    $rijinstructeurid = $_POST['rijinstructeurid'];

    $errors = [];

    if ($password !== $passwordRepeat) {
        $errors[] = "Wachtwoorden komen niet overeen!";
    }

    if (strlen($password) < 8) {
        $errors[] = "Wachtwoord moet minimaal 8 tekens bevatten!";
    }

    if (!preg_match('/^06[0-9]{8}$/', $phone)) {
        $errors[] = "Telefoonnummer moet beginnen met 06 en 10 cijfers bevatten.";
    }

    $stmtCheckEmail = $pdo->prepare("SELECT COUNT(*) FROM login WHERE email = :email");
    $stmtCheckEmail->execute([':email' => $email]);
    if ($stmtCheckEmail->fetchColumn() > 0) {
        $errors[] = "Dit e-mailadres is al geregistreerd!";
    }

    $stmtCheckPhone = $pdo->prepare("SELECT COUNT(*) FROM leerlingen WHERE telefoonnummer = :telefoon");
    $stmtCheckPhone->execute([':telefoon' => $phone]);
    if ($stmtCheckPhone->fetchColumn() > 0) {
        $errors[] = "Dit telefoonnummer is al geregistreerd!";
    }

    if (!empty($errors)) {
        $_SESSION['error'] = implode("<br>", $errors);
        $_SESSION['old'] = $_POST;

        header("Location: ../index.php?page=registreren");
        exit();
    }

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    try {

        $pdo->beginTransaction();

        $roleId = 2;

        $sqlLogin = "INSERT INTO login (roleid, email, wachtwoord)
                     VALUES (:roleid, :email, :wachtwoord)";

        $stmtLogin = $pdo->prepare($sqlLogin);
        $stmtLogin->execute([
            ':roleid' => $roleId,
            ':email' => $email,
            ':wachtwoord' => $hashedPassword
        ]);

        $loginId = $pdo->lastInsertId();

        $sqlAdres = "INSERT INTO adres (straatnaam, postcode, huisnummer, plaats)
                     VALUES (:straatnaam, :postcode, :huisnummer, :plaats)";

        $stmtAdres = $pdo->prepare($sqlAdres);
        $stmtAdres->execute([
            ':straatnaam' => $straatnaam,
            ':postcode' => $postcode,
            ':huisnummer' => $huisnummer,
            ':plaats' => $plaats
        ]);

        $addressId = $pdo->lastInsertId();

        $sqlLeerling = "INSERT INTO leerlingen 
                        (voornaam, tussenvoegsel, achternaam, adresid, telefoonnummer, loginid, rijinstructeurid)
                        VALUES
                        (:voornaam, :tussenvoegsel, :achternaam, :adresid, :telefoon, :loginid, :rijinstructeurid)";

        $stmtLeerling = $pdo->prepare($sqlLeerling);
        $stmtLeerling->execute([
            ':voornaam' => $firstName,
            ':tussenvoegsel' => $tussenvoegsel,
            ':achternaam' => $lastName,
            ':adresid' => $addressId,
            ':telefoon' => $phone,
            ':loginid' => $loginId,
            ':rijinstructeurid' => $rijinstructeurid
        ]);

        $pdo->commit();

        unset($_SESSION['old']);

        $_SESSION['success'] = "Registratie geslaagd!";
        header("Location: ../index.php?page=login");
        exit();

    } catch (Exception $e) {

        $pdo->rollBack();

        $_SESSION['error'] = "Fout bij registratie.";
        $_SESSION['old'] = $_POST;

        header("Location: ../index.php?page=registreren");
        exit();
    }
}