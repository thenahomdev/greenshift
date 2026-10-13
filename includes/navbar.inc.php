<?php

include __DIR__ . '/../config/connection.php';

if(isset($_SESSION['role'])){
    if($_SESSION['role'] == 'admin')
    {
        ?>
        <div class="navbar-header">

            <img class="logo" src="foto/greenshift.png">
            <div class="navbar-header-right">
                <a href="index.php?page=lespakket_overzicht"> Pakketten Beheer </a>
                <a href="index.php?page=rijinstructeur_overzicht"> Rijinstructeuren Beheer </a>
                <a href="Php/Loguit.php"> Loguit </a>
            </div>

        </div>
        <?php
    }
    elseif($_SESSION['role'] == 'rijinstructeuren')
    {
        ?>
        <div class="navbar-header">

            <img class="logo" src="foto/greenshift.png">
            <div class="navbar-header-right">
                <a href="index.php?page=rijinstructeur_dagplanning"> Dagrooster </a>
                <a href="index.php?page=beschikbaarheid_rijinstructeur_instellen"> Weekrooster </a>
                <a href="Php/Loguit.php"> Loguit </a>
            </div>

        </div>
        <?php
    }
    elseif($_SESSION['role'] == 'leerlingen'){

        if(!isset($_SESSION['loginid'])){
            $lestegoed = 0;
        }
        else {

            $loginid = $_SESSION['loginid'];

            /* LEERLING ID OPHALEN */

            $stmt = $pdo->prepare("
                SELECT leerlingid
                FROM leerlingen
                WHERE loginid = :loginid
                LIMIT 1
            ");

            $stmt->execute([
                    'loginid' => $loginid
            ]);

            $leerling = $stmt->fetch(PDO::FETCH_ASSOC);
            $leerlingid = $leerling['leerlingid'];


            /* TOTAAL GEKOCHTE LESSEN */

            $stmt = $pdo->prepare("
                SELECT COALESCE(SUM(p.aantal_lessen),0)
                FROM pakket_geschiedenis ph
                JOIN pakketten p ON ph.pakketid = p.pakkettenid
                WHERE ph.leerlingid = :leerlingid
            ");

            $stmt->execute([
                    'leerlingid'=>$leerlingid
            ]);

            $gekochteLessen = $stmt->fetchColumn();


            /* ACTIEVE GEBOEKTE LESSEN */

            $stmt = $pdo->prepare("
                SELECT COUNT(*)
                FROM rijlessen
                WHERE leerlingid = :leerlingid
                AND status = 1
            ");

            $stmt->execute([
                    'leerlingid'=>$leerlingid
            ]);

            $actieveBoekingen = $stmt->fetchColumn();


            /* RESTEREND LESTEGOED */

            $lestegoed = $gekochteLessen - $actieveBoekingen;

            if($lestegoed < 0){
                $lestegoed = 0;
            }
        }
        ?>

        <div class="navbar-header">

            <img class="logo" src="foto/greenshift.png">

            <div class="navbar-header-right">

                <a href="index.php?page=lespakketkopen"> Lespakket kopen </a>
                <a href="index.php?page=rijles_inplannen"> Rijles inplannen </a>
                <a href="index.php?page=lesgeschiedenis"> Geschiedenis </a>
                <a href="index.php?page=factuur"> Factuur </a>
                <a href="Php/Loguit.php"> Loguit </a>

                <span class="navbar-lestegoed">
                    Lestegoed: <?= $lestegoed ?> lessen
                </span>

            </div>

        </div>

        <?php
    }
}
else{
    ?>
    <div class="navbar-header">

        <img class="logo" src="foto/greenshift.png">

        <div class="navbar-header-right">
            <a href="#home">Home</a>
            <a href="#contact">Contact</a>
            <a href="#about">About</a>
            <a href="index.php?page=registreren"> Registreren </a>
            <a href="index.php?page=login"> Inloggen </a>
        </div>

    </div>
    <?php
}
?>