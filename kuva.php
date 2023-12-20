<?php if (isset($_GET['code'])) { die(highlight_file(__FILE__, 1)); } ?>
<?php
$ids = simplexml_load_file('eksamid.xml');

$maksId = 0;
foreach ($ids->id as $id) {
    $praeguId = (int) $id['id'];
    if ($praeguId > $maksId) {
        $maksId = $praeguId;
    }
}
$uusId = $maksId + 1;
if (isset($_POST['submit'])) {
    $xmlDoc = new DOMDocument("1.0", "UTF-8");
    $xmlDoc->preserveWhiteSpace = false;

    if (file_exists('eksamid.xml')) {
        $xmlDoc->load('eksamid.xml');
    }

    $xml_toode = $xmlDoc->createElement("id");

    $aeg = $xmlDoc->createElement("aeg", $_POST['aeg']);
    $eksam = $xmlDoc->createElement("eksam");
    $koht = $xmlDoc->createElement("koht", $_POST['koht']);
    $eksamineerija = $xmlDoc->createElement("eksamineerija", $_POST['eksamineerija']);
    $valik = $xmlDoc->createElement("valik", $_POST['valik']);

    $eksam->appendChild($koht);
    $eksam->appendChild($eksamineerija);
    $aeg->appendChild($eksam);
    $aeg->appendChild($valik);
    $xml_toode->appendChild($aeg);

    // Установите новый ID
    $xml_toode->setAttribute('id', $uusId);

    $root = $xmlDoc->documentElement;
    $root->appendChild($xml_toode);

    $xmlDoc->formatOutput = true;
    $xmlDoc->save('eksamid.xml');
    header("Location: {$_SERVER['REQUEST_URI']}");
    exit;
}

function Kustuta($xml, $id) {
    $elementsToDelete = [];

    foreach ($xml->id as $xmlId) {
        if ((int)$xmlId['id'] === (int)$id) {
            $elementsToDelete[] = $xmlId;
        }
    }

    foreach ($elementsToDelete as $element) {
        $node = dom_import_simplexml($element);
        $node->parentNode->removeChild($node);
    }

    $xml->asXML('eksamid.xml');
}

if (isset($_POST['delete'])) {
    $eksamineerijaToDelete = $_POST['delete'];
    Kustuta($ids, $eksamineerijaToDelete);
    $ids = simplexml_load_file('eksamid.xml');
}
function OtsiIsikukoodiga($xml, $eksamineerija) {
    $vastused = array();

    foreach ($xml->id as $id) {
        $InimeneIsikukood = (string) $id->aeg->eksam->eksamineerija;
        if ((string) $eksamineerija == $InimeneIsikukood) {
            $vastus = array(
                'koht' => (string) $id->aeg->eksam->koht,
                'eksamineerija' => $InimeneIsikukood,
                'aeg' => (string) $id->aeg,
                'valik' => (string) $id->aeg->valik,
            );
            $vastused[] = $vastus;
        }
    }

    return $vastused;
}
?>
<!DOCTYPE html>
<html lang="et">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Jalgrattaeksami register</title>
</head>

<h1>Jalgrattaeksami register</h1>
<form action="" method="post" name="vorm1">
    <table>
        <tr>
            <td><label for="koht">Sisesta eksami asukoht:</label></td>
            <td><input type="text" name="koht" id="koht"></td>
        </tr>
        <tr>
            <td><label for="eksamineerija">Sisesta eksamineerija nimi:</label></td>
            <td><input type="text" name="eksamineerija" id="eksamineerija"></td>
        </tr>
        <tr>
            <td><label for="aeg">Sisesta algusaeg:</label></td>
            <td><input type="time" name="aeg" id="aeg"></td>
        </tr>
        <tr>
            <td><label for="valik">Eksami kestus:</label></td>
            <td>
                <select name="valik" id="valik">
                    <option value="30 minutit">30 minutit</option>
                    <option value="1 tund">1 tund</option>
                    <option value="1,5 tundi">1,5 tundi</option>
                    <option value="2 tundi">2 tundi</option>
                    <option value="2,5 tundi">2,5 tundi</option>
                    <option value="3 tundi">3 tundi</option>
                    <option value="3,5 tundi">3,5 tundi</option>
                    <option value="4 tundi">4 tundi</option>
                </select>
            </td>
        </tr>
        <tr>
            <td><input type="submit" name="submit" id="submit" value="Sisesta"></td>
            <td></td>
        </tr>
    </table>
</form>
<h1>Eksamid</h1>
<table>
    <tr>
        <th>Asukoht</th>
        <th>Eksamineerija</th>
        <th>Algusaeg</th>
        <th>Eksami kestus</th>
    </tr>
    <?php
    foreach ($ids->id as $id) {
        echo "<tr>";
        echo "<td>{$id->aeg->eksam->koht}</td>";
        echo "<td>{$id->aeg->eksam->eksamineerija}</td>";
        echo "<td>{$id->aeg}</td>";
        echo "<td>{$id->aeg->valik}</td>";
        echo "<td><form action='' method='post'><input type='hidden' name='delete' value='{$id['id']}'><button type='submit'>Kustuta</button></form></td>";
        echo "</tr>";
    }
    ?>
</table>
<form action="" method="post">
    <h1>Otsing eksamineerija järgi:</h1>
    <input type="text" name="search" id="search"">
    <input type="submit" value="Otsi">
</form>
<?php
if (isset($_POST['search'])) {
$otsitavEksamineerija = $_POST['search'];
$otsiVastused = OtsiIsikukoodiga($ids, $otsitavEksamineerija);

?>
<table>
    <tr>
        <th>Asukoht</th>
        <th>Eksamineerija</th>
        <th>Algusaeg</th>
        <th>Eksami kestus</th>
    </tr>
    <?php
    foreach ($otsiVastused as $vastus) {
        echo "<tr>";
        echo "<td>{$vastus['koht']}</td>";
        echo "<td>{$vastus['eksamineerija']}</td>";
        echo "<td>{$vastus['aeg']}</td>";
        echo "<td>{$vastus['valik']}</td>";
        echo "</tr>";
    }
    }
    ?>
</table>
</body>
</html>