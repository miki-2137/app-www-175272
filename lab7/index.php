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
        session_start();
        error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);

        if(!isset($_GET['idp'])){
            $_GET['idp'] = '1';
        }

        if($_GET['idp'] == '6'){
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
        <li><a href="index.php?idp=1">Strona Główna</a></li>
        <li><a href="index.php?idp=2">Nagrody główne</a></li>
        <li><a href="index.php?idp=3">Nagrody techniczne</a></li>
        <li><a href="index.php?idp=4">Pozostałe nagrody</a></li>
        <li><a href="index.php?idp=5">Kontakt</a></li>
        <li><a href="index.php?idp=6">Skrypty</a></li>
        <li><a href="index.php?idp=7">Poprzednie ceremonie</a></li>
        <li><a href="index.php?idp=-1">Panel Admina</a></li>
    </ul>
</header>
<div class="content">
    <?php
    include('cfg.php');
    include('showpage.php');
    include('admin/admin.php');
    
    $id = htmlspecialchars($_GET['idp']);

    static $Admin = null;

    switch($id) {
        case -1:
            if($Admin === null) {
                $Admin = new Admin();
            }
            echo $Admin->LoginAdmin();
            break;
        
        case -2:
            if($Admin === null) {
                $Admin = new Admin();
            }
            echo $Admin->EditPage();
            break;
        
        case -3:
            if($Admin === null) {
                $Admin = new Admin();
            }
            echo $Admin->DeletePage();
            break;
        case -4:
            if($Admin === null) {
                $Admin = new Admin();
            }
            echo $Admin->CreatePage();
            break;

        default:
            echo PokazStrone($id);
            break;
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