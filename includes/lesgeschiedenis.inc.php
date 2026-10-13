<?php
if (isset($_SESSION['role']))
{
    if ($_SESSION['role'] != "leerlingen")
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

/* Statusbepaling per rijles:
   - status = 0 in database -> les is geannuleerd
   - status = 1 + datum/tijd in verleden -> les is voltooid
   - status = 1 + datum/tijd in toekomst -> les is ingepland
 */

$loginId    = $_SESSION['loginid'];
$nu         = new DateTime();
$vandaag    = $nu->format('Y-m-d');
$huidigTijd = $nu->format('H:i');

$sql = "SELECT leerlingid FROM leerlingen WHERE loginid = :loginId LIMIT 1";
$stmt = $pdo->prepare($sql);
$stmt->execute(['loginId' => $loginId]);
$leerling   = $stmt->fetch();
$leerlingId = $leerling['leerlingid'];

$sql = "
    SELECT r.rijlesid, r.datum, r.tijd, r.status, i.voornaam AS instructeurVoornaam, i.tussenvoegsel AS instructeurTussenvoegsel, i.achternaam AS instructeurAchternaam
    FROM rijlessen r
    JOIN rijinstructeurs i ON i.rijinstructeurid = r.rijinstructeurid
    WHERE r.leerlingid = :leerlingId
    ORDER BY r.datum DESC, r.tijd DESC
";
$stmt = $pdo->prepare($sql);
$stmt->execute(['leerlingId' => $leerlingId]);
$rijlessen = $stmt->fetchAll();

$sql = "
    SELECT pg.geschiedenisid, pg.datum AS aankoopdatum, pg.prijs, p.pakkettenid, p.naam AS pakketNaam, p.aantal_lessen
    FROM pakket_geschiedenis pg
    JOIN pakketten p ON p.pakkettenid = pg.pakketid
    WHERE pg.leerlingid = :leerlingId
    ORDER BY pg.datum DESC
";
$stmt = $pdo->prepare($sql);
$stmt->execute(['leerlingId' => $leerlingId]);
$gekochtePacketten = $stmt->fetchAll();

function bepaalLesStatus($les, $vandaag, $huidigTijd)
{
    if ($les['status'] == 0)
    {
        return 'geannuleerd';
    }

    if ($les['datum'] < $vandaag)
    {
        return 'voltooid';
    }

    if ($les['datum'] == $vandaag && $les['tijd'] <= $huidigTijd)
    {
        return 'voltooid';
    }

    return 'ingepland';
}

function instructeurNaam($les)
{
    $naam = $les['instructeurVoornaam'];
    if (!empty($les['instructeurTussenvoegsel']))
    {
        $naam .= ' ' . $les['instructeurTussenvoegsel'];
    }
    $naam .= ' ' . $les['instructeurAchternaam'];
    return htmlspecialchars($naam);
}
?>
<nav class="tabbalk">
    <button class="tabbalk__tab tabbalk__tab--actief" onclick="setTab(this, 'alle')">Alle Lessen</button>
    <button class="tabbalk__tab" onclick="setTab(this, 'ingepland')">Geplande Lessen</button>
    <button class="tabbalk__tab" onclick="setTab(this, 'voltooid')">Voltooide Lessen</button>
    <button class="tabbalk__tab" onclick="setTab(this, 'geannuleerd')">Geannuleerde Lessen</button>
    <button class="tabbalk__tab" onclick="setTab(this, 'pakketten')">Gekochte Pakketten</button>
</nav>


<main class="pagina-inhoud">

    <section class="sectie" id="sectie-lessen">
        <div class="sectie__header">
            <div class="sectie__icoon">
                <svg viewBox="0 0 24 24"><path d="M18.92 6.01C18.72 5.42 18.16 5 17.5 5h-11c-.66 0-1.21.42-1.42 1.01L3 12v8c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-1h12v1c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-8l-2.08-5.99zM6.5 16c-.83 0-1.5-.67-1.5-1.5S5.67 13 6.5 13s1.5.67 1.5 1.5S7.33 16 6.5 16zm11 0c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5zM5 11l1.5-4.5h11L19 11H5z"/></svg>
            </div>
            <h2 class="sectie__titel">Rijlessen</h2>
        </div>

        <?php if (empty($rijlessen))
        { ?>
            <p class="geen-resultaat-tekst">Je hebt nog geen rijlessen gepland.</p>
        <?php }
        else
        { ?>
            <?php foreach ($rijlessen as $les)
            {
                $lesStatus = bepaalLesStatus($les, $vandaag, $huidigTijd);
                $dagNummer = date('d', strtotime($les['datum']));
                $maandAfko = date('M', strtotime($les['datum']));
                $beginTijd = date('H:i', strtotime($les['tijd']));
                $eindTijd  = date('H:i', strtotime($les['tijd'] . ' +1 hour'));
                ?>
                <div class="leskaart" data-status="<?= $lesStatus ?>">
                    <div class="leskaart__datum">
                        <div class="leskaart__datum-dag"><?= $dagNummer ?></div>
                        <div class="leskaart__datum-maand"><?= $maandAfko ?></div>
                    </div>
                    <div class="leskaart__scheidingslijn"></div>
                    <div class="leskaart__info">
                        <div class="leskaart__tijd"><?= $beginTijd ?> - <?= $eindTijd ?></div>
                        <div class="leskaart__instructeur">
                            Instructeur: <span class="leskaart__instructeur-naam"><?= instructeurNaam($les) ?></span>
                        </div>
                    </div>
                    <div class="leskaart__status">
                        <?php if ($lesStatus == 'ingepland')
                        { ?>
                            <span class="statusbadge statusbadge--ingepland">Ingepland</span>
                            <a href="index.php?page=rijles_inplannen" class="wijzigenknop">Wijzigen</a>
                        <?php }
                        elseif ($lesStatus == 'voltooid')
                        { ?>
                            <span class="statusbadge statusbadge--voltooid">Voltooid</span>
                        <?php }
                        elseif ($lesStatus == 'geannuleerd')
                        { ?>
                            <span class="statusbadge statusbadge--geannuleerd">Geannuleerd</span>
                        <?php } ?>
                    </div>
                </div>
            <?php } ?>
        <?php } ?>
    </section>

    <section class="sectie sectie--vertraagd" id="sectie-pakketten" style="display: none;">
        <div class="sectie__header">
            <div class="sectie__icoon">
                <svg viewBox="0 0 24 24"><path d="M20 4H4c-1.11 0-2 .89-2 2v12c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V6c0-1.11-.89-2-2-2zm0 14H4v-6h16v6zm0-10H4V6h16v2z"/></svg>
            </div>
            <h2 class="sectie__titel">Gekochte Pakketten</h2>
        </div>
        <div class="sectie__scheidingslijn"></div>


        <?php if (empty($gekochtePacketten))
        { ?>
            <p class="geen-resultaat-tekst">Je hebt nog geen pakketten gekocht.</p>
        <?php }
        else
        { ?>
            <div class="pakketraster">
                <?php foreach ($gekochtePacketten as $pakket)
                { ?>
                   <?php htmlspecialchars($pakket['pakketNaam']) ?>
                    <div class="pakketkaart">
                        <div class="pakketkaart__naam">
                            <?= htmlspecialchars($pakket['pakketNaam']) ?>
                        </div>
                        <div class="pakketkaart__meta">
                            <div class="pakketkaart__prijs">
                                <div class="pakketkaart__prijs-label">Lessen</div>
                                <div class="pakketkaart__prijs-bedrag"><?= (int)$pakket['aantal_lessen'] ?></div>
                            </div>
                            <div class="pakketkaart__prijs">
                                <div class="pakketkaart__prijs-label">Prijs</div>
                                <div class="pakketkaart__prijs-bedrag">€<?= number_format($pakket['prijs'], 0, ',', '.') ?></div>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>
        <?php } ?>
    </section>

</main>

<script>
    function setTab(el, filter)
    {
        document.querySelectorAll('.tabbalk__tab').forEach(function(tab)
        {
            tab.classList.remove('tabbalk__tab--actief');
        });

        el.classList.add('tabbalk__tab--actief');

        var sectieLessen    = document.getElementById('sectie-lessen');
        var sectiePakketten = document.getElementById('sectie-pakketten');
        var alleKaarten     = document.querySelectorAll('.leskaart');

        if (filter === 'pakketten')
        {
            sectieLessen.style.display    = 'none';
            sectiePakketten.style.display = 'block';
            return;
        }

        sectieLessen.style.display    = 'block';
        sectiePakketten.style.display = 'none';


        alleKaarten.forEach(function(kaart)
        {
            if (filter === 'alle')
            {
                kaart.style.display = 'flex';
            }
            else
            {
                if (kaart.dataset.status === filter)
                {
                    kaart.style.display = 'flex';
                }
                else
                {

                    kaart.style.display = 'none';
                }
            }
        });
    }
</script>