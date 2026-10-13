<?php
if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] != "admin") {
        header('location: index.php?page=login');
        die();
    }
} else {
    header('location: index.php?page=login');
    die();
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    $sql = "SELECT * FROM pakketten WHERE pakkettenid = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id' => $id]);

    $lespakket = $stmt->fetch();

if (!$lespakket) {
    $_SESSION['error'] = "Pakket niet gevonden.";
    header("Location: index.php?page=lespakket_overzicht");
    exit();
}

}else{
    $_SESSION['error']= "Pakket kan niet vinden.";
    header("Location: ../index.php?page=lespakket_overzicht");
    exit();
}
?>
<form method="post" action="php/lespakket_aanpassen.php" class="aanpassen">
    <div class="info">
        <input type="hidden" name="lespakketid" value="<?= $lespakket['pakkettenid'] ?>">
        <div>

            <label>Naam:</label>
            <input type="text" name="naam" value="<?=$lespakket['naam'] ?>" required>

        </div>
        <div>

            <label>Prijs:</label>
            <input type="text" name="prijs" value="<?=$lespakket['prijs'] ?>" required>

        </div>
        <div>

            <label>Aantal lessen:</label>
            <input type="number" name="aantal" value="<?=$lespakket['aantal_lessen'] ?>" required>

        </div>

        <div class="button-group">
            <button type="submit" class="btn-opslaan">Opslaan</button>
            <a href="index.php?page=lespakket_overzicht" class="btn-terug">Terug</a>
        </div>

    </div>
</form>