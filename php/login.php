<?php
session_start();
include __DIR__ . '/../config/connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST')
{
    $email = $_POST['email'];
    $password = $_POST['password'];


    if (empty($email) || empty($password)) {
        $_SESSION['error'] = "Vul alle velden in!";
    }

    try {
        $sql = "SELECT loginid, email, wachtwoord, roleid FROM login WHERE email = :email AND status = 1";
        $statement = $pdo->prepare($sql);
        $statement->execute([':email' => $email]);

        $user = $statement->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['wachtwoord'])) {

            session_regenerate_id(true);

            $_SESSION['loginid'] = $user['loginid'];
            $_SESSION['role'] = $user['roleid'];

            if ($user['roleid'] == 1) {
                $_SESSION['role'] = 'admin';
                $_SESSION['success'] = "success ingelogd";
                header("Location: ../index.php?page=lespakket_overzicht");
            }
            elseif ($user['roleid'] == 2) {
                $_SESSION['role'] = 'leerlingen';

                $loginid = $_SESSION['loginid'];

                $sql='    SELECT 
                        l.rijinstructeurid, 
                        l.old_rijinstructeurid,
                        r.voornaam,
                        r.tussenvoegsel,
                        r.achternaam
                    FROM leerlingen l
                    LEFT JOIN rijinstructeurs r 
                        ON l.rijinstructeurid = r.rijinstructeurid
                    WHERE l.loginid = :loginid';
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':loginid', $loginid, PDO::PARAM_INT);
                $stmt->execute();

                $check_instructeurs = $stmt->fetch();

                $naam = $check_instructeurs['voornaam'];

                if (!empty($check_instructeurs['tussenvoegsel'])) {
                    $naam .= ' ' . $check_instructeurs['tussenvoegsel'];
                }

                $naam .= ' ' . $check_instructeurs['achternaam'];

                if ( !is_null($check_instructeurs['old_rijinstructeurid']) &&
                    $check_instructeurs['old_rijinstructeurid'] != $check_instructeurs['rijinstructeurid']) {
                    $_SESSION['melding']= "Je rijinstructeur is gewijzigd! Je nieuwe rijinstructeur heet $naam.";

                    $sql = "UPDATE leerlingen 
                           SET old_rijinstructeurid = NULL 
                           WHERE loginid = :loginid";

                    $stmt = $pdo->prepare($sql);
                    $stmt->execute(['loginid' => $loginid]);
                }else {
                    $_SESSION['success'] = "success ingelogd";
                }
                header("Location: ../index.php?page=rijles_inplannen");
            }
            elseif ($user['roleid'] == 3) {
                $_SESSION['role'] = 'rijinstructeuren';
                $_SESSION['success'] = "success ingelogd";
                header("Location: ../index.php?page=beschikbaarheid_rijinstructeur_instellen");
            }
            exit();

        } else {

            $_SESSION['error'] = "Onjuiste email of wachtwoord";
            header("Location: ../index.php?page=login");
            exit();
        }

    } catch (PDOException $e) {

        $_SESSION['error'] = "Er is een probleem opgetreden. Probeer later opnieuw.";
        header("Location: ../index.php?page=login");
        exit();
    }
}
?>
