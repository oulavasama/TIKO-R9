<?php
require 'db.php';

$asiakkaat = pg_query($conn, "SELECT asiakas_id, nimi FROM Asiakas ORDER BY asiakas_id");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $res = pg_query_params($conn,
        "INSERT INTO Tyo (tyo_id, asiakas_id, osoite, tyyppi)
         VALUES ($1, $2, $3, $4)",
        [$_POST['tyo_id'], $_POST['asiakas_id'], $_POST['osoite'], $_POST['tyyppi']]
    );

    echo $res ? "Lisätty onnistuneesti" : "Virhe lisäyksessä";
}
?>

<h1>Lisää työkohde</h1>

<form method="post">
    Työ ID: <input name="tyo_id"><br>

    Asiakas:
    <select name="asiakas_id">
        <?php while ($a = pg_fetch_assoc($asiakkaat)): ?>
            <option value="<?= $a['asiakas_id'] ?>">
                <?= $a['asiakas_id'] . " - " . $a['nimi'] ?>
            </option>
        <?php endwhile; ?>
    </select><br>

    Osoite: <input name="osoite"><br>

    Tyyppi:
    <select name="tyyppi">
        <option value="tunti">tuntityö</option>
        <option value="urakka">urakka</option>
    </select><br>

    <button type="submit">Lisää</button>
</form>

<br>
<a href="index.php"><button>Palaa etusivulle</button></a>