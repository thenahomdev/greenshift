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

$loginId    = $_SESSION['loginid'];
$nu         = new DateTime();
$vandaag    = $nu->format('Y-m-d');
$huidigTijd = $nu->format('H:i');

$sql  = "SELECT leerlingid FROM leerlingen WHERE loginid = :loginId LIMIT 1";
$stmt = $pdo->prepare($sql);
$stmt->execute(['loginId' => $loginId]);
$leerling   = $stmt->fetch();
$leerlingId = $leerling['leerlingid'];

$sql = "
    SELECT pg.geschiedenisid, pg.datum AS aankoopdatum, pg.prijs, p.pakkettenid,
           p.naam AS pakketNaam, p.aantal_lessen
    FROM pakket_geschiedenis pg
    JOIN pakketten p ON p.pakkettenid = pg.pakketid
    WHERE pg.leerlingid = :leerlingId
    ORDER BY pg.datum ASC
";
$stmt = $pdo->prepare($sql);
$stmt->execute(['leerlingId' => $leerlingId]);
$gekochtePacketten = $stmt->fetchAll();

$sql = "
    SELECT r.rijlesid, r.datum, r.tijd, r.status,
           i.voornaam AS instructeurVoornaam,
           i.tussenvoegsel AS instructeurTussenvoegsel,
           i.achternaam AS instructeurAchternaam
    FROM rijlessen r
    JOIN rijinstructeurs i ON i.rijinstructeurid = r.rijinstructeurid
    WHERE r.leerlingid = :leerlingId
    ORDER BY r.datum ASC, r.tijd ASC
";
$stmt = $pdo->prepare($sql);
$stmt->execute(['leerlingId' => $leerlingId]);
$alleRijlessen = $stmt->fetchAll();

$totaalGekochteleden = 0;
$totaalGekochteprijs = 0.0;

foreach ($gekochtePacketten as $pakket)
{
    $totaalGekochteleden += (int)$pakket['aantal_lessen'];
    $totaalGekochteprijs += (float)$pakket['prijs'];
}

$voltooideRijlessen = [];
foreach ($alleRijlessen as $les)
{
    $isVoltooid = false;

    if ($les['status'] == 1)
    {
        if ($les['datum'] < $vandaag)
        {
            $isVoltooid = true;
        }
        elseif ($les['datum'] == $vandaag && $les['tijd'] <= $huidigTijd)
        {
            $isVoltooid = true;
        }
    }

    if ($isVoltooid)
    {
        $voltooideRijlessen[] = $les;
    }
}

$totaalGebruikteLessen = count($voltooideRijlessen);
$resterendeLessen      = $totaalGekochteleden - $totaalGebruikteLessen;

function instructeurNaamFactuur($les)
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
    <button class="tabbalk__tab tabbalk__tab--actief" onclick="setTab(this, 'pakketten')">Gekochte Pakketten</button>
    <button class="tabbalk__tab" onclick="setTab(this, 'lessen')">Gevolgde Lessen</button>
</nav>

<main class="pagina-inhoud">

    <div class="factuur-kop">
        <h1>Mijn Lesoverzicht</h1>
    </div>

    <div class="saldo-rij">
        <div class="saldo-kaart">
            <div class="saldo-kaart__label">Totaal gekocht</div>
            <div class="saldo-kaart__getal"><?= $totaalGekochteleden ?></div>
            <div class="saldo-kaart__sub">lessen via pakketten</div>
        </div>
        <div class="saldo-kaart">
            <div class="saldo-kaart__label">Gebruikt</div>
            <div class="saldo-kaart__getal"><?= $totaalGebruikteLessen ?></div>
            <div class="saldo-kaart__sub">voltooide rijlessen</div>
        </div>
        <div class="saldo-kaart saldo-kaart--rest">
            <div class="saldo-kaart__label">Resterend</div>
            <div class="saldo-kaart__getal"><?= max(0, $resterendeLessen) ?></div>
            <div class="saldo-kaart__sub">lessen beschikbaar</div>
        </div>
    </div>

    <section class="factuur-sectie" id="sectie-pakketten">
        <div class="factuur-sectie__hoofd">
            <svg viewBox="0 0 24 24"><path d="M20 4H4c-1.11 0-2 .89-2 2v12c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V6c0-1.11-.89-2-2-2zm0 14H4v-6h16v6zm0-10H4V6h16v2z"/></svg>
            Gekochte Pakketten
        </div>

        <?php if (empty($gekochtePacketten))
        { ?>
            <p class="geen-items">Je hebt nog geen pakketten gekocht.</p>
        <?php }
        else
        { ?>
            <table class="factuur-tabel">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Pakket</th>
                    <th>Aankoopdatum</th>
                    <th class="rechts">Aantal lessen</th>
                    <th class="rechts">Prijs</th>
                </tr>
                </thead>
                <tbody>
                <?php $teller = 1;
                foreach ($gekochtePacketten as $pakket)
                { ?>
                    <tr>
                        <td><?= $teller++ ?></td>
                        <td>
                            <span class="badge-pakket">Pakket</span>
                            <?= htmlspecialchars($pakket['pakketNaam']) ?>
                        </td>
                        <td><?= date('d-m-Y', strtotime($pakket['aankoopdatum'])) ?></td>
                        <td class="rechts"><?= (int)$pakket['aantal_lessen'] ?></td>
                        <td class="rechts">€<?= number_format($pakket['prijs'], 2, ',', '.') ?></td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>

            <div class="totaal-blok">
                <div class="totaal-blok__rij">
                    <span>Totaal lessen gekocht</span>
                    <span><?= $totaalGekochteleden ?></span>
                </div>
                <div class="totaal-blok__rij totaal-blok__rij--eindtotaal">
                    <span>Totaal uitgegeven</span>
                    <span>€<?= number_format($totaalGekochteprijs, 2, ',', '.') ?></span>
                </div>
            </div>
        <?php } ?>
    </section>

    <section class="factuur-sectie" id="sectie-lessen" style="display: none;">
        <div class="factuur-sectie__hoofd">
            <svg viewBox="0 0 24 24"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>
            Gevolgde Lessen
        </div>

        <?php if (empty($voltooideRijlessen))
        { ?>
            <p class="geen-items">Je hebt nog geen lessen gevolgd.</p>
        <?php }
        else
        { ?>
            <table class="factuur-tabel">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Datum</th>
                    <th>Tijd</th>
                    <th>Instructeur</th>
                    <th class="rechts">Lessen</th>
                </tr>
                </thead>
                <tbody>
                <?php $teller = 1;
                foreach ($voltooideRijlessen as $les)
                { ?>
                    <tr>
                        <td><?= $teller++ ?></td>
                        <td><?= date('d-m-Y', strtotime($les['datum'])) ?></td>
                        <td>
                            <?= date('H:i', strtotime($les['tijd'])) ?> –
                            <?= date('H:i', strtotime($les['tijd'] . ' +1 hour')) ?>
                        </td>
                        <td><?= instructeurNaamFactuur($les) ?></td>
                        <td class="rechts">
                            <span class="badge-les">1</span>
                        </td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>

            <div class="totaal-blok">
                <div class="totaal-blok__rij">
                    <span>Totaal gevolgd</span>
                    <span><?= $totaalGebruikteLessen ?></span>
                </div>
                <div class="totaal-blok__rij totaal-blok__rij--eindtotaal">
                    <span>Totaal gekocht</span>
                    <span><?= $totaalGekochteleden ?></span>
                </div>
                <div class="totaal-blok__rij totaal-blok__rij--rest">
                    <span>Resterend saldo</span>
                    <span><?= max(0, $resterendeLessen) ?> les<?= $resterendeLessen != 1 ? 'sen' : '' ?></span>
                </div>
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

        var sectiePakketten = document.getElementById('sectie-pakketten');
        var sectieLessen    = document.getElementById('sectie-lessen');

        if (filter === 'pakketten')
        {
            sectiePakketten.style.display = 'block';
            sectieLessen.style.display    = 'none';
        }
        else
        {
            sectiePakketten.style.display = 'none';
            sectieLessen.style.display    = 'block';
        }
    }
</script>