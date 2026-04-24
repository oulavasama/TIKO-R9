<?php
require 'db.php';

$tyo_id = $_GET['tyo_id'] ?? null;

$alennus = 0;
$maara = 0;


$tyot = pg_query($conn, "SELECT tyo_id, osoite FROM Tyo ORDER BY tyo_id");
$tarvikkeet = pg_query($conn, "SELECT tarvike_id, nimi FROM Tarvike ORDER BY tarvike_id");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $sql = "
        INSERT INTO Tyotarvike (tarvike_id, tyo_id, alennus, maara)
        VALUES ($1,$2,$3,$4)
        ON CONFLICT (tarvike_id, tyo_id) DO UPDATE SET
            maara = Tyotarvike.maara + EXCLUDED.maara,
            alennus = EXCLUDED.alennus
    ";

    pg_query_params($conn, $sql, [
        $_POST['tarvike_id'],
        $_POST['tyo_id'],
        $_POST['alennus'],
        $_POST['maara']
    ]);

    header("Location: add_tarvike.php?tyo_id=" . $_POST['tyo_id']);
    exit;
}
?>

<h1>Lisää tarvikkeita työlle</h1>

<form method="get">
    Työ:
    <select name="tyo_id" onchange="this.form.submit()">
        <option value="">-- valitse työ --</option>

        <?php while ($t = pg_fetch_assoc($tyot)): ?>
            <option value="<?= $t['tyo_id'] ?>"
                <?= ($tyo_id == $t['tyo_id']) ? 'selected' : '' ?>>
                <?= $t['tyo_id'] . " - " . $t['osoite'] ?>
            </option>
        <?php endwhile; ?>
    </select>
</form>

<hr>

<form method="post">

    <input type="hidden" name="tyo_id" value="<?= $tyo_id ?>">

    Tarvike:
    <select name="tarvike_id">
        <?php while ($tar = pg_fetch_assoc($tarvikkeet)): ?>
            <option value="<?= $tar['tarvike_id'] ?>">
                <?= $tar['tarvike_id'] . " - " . $tar['nimi'] ?>
            </option>
        <?php endwhile; ?>
    </select><br>

    Määrä:
    <input name="maara" type="number" step="1" value="0"><br>

    Alennus:
    <input name="alennus" type="number" step="0.5" value="0"> %<br>

    <button type="submit">Lisää</button>
</form>

<br>
<a href="index.php"><button>Palaa etusivulle</button></a>