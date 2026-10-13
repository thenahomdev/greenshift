<?php
session_start();
include __DIR__ . '/../config/connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST')
{
    $firstName = $_POST['firstName'];
    $tussenvoegsel = $_POST['tussenvoegsel'];
    $lastName = $_POST['lastName'];
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $passwordRepeat = $_POST['passwordRepeat'];

    $errors = [];

    if ($password !== $passwordRepeat) {
        $errors[] = "Wachtwoorden komen niet overeen!";
    }

    if (strlen($password) < 8) {
        $errors[] = "Wachtwoord moet minimaal 8 tekens bevatten!";
    }

    $stmtCheckEmail = $pdo->prepare("SELECT COUNT(*) FROM login WHERE email = :email");
    $stmtCheckEmail->execute([':email' => $email]);
    if ($stmtCheckEmail->fetchColumn() > 0) {
        $errors[] = "Dit e-mailadres is al geregistreerd!";
    }

    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        header("Location: ../index.php?page=rijinstructeur_toevoegen");
        exit();
    }

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    try {
        $pdo->beginTransaction();

        $roleId = 3;
        $sqlLogin = "INSERT INTO login (roleid, email, wachtwoord) VALUES (:roleid, :email, :wachtwoord)";
        $stmtLogin = $pdo->prepare($sqlLogin);
        $stmtLogin->execute([
            ':roleid' => $roleId,
            ':email' => $email,
            ':wachtwoord' => $hashedPassword
        ]);
        $loginId = $pdo->lastInsertId();

        $sqlLeerling = "INSERT INTO rijinstructeurs (voornaam, tussenvoegsel, achternaam, loginid)
                        VALUES (:voornaam, :tussenvoegsel, :achternaam, :loginid)";
        $stmtLeerling = $pdo->prepare($sqlLeerling);
        $stmtLeerling->execute([
            ':voornaam' => $firstName,
            ':tussenvoegsel' => $tussenvoegsel,
            ':achternaam' => $lastName,
            ':loginid' => $loginId,
        ]);

        $pdo->commit();

        $_SESSION['success'] = "Registratie geslaagd!";
        header("Location: ../index.php?page=rijinstructeur_overzicht");
        exit();

    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['errors'] = ["Fout bij registratie: " . $e->getMessage()];
        header("Location: ../index.php?page=rijinstructeur_overzicht");
        exit();
    }
}
?>
