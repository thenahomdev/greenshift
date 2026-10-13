<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-error">
        <?= e($_SESSION['error']) ?>
    </div>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success">
        <?= e($_SESSION['success']) ?>
    </div>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['melding'])): ?>
    <div class="alert alert-melding">
        <?= e($_SESSION['melding']) ?>
    </div>
    <?php unset($_SESSION['melding']); ?>
<?php endif; ?>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const alerts = document.querySelectorAll('.alert');

        alerts.forEach(alert => {
            setTimeout(() => {
                alert.style.opacity = "0";

                setTimeout(() => {
                    alert.remove();
                }, 600);

            }, 4000);
        });
    });
</script>