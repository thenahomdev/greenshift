<?php
include __DIR__ . '/../php/rijles_inplannen.php';
include __DIR__ . '/alerts.inc.php';
?>

<div class="container">

    <div class="top-bar">

        <h1>Rijles Inplannen</h1>

        <div class="week-navigation">

            <a href="index.php?page=rijles_inplannen&week=<?php echo $prevWeek; ?>" class="week-btn">
                ← Vorige week
            </a>

            <span class="week-text">
                Week <?php echo $weekNumber; ?>
            </span>

            <a href="index.php?page=rijles_inplannen&week=<?php echo $nextWeek; ?>" class="week-btn">
                Volgende week →
            </a>

        </div>

    </div>

    <?php if($volgendeWeekGeblokkeerd): ?>

        <div style="text-align:center;padding:120px 20px;font-size:18px;">
            De roosters zijn nog niet definitief.<br><br>
            Vanaf <strong><?php echo $releaseDate->format("d F"); ?></strong> kunt u weer lessen boeken.
        </div>

    <?php else: ?>

        <table class="rooster">

            <thead>
            <tr>
                <th>Tijd</th>

                <th>Maandag<br><span class="datum"><?php echo $monday->format("j F"); ?></span></th>
                <th>Dinsdag<br><span class="datum"><?php echo $tuesday->format("j F"); ?></span></th>
                <th>Woensdag<br><span class="datum"><?php echo $wednesday->format("j F"); ?></span></th>
                <th>Donderdag<br><span class="datum"><?php echo $thursday->format("j F"); ?></span></th>
                <th>Vrijdag<br><span class="datum"><?php echo $friday->format("j F"); ?></span></th>

            </tr>
            </thead>

            <tbody>

            <?php

            $start = 8;
            $end = 20;

            for ($i = $start; $i < $end; $i++) {

                $startTime = str_pad($i, 2, "0", STR_PAD_LEFT) . ":00";
                $endTime = str_pad($i + 1, 2, "0", STR_PAD_LEFT) . ":00";

                echo "<tr>";
                echo "<td>$startTime - $endTime</td>";

                for ($d = 0; $d < 5; $d++) {

                    $dag = clone $monday;
                    $dag->modify("+$d day");

                    $datum = $dag->format("Y-m-d");
                    $key = $datum . "_" . $startTime;

                    $nu = new DateTime();
                    $lesStart = new DateTime($datum . " " . $startTime);

                    $verleden = ($lesStart < $nu);

                    $sluitMoment = clone $lesStart;
                    $sluitMoment->modify("-1 hour");

                    $teLaatOmTeBoeken = ($nu >= $sluitMoment);

                    echo "<td>";

                    if (isset($bookedSlots[$key])) {

                        if ($bookedSlots[$key] == $leerlingId) {

                            if($verleden){

                                echo "<button style='background:#7f8c8d;color:white;border:none;padding:8px;width:100%;cursor:not-allowed;' disabled>
Les voldaan
</button>";

                            } else {
                                ?>

                                <form method="POST">

                                    <input type="hidden" name="datum" value="<?php echo $datum; ?>">
                                    <input type="hidden" name="tijd" value="<?php echo $startTime; ?>">

                                    <button type="submit" name="annuleer_les"
                                            style="background:#e74c3c;color:white;border:none;padding:8px;width:100%;">
                                        Annuleer
                                    </button>

                                </form>

                                <?php
                            }

                        } else {

                            echo "<button style='background:#3498db;color:white;border:none;padding:8px;width:100%;cursor:not-allowed;' disabled>
Bezet
</button>";

                        }

                    }
                    elseif (isset($availableSlots[$key])) {

                        if($verleden){

                            echo "<button style='background:#bdc3c7;color:white;border:none;padding:8px;width:100%;cursor:not-allowed;' disabled>
Verlopen
</button>";

                        }
                        elseif($teLaatOmTeBoeken){

                            echo "<button style='background:#95a5a6;color:white;border:none;padding:8px;width:100%;cursor:not-allowed;' disabled>
Gesloten
</button>";

                        }
                        elseif(!$boekenToegestaan){

                            echo "<button style='background:gray;color:white;border:none;padding:8px;width:100%;cursor:not-allowed;' disabled>
Nog niet open
</button>";

                        }
                        else {
                            ?>

                            <form method="POST">

                                <input type="hidden" name="datum" value="<?php echo $datum; ?>">
                                <input type="hidden" name="tijd" value="<?php echo $startTime; ?>">

                                <button type="submit" name="boek_les"
                                        style="background:#2ecc71;color:white;border:none;padding:8px;width:100%;">
                                    Boek
                                </button>

                            </form>

                            <?php
                        }

                    }
                    else {

                        echo "-";

                    }

                    echo "</td>";

                }

                echo "</tr>";

            }

            ?>

            </tbody>

        </table>

    <?php endif; ?>

</div>