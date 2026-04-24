<?php
require 'db.php';

$tyot = pg_query($conn, "SELECT tyo_id, osoite FROM Tyo ORDER BY tyo_id");
$tyo_id = $_GET['tyo_id'] ?? null;

if (!$tyo_id):
?>
<h1>Valitse työ laskulle</h1>
<form method="get">
    <select name="tyo_id">
        <?php while ($t = pg_fetch_assoc($tyot)): ?>
            <option value="<?= $t['tyo_id'] ?>">
                <?= $t['tyo_id'] . " - " . $t['osoite'] ?></option>
        <?php endwhile; ?>
    </select>
    <button type="submit">Näytä lasku</button>
</form>

<br>
<a href="index.php"><button>Palaa etusivulle</button></a>
<?php exit; endif; ?>

<?php
$res = pg_query_params($conn,
    "SELECT a.nimi, a.osoite AS a_osoite, t.osoite AS t_osoite, t.tyyppi
     FROM Tyo t
     JOIN Asiakas a ON t.asiakas_id = a.asiakas_id
     WHERE t.tyo_id = $1",
    [$tyo_id]
);
$data = pg_fetch_assoc($res);

$tunti = pg_fetch_assoc(pg_query_params($conn,
    "SELECT * FROM Tuntityo WHERE tyo_id = $1",
    [$tyo_id]
));

$urakka = pg_fetch_assoc(pg_query_params($conn,
    "SELECT * FROM Urakkatyo WHERE tyo_id = $1",
    [$tyo_id]
));

// Taikalukuhinnat
$h_s = 55.00;
$h_t = 45.00;
$h_a = 35.00;

$tyo_summa = 0;
$tyo_alv = 0;
$rivit = [];

if ($data['tyyppi'] === 'tunti' && $tunti) {

    $s = $h_s * (1 - $tunti['suunnittelu_ale']/100) * $tunti['suunnittelutunnit'];
    $t = $h_t * (1 - $tunti['tyo_ale']/100) * $tunti['tyotunnit'];
    $a = $h_a * (1 - $tunti['apu_ale']/100) * $tunti['aputunnit'];

    $rivit[] = ["Suunnittelu", $tunti['suunnittelutunnit'], $s/1.24, $s - $s/1.24, $s];
    $rivit[] = ["Työ", $tunti['tyotunnit'], $t/1.24, $t - $t/1.24, $t];
    $rivit[] = ["Aputyö", $tunti['aputunnit'], $a/1.24, $a - $a/1.24, $a];

    $tyo_summa = $s/1.24 + $t/1.24 + $a/1.24;
    $tyo_alv = ($s + $t + $a) - ($s/1.24 + $t/1.24 + $a/1.24);
}

if ($data['tyyppi'] === 'urakka' && $urakka) {
    $sum = $urakka['kokonaishinta'] * (1 - $urakka['alennus']/100);
    $rivit[] = ["Urakka", "-", $sum, $sum*0.24];
    $tyo_summa = $sum;
    $tyo_alv = $sum * 0.24;
}

$res2 = pg_query_params($conn,
    "SELECT ta.maara, ta.alennus, t.nimi, t.myyntihinta, t.alv
     FROM Tyotarvike ta
     JOIN Tarvike t ON ta.tarvike_id = t.tarvike_id
     WHERE ta.tyo_id = $1",
    [$tyo_id]
);

$tarvike_summa = 0;
$tarvike_alv = 0;
$tarvike_rivit = [];

while ($r = pg_fetch_assoc($res2)) {

    $yht = $r['myyntihinta'] * $r['maara'];

    $netto = $yht / (1 + $r['alv']);
    $netto = $netto * (1 - $r['alennus'] / 100);

    $alv = $netto * $r['alv'];
    $yht2 = $netto + $alv;

    $tarvike_rivit[] = [
        $r['nimi'],
        $r['maara'],
        $netto,
        $alv,
        $yht2
    ];

    $tarvike_summa += $netto;
    $tarvike_alv += $alv;
}

$netto = $tyo_summa + $tarvike_summa;
$alv = $tyo_alv + $tarvike_alv;
$yht = $netto + $alv;
$kt = $tyo_summa + $tyo_alv;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tallenna_lasku'])) {

    $id_res = pg_query($conn, "SELECT COALESCE(MAX(lasku_id),0)+1 AS id FROM Lasku");
    $new_id = pg_fetch_assoc($id_res)['id'];

    $res = pg_query_params($conn,
        "INSERT INTO Lasku (
            lasku_id,
            tyo_id,
            paivamaara,
            erapaiva,
            maksupaiva,
            maksettu,
            mennyt_muistutus_karhu,
            tyyppi,
            vanha_lasku_id
        )
        VALUES ($1,$2,$3,$4,NULL,false,false,'lasku',NULL)",
        [
            $new_id,
            $tyo_id,
            $_POST['paivamaara'],
            $_POST['erapaiva']
        ]
    );

    echo $res ? "<p>Lasku tallennettu!</p>" : "<p>Virhe tallennuksessa</p>";
}

?>

<h1>Lasku</h1>

<h2>Asiakas</h2>
<p><?= $data['nimi'] ?><br><?= $data['a_osoite'] ?></p>

<h2>Työkohde</h2>
<p><?= $data['t_osoite'] ?></p>

<h2>Työt</h2>
<table border="1">
<tr><th>Tyyppi</th><th>Määrä</th><th>Veroton hinta</th><th>ALV</th><th>Yht.</th></tr>
<?php foreach ($rivit as $r): ?>
<tr>
<td><?= $r[0] ?></td>
<td><?= $r[1] ?></td>
<td><?= round($r[2],2) ?> €</td>
<td><?= round($r[3],2) ?> €</td>
<td><?= round($r[4],2) ?> €</td>
</tr>
<?php endforeach; ?>
</table>

<h2>Tarvikkeet</h2>
<table border="1">
<tr><th>Nimi</th><th>Määrä</th><th>Veroton hinta</th><th>ALV</th><th>Yht.</th></tr>
<?php foreach ($tarvike_rivit as $t): ?>
<tr>
<td><?= $t[0] ?></td>
<td><?= $t[1] ?></td>
<td><?= round($t[2],2) ?> €</td>
<td><?= round($t[3],2) ?> €</td>
<td><?= round($t[4],2) ?> €</td>
</tr>
<?php endforeach; ?>
</table>

<h2>Yhteenveto</h2>
<p>Veroton summa: <?= round($netto,2) ?> €</p>
<p>ALV yhteensä: <?= round($alv,2) ?> €</p>
<p><strong>Maksettavaa yhteensä: <?= round($yht,2) ?> €</strong></p>

<h2>Kotitalousvähennys</h2>
<p><?= round($kt,2) ?> €</p>

<h2>Tallenna lasku</h2>

<form method="post">
    <input type="hidden" name="tallenna_lasku" value="1">

    Päivämäärä:
    <input type="date" name="paivamaara" required><br>

    Eräpäivä:
    <input type="date" name="erapaiva" required><br>

    <button type="submit">Tallenna lasku</button>
</form>

<br>
<a href="index.php"><button>Palaa etusivulle</button></a>