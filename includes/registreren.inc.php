<?php
function check($name) {
    return isset($_SESSION['data'][$name]) ? $_SESSION['data'][$name] : '';
}

?>

<form action="php/registreren.php" method="post" class="auth-form">

    <div class="auth-container">

        <h1>Registreren</h1>

        <div class="form-group">
            <label>Voornaam *</label>
            <?php check('firstName'); ?>
            <input type="text"
                   name="firstName"
                   class="auth-input"
                   placeholder="Voer uw voornaam in"
                   value="<?= check('firstName'); ?>" required>
        </div>

        <div class="form-group">
            <label>Tussenvoegsel</label>
            <input type="text"
                   name="tussenvoegsel"
                   class="auth-input"
                   placeholder="Voer uw tussenvoegsel in"
                   value="<?= check('tussenvoegsel'); ?>"
            >
        </div>

        <div class="form-group">
            <label>Achternaam *</label>
            <input type="text"
                   name="lastName"
                   class="auth-input"
                   placeholder="Voer uw achternaam in"
                   value="<?= check('lastName'); ?>"
                   required>
        </div>

        <div class="form-group">
            <label>Straatnaam *</label>
            <input type="text"
                   name="straatnaam"
                   class="auth-input"
                   placeholder="Voer uw straatnaam in"
                   value="<?= check('straatnaam'); ?>"
                   required>
        </div>

        <div class="form-group">
            <label>Huisnummer *</label>
            <input type="text"
                   name="huisnummer"
                   class="auth-input"
                   placeholder="Huisnummer"
                   value="<?= check('huisnummer'); ?>"
                   required>
        </div>

        <div class="form-group">
            <label>Postcode *</label>
            <input type="text"
                   name="postcode"
                   class="auth-input"
                   placeholder="Postcode"
                   value="<?= check('postcode'); ?>"
                   required>
        </div>

        <div class="form-group">
            <label>Plaats *</label>
            <input type="text"
                   name="plaats"
                   class="auth-input"
                   placeholder="Plaats"
                   value="<?= check('plaats'); ?>"
                   required>
        </div>

        <div class="form-group">
            <label>Telefoonnummer *</label>
            <input type="text"
                   name="phone"
                   class="auth-input"
                   placeholder="Telefoonnummer"
                   value="<?= check('phone'); ?>"
                   required>
        </div>

        <div class="form-group full">
            <label>E-mailadres *</label>
            <input type="email"
                   name="email"
                   class="auth-input"
                   placeholder="E-mailadres"
                   value="<?= check('email'); ?>"
                   required>
        </div>

        <div class="form-group">
            <label>Wachtwoord *</label>
            <input type="password"
                   name="password"
                   class="auth-input"
                   placeholder="Wachtwoord"
                   required>
        </div>

        <div class="form-group">
            <label>Herhaal wachtwoord *</label>
            <input type="password"
                   name="passwordRepeat"
                   class="auth-input"
                   placeholder="Herhaal wachtwoord"
                   required>
        </div>

        <div class="form-group full">
            <label> Rijinstructeur *</label>
            <select name="rijinstructeurid" class="auth-address" required>

                <option value="">-- Kies uw rijinstructeur --</option>

                <?php
                $sql = "SELECT * FROM rijinstructeurs WHERE status = 1 ORDER BY voornaam ASC";
                $stmt = $pdo->prepare($sql);
                $stmt->execute();

                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

                    $rijinstructeurid = $row['rijinstructeurid'];

                    $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM leerlingen WHERE rijinstructeurid = :rijinstructeurid");
                    $stmtCheck->execute([':rijinstructeurid' => $rijinstructeurid]);
                    $totalStudents = $stmtCheck->fetchColumn();

                    $selected = ($old['rijinstructeurid'] ?? '') == $rijinstructeurid ? 'selected' : '';

                    echo "<option value='{$rijinstructeurid}' $selected>
                    {$row['voornaam']} {$row['tussenvoegsel']} {$row['achternaam']} ({$totalStudents} studenten)
                    </option>";
                }
                ?>

            </select>

        </div>

        <button type="submit" class="auth-button">
            Registreren
        </button>

    </div>
</form>

<?php
unset($_SESSION['data']);
?>