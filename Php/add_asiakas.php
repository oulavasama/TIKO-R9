<?php
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $res = pg_query_params($conn,
        "INSERT INTO Asiakas (asiakas_id, nimi, osoite, sahkoposti, puhelin)
         VALUES ($1,$2,$3,$4,$5)",
        [
            $_POST['asiakas_id'],
            $_POST['nimi'],
            $_POST['osoite'],
            $_POST['sahkoposti'],
            $_POST['puhelin']
        ]
    );

    echo $res ? "Asiakas lisätty onnistuneesti" : "Virhe lisäyksessä";
}
?>

<h1>Lisää asiakas</h1>

<form method="post">
    Tunnus: <input name="asiakas_id"><br>
    Nimi: <input name="nimi"><br>
    Osoite: <input name="osoite"><br>
    Sähköposti: <input name="sahkoposti"><br>
    Puhelin: <input name="puhelin"><br>

    <button type="submit">Lisää</button>
</form>

<br>
<a href="index.php"><button>Palaa etusivulle</button></a>