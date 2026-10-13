<?php
include __DIR__ . '/../php/rijinstructeur_dagplanning.php';
include __DIR__ . '/alerts.inc.php';

$googleMapsApiKey = '';
if (is_file(__DIR__ . '/../config/keys.php')) {
    include __DIR__ . '/../config/keys.php';
}

$dagen = [
        1 => 'maandag',
        2 => 'dinsdag',
        3 => 'woensdag',
        4 => 'donderdag',
        5 => 'vrijdag',
        6 => 'zaterdag',
        7 => 'zondag'
];

$dagNaam = $dagen[$datumObject->format('N')];
?>

<div class="container">

    <h1>Dagplanning Rijinstructeur</h1>

    <div class="date-navigation" style="margin-bottom:20px;">

        <?php
        $prevDate = clone $datumObject;
        do { $prevDate->modify("-1 day"); } while (in_array($prevDate->format('N'), [6,7]));

        $nextDate = clone $datumObject;
        do { $nextDate->modify("+1 day"); } while (in_array($nextDate->format('N'), [6,7]));
        ?>

        <a href="index.php?page=rijinstructeur_dagplanning&datum=<?php echo $prevDate->format('Y-m-d'); ?>">← Vorige dag</a>

        <strong style="margin:0 20px;">
            <?php echo ucfirst($dagNaam).' '.$datumObject->format("d F Y"); ?>
        </strong>

        <a href="index.php?page=rijinstructeur_dagplanning&datum=<?php echo $nextDate->format('Y-m-d'); ?>">Volgende dag →</a>

    </div>

    <?php if (empty($lessen)): ?>
        <div style="padding:40px;text-align:center;">Geen geplande lessen op deze dag.</div>
    <?php else: ?>

        <table class="rooster">
            <thead>
            <tr>
                <th>Tijd</th>
                <th>Naam</th>
                <th>Adres</th>
                <th>Telefoonnummer</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach($lessen as $les):
                $adres = $les['straatnaam'].' '.$les['huisnummer'].', '.$les['postcode'].' '.$les['plaats'];
                ?>
                <tr>
                    <td><?php echo date("H:i", strtotime($les['tijd'])); ?></td>
                    <td><?php echo htmlspecialchars($les['voornaam'].' '.($les['tussenvoegsel'] ?? '').' '.$les['achternaam']); ?></td>

                    <td>
                        <a href="#" onclick="openGoogleMaps('<?php echo htmlspecialchars($adres, ENT_QUOTES); ?>'); return false;">
                            <?php echo htmlspecialchars($adres); ?>
                        </a>
                    </td>

                    <td>
                        <a href="tel:<?php echo htmlspecialchars($les['telefoonnummer']); ?>">
                            <?php echo htmlspecialchars($les['telefoonnummer']); ?>
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

    <?php endif; ?>

    <h2 style="margin-top:40px;">Kaart overzicht</h2>
    <div id="map" style="height:400px; width:100%; border:1px solid #ccc;"></div>

    <script>
        let map;

        function initMap() {
            const mapElement = document.getElementById("map");

            if (!mapElement) return;

            map = new google.maps.Map(mapElement, {
                zoom: 12,
                center: { lat: 51.9851, lng: 5.8987 }
            });

            const geocoder = new google.maps.Geocoder();

            <?php foreach($lessen as $les):
            $adres = $les['straatnaam'].' '.$les['huisnummer'].', '.$les['postcode'].' '.$les['plaats'];
            ?>
            geocoder.geocode({ address: "<?php echo addslashes($adres); ?>" }, function(results, status) {
                if (status === "OK") {
                    new google.maps.Marker({
                        map: map,
                        position: results[0].geometry.location
                    });
                }
            });
            <?php endforeach; ?>
        }

        // 🔥 ALLEEN BESTEMMING → GEEN STARTLOCATIE
        function openGoogleMaps(adres) {
            if (!adres) return;

            const url = "https://www.google.com/maps/search/?api=1&query=" + encodeURIComponent(adres);

            window.open(url, "_blank");
        }

        window.initMap = initMap;
    </script>

    <script async defer
            src="https://maps.googleapis.com/maps/api/js?key=<?php echo urlencode($googleMapsApiKey); ?>&callback=initMap">

    </script>

</div>