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

$sql='SELECT * FROM pakketten WHERE status = 1 ORDER BY prijs ASC ';
$stmt = $pdo->prepare($sql);
$stmt->execute();

$lespakket = $stmt->fetchAll();

?>
<a href="index.php?page=lespakket_toevoegen" class="lespakket_button">
    Toevoegen
</a>
<div class="paket-container">

    <?php if (!empty($melding)) { ?>
        <div id="success-melding" style="
            background:#d4edda;
            padding:12px;
            margin-bottom:15px;
            border-radius:6px;
            color:#155724;
            font-weight:bold;">
            <?= htmlspecialchars($melding); ?>
        </div>

        <script>
            setTimeout(function () {
                var el = document.getElementById('success-melding');
                if (el) el.style.display = "none";
            }, 5000);
        </script>
    <?php } ?>


    <?php if (!empty($lespakket)) { ?>

        <?php foreach ($lespakket as $row) { ?>

            <div class="pakket-card">

                <div>
                    <h3><?= htmlspecialchars($row['naam']); ?></h3>
                    <p>Aantal lessen: <?= htmlspecialchars($row['aantal_lessen']); ?></p>

                    <p class="prijs">
                        €<?= number_format($row['prijs'], 2, ',', '.'); ?>
                    </p>
                </div>


                <div>
                    <a href="index.php?page=lespakket_aanpassen&id=<?= $row['pakkettenid']; ?>"
                       class="pakketten-button">
                        Aanpassen
                    </a>
                </div>


                <div>

                    <?php if ($row['aantal_lessen'] > 1) { ?>

                        <!-- Alleen voor pakketten -->
                        <form method="POST" action="php/lespakket_overzicht.php"
                              style="display:inline;"
                              onsubmit="return confirm('Weet je zeker dat je dit pakket wilt verwijderen?');">

                            <input type="hidden"
                                   name="delete_id"
                                   value="<?= $row['pakkettenid']; ?>">

                            <button type="submit" class="pakketten-button">
                                Verwijderen
                            </button>
                        </form>

                    <?php } else { ?>

                        <!-- Voor losse les -->
                        <button class="pakketten-button"
                                style="background:#ccc; cursor:not-allowed;"
                                disabled
                                title="Losse lessen kunnen niet verwijderd worden">
                            Niet verwijderbaar
                        </button>

                    <?php } ?>

                </div>

            </div>

        <?php } ?>

    <?php } else { ?>

        <p>Geen pakketten gevonden.</p>

    <?php } ?>

</div>