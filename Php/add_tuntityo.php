<?php
require 'db.php';


$tyo_id = $_GET['tyo_id'] ?? null;


$s = 0;
$t = 0;
$a = 0;
$s_ale = 0;
$t_ale = 0;
$a_ale = 0;

if ($tyo_id) {
    $res = pg_query_params(
        $conn,
        "SELECT * FROM Tuntityo WHERE tyo_id = $1",
        [$tyo_id]
    );

    if ($row = pg_fetch_assoc($res)) {
        $s = $row['suunnittelutunnit'];
        $t = $row['tyotunnit'];
        $a = $row['aputunnit'];

        $s_ale = $row['suunnittelu_ale'];
        $t_ale = $row['tyo_ale'];
        $a_ale = $row['apu_ale'];
    }
}

$tyot = pg_query($conn, "SELECT tyo_id, osoite FROM Tyo ORDER BY tyo_id");


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $sql = "
        INSERT INTO Tuntityo (
            tyo_id,
            suunnittelutunnit,
            tyotunnit,
            aputunnit,
            suunnittelu_ale,
            tyo_ale,
            apu_ale
        )
        VALUES ($1,$2,$3,$4,$5,$6,$7)
        ON CONFLICT (tyo_id) DO UPDATE SET
            suunnittelutunnit = Tuntityo.suunnittelutunnit + EXCLUDED.suunnittelutunnit,
            tyotunnit = Tuntityo.tyotunnit + EXCLUDED.tyotunnit,
            aputunnit = Tuntityo.aputunnit + EXCLUDED.aputunnit,
            suunnittelu_ale = EXCLUDED.suunnittelu_ale,
            tyo_ale = EXCLUDED.tyo_ale,
            apu_ale = EXCLUDED.apu_ale
    ";

    pg_query_params($conn, $sql, [
        $_POST['tyo_id'],
        $_POST['suunnittelu'],
        $_POST['tyo'],
        $_POST['apu'],
        $_POST['s_ale'],
        $_POST['t_ale'],
        $_POST['a_ale']
    ]);

    header("Location: add_tuntityo.php?tyo_id=" . $_POST['tyo_id']);
    exit;
}
?>

<h1>Lisää tunteja työhön</h1>


<form method="get">
    Kohde:
    <select name="tyo_id" onchange="this.form.submit()">
        <option value="">-- valitse työ --</option>

        <?php while ($trow = pg_fetch_assoc($tyot)): ?>
            <option value="<?= $trow['tyo_id'] ?>"
                <?= ($tyo_id == $trow['tyo_id']) ? 'selected' : '' ?>>
                <?= $trow['tyo_id'] . " - " . $trow['osoite'] ?>
            </option>
        <?php endwhile; ?>
    </select>
</form>

<hr>


<form method="post">

    <input type="hidden" name="tyo_id" value="<?= $tyo_id ?>">

    Suunnittelutunnit:
    <input name="suunnittelu" value="<?= $s ?>" type="number"><br>

    Työtunnit:
    <input name="tyo" value="<?= $t ?>" type="number"><br>

    Aputunnit:
    <input name="apu" value="<?= $a ?>" type="number"><br>

    Suunnittelun alennus:
    <input name="s_ale" value="<?= $s_ale ?>" type="number" step="0.5"> %<br>

    Työn alennus:
    <input name="t_ale" value="<?= $t_ale ?>" type="number" step="0.5"> %<br>

    Aputyön alennus:
    <input name="a_ale" value="<?= $a_ale ?>" type="number" step="0.5"> %<br>

    <button type="submit">Lisää</button>
</form>

<br>
<a href="index.php"><button>Palaa etusivulle</button></a>