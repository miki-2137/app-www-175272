<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Projekt 1">
    <meta name="keywords" content="HTML5, CSS3, JavaScript">
    <meta name="author" content="Mikołaj Małecki">
    <title>Filmy oscarowe</title>
    <?php
        error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);
        $idp = $_GET['idp'] ?? '';
        if (!$idp) $strona='html/glowna.html';
        elseif ($idp =='glowna') $strona = 'html/glowna.html';
        elseif ($idp =='glowne') $strona = 'html/glowne.html';
        elseif ($idp =='techniczne') $strona = 'html/techniczne.html';
        elseif ($idp =='pozostale') $strona = 'html/pozostale.html';
        elseif ($idp =='kontakt') $strona = 'html/kontakt.html';
        elseif ($idp =='skrypty') $strona = 'html/skrypty.html';
        elseif ($idp =='filmy') $strona = 'html/filmy.html';

        if($idp=='skrypty'){
            echo '<script src="js/kolorujtlo.js" type="text/javascript"></script>
            <script src="js/timedate.js" type="text/javascript"></script>
            <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
            <link rel="stylesheet" href="css/styl2.css">';
        }
        echo '<link rel="stylesheet" href="css/styl.css">';
    ?>
</head>
<body onload="startClock()">
<header>
    <h1 id="tytul">Filmy oscarowe</h1>
    <ul>
        <li><a href="index.php?idp=">Strona Główna</a></li>
        <li><a href="index.php?idp=glowne">Nagrody główne</a></li>
        <li><a href="index.php?idp=techniczne">Nagrody techniczne</a></li>
        <li><a href="index.php?idp=pozostale">Pozostałe nagrody</a></li>
        <li><a href="index.php?idp=kontakt">Kontakt</a></li>
        <li><a href="index.php?idp=skrypty">Skrypty</a></li>
        <li><a href="index.php?idp=filmy">Filmy</a></li>
    </ul>
</header>
<div class="content">
    <?php
    if(file_exists($strona)){
        include($strona);
    } else {
        echo '<p>Podstrona nie istnieje.</p>';
    }
    ?>
</div>
<footer>
<?php
    $nr_indeksu = '175272';
    $nrGrupy = '2';

    echo 'Autor: Mikołaj Małecki '.$nr_indeksu.' grupa '.$nrGrupy;
?>
</footer>
</body>
</html>