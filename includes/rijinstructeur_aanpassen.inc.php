<?php
if(isset($_SESSION['role'])){
    if($_SESSION['role'] != "admin"){
        header('location: index.php?page=login');
        die();
    }
}else{
    header('location: index.php?page=login');
    die();
}
$rijinstructeurid = $_GET["rijinstructeurid"];

$sql = "SELECT rijinstructeur.rijinstructeurid, rijinstructeur.voornaam, rijinstructeur.tussenvoegsel, rijinstructeur.achternaam, login.email
        FROM rijinstructeurs rijinstructeur
        LEFT JOIN login login 
            ON rijinstructeur.loginid = login.loginid
        WHERE rijinstructeur.rijinstructeurid = :rijinstructeurid";
$stmt = $pdo->prepare($sql);
$stmt->execute(['rijinstructeurid' => $rijinstructeurid]);

$rijinstructeurs = $stmt->fetch(PDO::FETCH_ASSOC);

?>

<div class="container">

    <h1>✏️ Rijinstructeur aanpassen</h1>

    <div class="card">

        <form action="php/rijinstructeur_aanpassen.php" method="post"
              class="green-form"
              onsubmit="return confirm('Weet u zeker dat u de wijzigingen wilt opslaan?');">


            <input type="text" name="voornaam"
                   value="<?= htmlspecialchars($rijinstructeurs['voornaam']) ?>"
                   placeholder="Voornaam" required>


            <input type="text" name="tussenvoegsel"
                   value="<?= htmlspecialchars($rijinstructeurs['tussenvoegsel']) ?>"
                   placeholder="Tussenvoegsel">

            <input type="text" name="achternaam"
                   value="<?= htmlspecialchars($rijinstructeurs['achternaam']) ?>"
                   placeholder="Achternaam" required>

            <input type="email" name="email"
                   value="<?= htmlspecialchars($rijinstructeurs['email']) ?>"
                   placeholder="Email" required>

            <hr>

            <h3>Nieuw wachtwoord (optioneel)</h3>

            <input type="password" name="wachtwoord"
                   placeholder="Nieuw wachtwoord">

            <input type="password" name="wachtwoord2"
                   placeholder="Herhaal wachtwoord">

            <input type="hidden" name="id" value="<?= htmlspecialchars($rijinstructeurs['rijinstructeurid']) ?>">
            <button name="opslaan">Opslaan</button>

        </form>

        <br>

        <a href="index.php?page=rijinstructeur_overzicht">⬅ Terug naar overzicht</a>

    </div>
</div>