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
?>

<div class="alert-container">
    <?php
    if (isset($_SESSION['errors']) && !empty($_SESSION['errors'])) {
        foreach ($_SESSION['errors'] as $error) {
            echo '<div class="alert alert-error">' . htmlspecialchars($error) . '</div>';
        }
        unset($_SESSION['errors']);
    }

    if (isset($_SESSION['success'])) {
        echo '<div class="alert alert-success">' . htmlspecialchars($_SESSION['success']) . '</div>';
        unset($_SESSION['success']);
    }
    ?>
</div>

<form action="php/rijinstructeur_toevoegen.php" method="post" class="auth-form">

    <div class="auth-container">
        <h1>Registreren</h1>

        <label>Voornaam *</label>
        <input type="text" name="firstName" class="auth-input" placeholder="Voer uw voornaam in" required>

        <label>Tussenvoegsel</label>
        <input type="text" name="tussenvoegsel" class="auth-input" placeholder="Voer uw tussenvoegsel in">

        <label>Achternaam *</label>
        <input type="text" name="lastName" class="auth-input" placeholder="Voer uw achternaam in" required>

        <label>E-mailadres *</label>
        <input type="email" name="email" class="auth-input" placeholder="Voer uw e-mailadres in" required>

        <label>Wachtwoord *</label>
        <input type="password" name="password" class="auth-input" placeholder="Voer uw wachtwoord in" required>

        <label>Herhaal wachtwoord *</label>
        <input type="password" name="passwordRepeat" class="auth-input" placeholder="Herhaal uw wachtwoord" required>

        <button type="submit" class="auth-button">Registreren</button>
    </div>
</form>
