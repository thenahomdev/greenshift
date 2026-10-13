<?php

$sql = 'SELECT * FROM pakketten WHERE status = 1 ORDER BY prijs ASC';
$stmt = $pdo->prepare($sql);
$stmt->execute();

$lespakket = $stmt->fetchAll();
?>

<div class="paket-container">

    <?php if (!empty($lespakket)) {
        foreach ($lespakket as $row) { ?>

            <div class="pakket-card">
                <h3><?= htmlspecialchars($row['naam']); ?></h3>
                <p>Aantal lessen: <?= htmlspecialchars($row['aantal_lessen']); ?></p>

                <p class="prijs">
                    €<?= number_format($row['prijs'], 2, ',', '.'); ?>
                </p>

                <div>
                    <span
                            onclick="onClickFn(<?= $row['pakkettenid']; ?>, '<?= htmlspecialchars(addslashes($row['naam'])); ?>')"
                            class="pakketten-button">
                        Kopen
                    </span>
                </div>
            </div>

        <?php }
    } else { ?>
        <p>Geen pakketten gevonden.</p>
    <?php } ?>

</div>

<script>
    function onClickFn(id, naam) {
        if (confirm('Weet je het zeker dat je "' + naam + '" wilt kopen?')) {
            window.location.href = 'Php/lespakketkopen.php?id=' + id;
        }
    }
</script>