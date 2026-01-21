<!DOCTYPE html>
<html lang="pl">
<head>
    <meta http-equiv="Content-type" content="text/html; charset=UTF-8" />  
    <meta http-equiv="Content-Language" content="pl" /> 
    <meta name="Author" content="Mikołaj Małecki" />
    <title>Oscary 2025</title>
    <?php
    session_start();

    // Jeśli $_GET['idp'] nie jest ustawiony, ustaw go na '1' aby wyświetli  stronę główną
    if (!isset($_GET['idp'])) {
        $_GET['idp'] = '1';
    }
    // Zawsze ładuj podstawowy plik stylów
    echo '<link rel="stylesheet" href="css/style.css">';
    // Jeśli to strona skryptów, dodaj dodatkowe style
    if ($_GET['idp'] == '6') {
        echo '<link rel="stylesheet" href="css/style2.css">';   
    }
    ?>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" type="text/javascript"></script>
    <script src="js/kolorujtlo.js" type="text/javascript"></script>
    <script src="js/timedate.js" type="text/javascript"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="js/cart.js" type="text/javascript"></script>
</head>
<body onload="startclock()">
<?php
    error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING); // Ustawia raportowanie błędów na wyświetlanie wszystkich błędów z wyjątkiem E_NOTICE i E_WARNING
    
    include('cfg.php');
    include('php/navbar.php');
    
    $navbar = new Navbar();
?>
<header>
    <h1 id="tytul">Oscary 2025</h1>
    <?php echo $navbar->generateNavbar(); ?>
</header> 

<div class='content'>
    <?php
    include('showpage.php');
    include('admin/admin.php');
    include('php/contact.php');
    include('php/categories.php');
    include('php/Products.php');
    include('php/Shop.php');
    
    $id = htmlspecialchars($_GET['idp']);

    
    static $Admin = null;



    // Panel Administratora:
    // case -1  : Panel logowania administratora
    // case -2  : Edycja istniejącej strony
    // case -3  : Usuwanie strony
    // case -4  : Tworzenie nowej strony
    // case -6  : Wylogowanie z panelu admina
    //
    // Kontakt i Hasło:
    // case -5  : Obsługa formularza kontaktowego
    // case -7  : Formularz odzyskiwania hasła
    //
    // Zarządzanie Kategoriami:
    // case -8  : Wyświetlanie listy wszystkich kategorii
    // case -9  : Dodawanie nowej kategorii
    // case -10 : Edycja istniejącej kategorii
    // case -11 : Usuwanie kategorii
    //
    // Zarządzanie Produktami:
    // case -12 : Wyświetlanie listy wszystkich produktów
    // case -13 : Dodawanie nowego produktu
    // case -14 : Edycja istniejącego produktu
    // case -15 : Usuwanie produktu
    //
    // Sklep:
    // case -16 : Strona sklepu z produktami
    // case -17 : Wyświetlanie koszyka zakupów
    //
    // Domyślne:
    // default  : Wyświetlenie strony o podanym ID
    
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
        case -5:
            //Tworzy nowy obiekt klasy Contact i wyświetla formularz kontaktowy
            //po przesłaniu formularza, wyświetli komunikat o sukcesie lub błędzie
            $contact = new Contact();
            echo "<h1> Kontakt </h1>";
            echo $contact->WyslijMailKontakt("175272@student.uwm.edu.pl");
            break;

        case -6:
            if($Admin === null) {
                $Admin = new Admin();
            }
            echo $Admin->Wyloguj();
            break;
        case -7:
            //Tutaj to samo co w 5 tylko dla odzyskiwania hasła
            $Contact = new Contact();
            echo "<h2> Odzyskanie hasla </h2>";
            echo $Contact->PrzypomnijHaslo("175272@student.uwm.edu.pl"); 			// Wyświetlenie promptu na email, w celu odzyskania hasła
		    break;
        case -8:
            $Categories = new Categories();
            echo $Categories->PokazKategorie();
            break;
        case -9:
            $Categories = new Categories();
            echo $Categories->DodajKategorie();
            break;
        case -10:
            $Categories = new Categories();
            echo $Categories->EdytujKategorie();
            break;
        case -11:
            $Categories = new Categories();
            echo $Categories->UsunKategorie();
            break;
        case -12:
            $Products = new Products();
            echo $Products->PokazProdukty();
            break;
        case -13:
            $Products = new Products();
            echo $Products->DodajProdukt();
            break;
        case -14:
            $Products = new Products();
            echo $Products->EdytujProdukt();
            break;
        case -15:
            $Products = new Products();
            echo $Products->UsunProdukt();
            break;
        case -16:
            $shop = new Shop();
            $shop->ShopPage();
            break;
        case -17:
            $shop = new Shop();
            $shop->ShowCart();
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

        echo 'Autor: Mikołaj Małecki '.$nr_indeksu.' grupa '.$nrGrupy.'<br>';
        ?>
</footer>
</body>
</html>
