<?php

    session_start();

    $nr_indeksu = '175272';
    $nrGrupy = '2';

    echo 'Mikołaj Małecki '.$nr_indeksu.' grupa '.$nrGrupy.'<br><br>';

    echo 'Zastosowanie metody include() <br>';
    include('testinclude.php');
    echo 'Zmienne ściągnięte metodą include(): ' .$interest. ', '.$color.'<br><br>';

    echo 'Użycie metody require_once() <br>';
    echo 'Zmienna ściągnięta metodą require_once(): ';
    require_once('testrequire.php');
    echo '<br><br>';

    echo 'Użycie warunków if, else, elseif <br>';
    $var = 4;
    echo 'Zmienna wynosi: '.$var.'<br>';
    if ($var < 5) {
        echo 'Zmienna jest mniejsza niż 5<br><br>';
    } elseif ($var == 5) {
        echo 'Zmienna jest równa 5<br><br>';
    } else {
        echo 'Zmienna jest większa niż 5<br><br>';
    }

    echo 'Użycie warunku switch';
    $var2 = 3;
    echo '<br>Dzień tygodnia: ';
    switch ($var2) {
        case 1:
            echo 'Poniedziałek<br><br>';
            break;
        case 2:
            echo 'Wtorek<br><br>';
            break;
        case 3:
            echo 'Środa<br><br>';
            break;
        case 4:
            echo 'Czwartek<br><br>';
            break;
        case 5:
            echo 'Piątek<br><br>';
            break;
        case 6:
            echo 'Sobota<br><br>';
            break;
        case 7:
            echo 'Niedziela<br><br>';
            break;
        default:
            echo 'Liczba spoza zakresu<br><br>';
            break;
    }

    echo 'Użycie pętli while()<br>';
    $i = 0;
    while($i < 5){
        echo 'Zmienna i wynosi '.$i.'<br>';
        $i++;
    }

    echo '<br>Użycie pętli for()<br>';
    for($j = 0; $j < 5; $j++){
        echo 'Zmienna j wynosi '.$j.'<br>';
    }

    echo '<br>Użycie zmiennej $_GET<br>';
    echo '<form method="GET">
            Podaj imię:<input type="text" name="imie">
            <input type="submit" value="Wyślij">
        </form>';
    echo 'Imię otrzymane ze zmiennej GET: '.$_GET["imie"].'<br><br>';

    echo 'Użycie zmiennej $_POST<br>';
    echo '<form method="POST">
            Podaj wiek:<input type="text" name="wiek">
            <input type="submit" value="Wyślij">
        </form>';
    echo 'Wiek otrzymany ze zmiennej POST: '.$_POST["wiek"].'<br><br>';

    echo 'Użycie zmiennej $_SESSION<br>';
    $_SESSION['miasto'] = 'Olsztyn';
    echo 'Miasto otrzymane ze zmiennej sesji: '.$_SESSION['miasto'];
?>