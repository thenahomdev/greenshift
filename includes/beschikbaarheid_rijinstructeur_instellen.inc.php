<?php

if(isset($_SESSION['role'])){
    if($_SESSION['role'] != "rijinstructeuren"){
        header('location: index.php?page=login');
        die();
    }
}else{
    header('location: index.php?page=login');
    die();
}

date_default_timezone_set("Europe/Amsterdam");

if(isset($_GET['week'])){
    $weekNumber = (int)$_GET['week'];
} else {
    $weekNumber = date("W");
}

$year             = date("Y");
$loginid          = $_SESSION['loginid'];
$disabled         = "";
$actieveLessen    = [];
$nu               = new DateTime();
$huidigeWeek      = (int)$nu->format("W");
$huidigeDag       = (int)$nu->format("N");
$huidigeUur       = (int)$nu->format("H");
$start            = 8;
$end              = 20;

$monday = new DateTime();
$monday->setISODate($year, $weekNumber);

$tuesday = clone $monday;
$tuesday->modify('+1 day');

$wednesday = clone $monday;
$wednesday->modify('+2 day');

$thursday = clone $monday;
$thursday->modify('+3 day');

$friday = clone $monday;
$friday->modify('+4 day');

$dagen = [$monday, $tuesday, $wednesday, $thursday, $friday];

if ($weekNumber <= 1)
{
    $prevWeek = 52;
}else{
    $prevWeek = $weekNumber - 1;
}
if ($weekNumber >= 52)
{
    $nextWeek = 1;
}
else
{
    $nextWeek = $weekNumber + 1;
}

if($weekNumber <= $huidigeWeek)
{
    $disabled = "disabled";
}
elseif($weekNumber == $huidigeWeek + 1 && $huidigeDag >= 6)
{
    $disabled = "disabled";
}

$sql = "SELECT * FROM rijinstructeurs WHERE loginid = '" . $loginid . "'";
$result = $pdo->query($sql);
$user = $result->fetch();

$rijinstructeurid = $user['rijinstructeurid'];


$sql = "SELECT datum, tijd FROM instructeur_beschikbaarheid WHERE rijinstructeurid = :rijinstructeurid AND status = 1";
$stmt = $pdo->prepare($sql);
$stmt->execute([':rijinstructeurid' => $rijinstructeurid]);

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $actieveLessen[$row['datum']][$row['tijd']] = true;
}

?>
<div class="top-bar">
    <h1>Rijles Inplannen</h1>

    <div class="week-navigation">
        <a href="index.php?page=beschikbaarheid_rijinstructeur_instellen&week=<?= $prevWeek; ?>" class="week-btn">← Vorige week</a>

        <span class="week-text">
                Week <?= $weekNumber; ?>
            </span>

        <a href="index.php?page=beschikbaarheid_rijinstructeur_instellen&week=<?= $nextWeek; ?>" class="week-btn">Volgende week →</a>
    </div>

</div>

<form method="POST" action="php/beschikbaarheid_rijinstructeur_instellen.php">
    <input type="hidden" name="week" value="<?= $weekNumber; ?>">
    <input type="hidden" name="monday" value="<?= $monday->format("Y-m-d"); ?>">
    <input type="hidden" name="friday" value="<?= $friday->format("Y-m-d"); ?>">

    <table class="rooster">
        <thead>
        <tr>
            <th>Tijd</th>
            <?php foreach($dagen as $dagObj){ ?>
                <th><?= $dagObj->format("l"); ?><br>
                    <span class="datum"><?= $dagObj->format("Y-m-d"); ?></span>
                </th>
            <?php } ?>
        </tr>
        </thead>
        <tbody>
        <?php for($i = $start; $i < $end; $i++){
            $startTime = str_pad($i,2,"0",STR_PAD_LEFT).":00";
            $endTime = str_pad($i+1,2,"0",STR_PAD_LEFT).":00";
            ?>
            <tr>
                <td><?= $startTime . " - " . $endTime; ?></td>
                <?php foreach($dagen as $dagObj){
                    $datum = $dagObj->format("Y-m-d");
                    ?>
                    <td>
                        <?php
                        $checked = '';

                        if (isset($actieveLessen[$datum][str_pad($i, 2, "0", STR_PAD_LEFT) . ":00:00"])) {
                            $checked = 'checked';
                        }
                        ?>
                        <input type="checkbox" name="beschikbaarheid[<?= $datum; ?>][<?= $i; ?>]" value="1" <?= $checked;?> <?=$disabled?>>
                    </td>
                <?php } ?>
            </tr>
        <?php } ?>
        </tbody>
    </table>
    <button class="actie_button" type="submit"  >Opslaan</button>
</form>