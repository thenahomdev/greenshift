<?php
if(isset($_SESSION['role']))
{
    if($_SESSION['role'] != "admin")
    {
        header('location: index.php?page=login');
        die();
    }
}
else
{
    header('location: index.php?page=login');
    die();
}?>

<div class="form-wrapper">
    <div class="form-card">

        <h2>Nieuw Lespakket</h2>
        <p class="subtitle">Voeg een lespakket toe.</p>

        <?php if (!empty($melding)): ?>
            <div class="alert <?php echo $melding_type; ?>">
                <?php echo $melding; ?>
            </div>
        <?php endif; ?>

        <!-- BELANGRIJK: juiste action voor router -->
        <form method="POST" action="php/lespakket_toevoegen.php" class="form">

            <div class="field">
                <label>Naam pakket</label>
                <input type="text" name="naam" required
                       value="<?php echo htmlspecialchars($_POST['naam'] ?? ''); ?>">
            </div>

            <div class="field">
                <label>Prijs (€)</label>
                <input type="text" name="prijs" required
                       value="<?php echo htmlspecialchars($_POST['prijs'] ?? ''); ?>">
            </div>

            <div class="field">
                <label>Aantal lessen</label>
                <input type="number" name="aantal_lessen" required
                       value="<?php echo htmlspecialchars($_POST['aantal_lessen'] ?? ''); ?>">
            </div>

            <div class="field">
                <label>Status</label>
                <select name="status">
                    <option value="1">Actief</option>
                    <option value="0">Inactief</option>
                </select>
            </div>

            <button type="submit" name="toevoegen" class="btn-primary">
                Lespakket toevoegen
            </button>

        </form>

    </div>
</div>