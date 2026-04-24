<?php
require 'db.php';

$hinta_suunnittelu = 50;
$hinta_asennus = 45;
$hinta_kaapeli = 1.25;
$hinta_pistorasia = 12.50;

$tulos = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $suunnittelu = 3;
    $asennus = 12;
    $kaapeli = 3;
    $pistorasia = 1;

    $tyo = ($suunnittelu * $hinta_suunnittelu)
         + ($asennus * $hinta_asennus);

    $tarvikkeet = ($kaapeli * $hinta_kaapeli)
                + ($pistorasia * $hinta_pistorasia);

    $netto = $tyo + $tarvikkeet;
    $alv = $netto * 0.24;
    $yhteensa = $netto + $alv;

    $tulos = [
        'tyo' => $tyo,
        'tarvikkeet' => $tarvikkeet,
        'netto' => $netto,
        'alv' => $alv,
        'yhteensa' => $yhteensa
    ];
}
?>

<h1>Hinta-arvio (R1)</h1>

<p>
Kohde sisältää:
<ul>
    <li>Suunnittelu: 3 h</li>
    <li>Asennustyö: 12 h</li>
    <li>Sähköjohto: 3 m</li>
    <li>Pistorasia: 1 kpl</li>
</ul>
</p>

<form method="post">
    <button type="submit">Laske arvio</button>
</form>

<br>

<?php if ($tulos): ?>
    <h2>Tulos</h2>

    <p>Työt yhteensä: <?= round($tulos['tyo'], 2) ?> €</p>
    <p>Tarvikkeet: <?= round($tulos['tarvikkeet'], 2) ?> €</p>

    <p><b>Nettosumma:</b> <?= round($tulos['netto'], 2) ?> €</p>
    <p><b>ALV (24%):</b> <?= round($tulos['alv'], 2) ?> €</p>

    <h3>Yhteensä: <?= round($tulos['yhteensa'], 2) ?> €</h3>
<?php endif; ?>

<br>
<a href="index.php"><button>Palaa etusivulle</button></a>