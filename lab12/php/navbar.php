<?php
class Navbar {
    /* generateNavbar()
     * Generuje kod HTML dla paska nawigacyjnego
     * 
     * Metoda pobiera strony z bazy danych i generuje dynamiczny pasek nawigacyjny.
     * Strony są podzielone na główne i podstrony z nagrodami (w dropdownie).
     * Dodatkowo obsługuje linki administracyjne i stan logowania.
     * 
     * zwraca string Kod HTML paska nawigacyjnego
     * $conn Globalne połączenie z bazą danych
     * $_GET['idp'] ID aktualnie wyświetlanej strony
     * $_SESSION['loggedin'] Stan zalogowania użytkownika
     */
    function generateNavbar() {
        global $conn;
        $currentPage = isset($_GET['idp']) ? $_GET['idp'] : '1';

        // Pobiera wszystkie aktywne strony z bazy danych
        $query = "SELECT * FROM page_list WHERE status = 1 ORDER BY id ASC";
        $result = mysqli_query($conn, $query);
        
        // Inicjalizuje tablice dla stron głównych i podstron z nagrodami
        $mainPages = array();
        $awardPages = array();
        $specialPages = array(1, 5, 6);
        
        // Segreguje strony na główne i podstrony z grami
        while($row = mysqli_fetch_assoc($result)) {
            $id = intval($row['id']);
            if($id > 0) {
                if(in_array($id, $specialPages)) {
                    $mainPages[] = $row;
                } else {
                    $awardPages[] = $row;
                }
            }
        }

        // Rozpoczyna tworzenie kodu HTML dla paska nawigacyjnego
        $nav = '<nav id="navbar"><ul>';
        
        // Dodaje stronę główną do paska nawigacyjnego
        foreach($mainPages as $page) {
            if($page['id'] == 1) {
                $nav .= '<li' . ($currentPage == $page['id'] ? ' class="active"' : '') . '>';
                $nav .= '<a href="index.php?idp=' . $page['id'] . '">' . $page['page_title'] . '</a></li>';
                break;
            }
        }
        
        // Dodaje dropdown z kategoriami nagrod
        if(!empty($awardPages)) {
            $nav .= '<li class="dropdown' . (in_array($currentPage, array_column($awardPages, 'id')) ? ' active' : '') . '">';
            $nav .= '<a href="#" class="dropbtn">Nagrody</a>';
            $nav .= '<div class="dropdown-content">';
            
            // Dodaje każdą kategorię nagród do dropdown menu
            foreach($awardPages as $game) {
                $nav .= '<a href="index.php?idp=' . $game['id'] . '"' . 
                        ($currentPage == $game['id'] ? ' class="active"' : '') . '>' . 
                        $game['page_title'] . '</a>';
            }
            $nav .= '</div></li>';
        }
        
        // Dodaje pozostałe strony główne do paska nawigacyjnego
        foreach($mainPages as $page) {
            if($page['id'] != 1) {
                $nav .= '<li' . ($currentPage == $page['id'] ? ' class="active"' : '') . '>';
                $nav .= '<a href="index.php?idp=' . $page['id'] . '">' . $page['page_title'] . '</a></li>';
            }
        }
        
        // Dodaje linki administracyjne i funkcjonalne
        $nav .= '<li' . ($currentPage == -16 ? ' class="active"' : '') . '><a href="index.php?idp=-16">Sklep</a></li>';
        $nav .= '<li' . ($currentPage == -17 ? ' class="active"' : '') . '><a href="index.php?idp=-17">Koszyk</a></li>';
        $nav .= '<li' . ($currentPage == -1 ? ' class="active"' : '') . '><a href="index.php?idp=-1">Panel Admina</a></li>';
        $nav .= '<li' . ($currentPage == -5 ? ' class="active"' : '') . '><a href="index.php?idp=-5">Kontakt</a></li>';
        
        // Dodaje link do wylogowania lub odzyskiwania hasła w zależności od stanu logowania
        if(isset($_SESSION['loggedin'])) {
            $nav .= '<li' . ($currentPage == -6 ? ' class="active"' : '') . '><a href="index.php?idp=-6">Wyloguj</a></li>';
        } else {
            $nav .= '<li' . ($currentPage == -7 ? ' class="active"' : '') . '><a href="index.php?idp=-7">Odzyskaj hasło</a></li>';
        }
        
        // Zamyka znaczniki HTML paska nawigacyjnego
        $nav .= '</ul></nav>';
        
        // Zwraca kompletny kod HTML paska nawigacyjnego
        return $nav;
    }
}
?>
