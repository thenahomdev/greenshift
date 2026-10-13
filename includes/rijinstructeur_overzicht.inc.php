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
}

$sql = "
    SELECT 
        r.*, 
        l.email,
        COUNT(leerling.leerlingid) AS aantal_leerlingen
    FROM rijinstructeurs r
    LEFT JOIN login l ON r.loginid = l.loginid
    LEFT JOIN leerlingen leerling 
        ON leerling.rijinstructeurid = r.rijinstructeurid
      WHERE r.status != 0
    GROUP BY r.rijinstructeurid
";

$stmt = $pdo->prepare($sql);
$stmt->execute();
$rijinstructeurs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container">
    <div class="add-btnheader">
        <span class="add-btn" onclick="location.href='index.php?page=rijinstructeur_toevoegen'">Toevoegen</span>
    </div>

    <table class="instructeur-table">
        <thead class="table-head">
        <tr class="table-row">
            <th class="table-header">Naam</th>
            <th class="table-header">Leerlingen</th>
            <th class="table-header">Email</th>
            <th colspan="2" class="table-header"></th>
        </tr>
        </thead>

        <tbody class="table-body">
        <?php foreach($rijinstructeurs as $rijinstructeur){ ?>
            <tr class="table-row">
                <td class="table-data">
                    <?= $rijinstructeur['voornaam'] ?>
                    <?= $rijinstructeur['tussenvoegsel'] ?>
                    <?= $rijinstructeur['achternaam'] ?>
                </td>

                <td class="table-data">
                    <?= $rijinstructeur['aantal_leerlingen'] ?>
                </td>

                <td class="table-data">
                    <?= $rijinstructeur['email'] ?>
                </td>

                <td class="table-data">
                    <span class="action-btn"
                          onclick="location.href='index.php?page=rijinstructeur_aanpassen&rijinstructeurid=<?= htmlspecialchars($rijinstructeur['rijinstructeurid']) ?>'">
                        Bewerken
                    </span>

                    <span onClick="onClickFn(<?= $rijinstructeur['rijinstructeurid'] ?>)" class="action-btn">Verwijderen</span>
                </td>

            </tr>
        <?php } ?>
        </tbody>
    </table>
</div>
<script>

    function onClickFn(id) {
        if(confirm('weet je het zeker ?')) {
            console.log('yes');
            window.location.href = 'php/rijinstructeur_verwijderen.php?id=' + id
        } else {
            console.log('no');
        }
    }
</script>