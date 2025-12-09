// gettheDate 
// Funkcja pobiera biezaca date i ustawia ja na stronie
function getTheDate(){
    Todays = new Date();
    TheDate = Todays.getDate() + "." + (Todays.getMonth() + 1) + "." + (Todays.getFullYear());
    document.getElementById("data").innerHTML = TheDate;
}

var timerID = null;
var timerRunnung = false;

// stopclock
// Funkcja zatrzymuje odliczanie czasu
function stopClock(){
    if(timerRunnung)
        clearTimeout(timerID);
    timerRunnung = false;
}

/*
* startclock
* Funkcja startuje odliczanie czasu. Najpierw zatrzymuje je, potem ustawia biezaca date,
* a na koncu wywoluje funkcje showtime(), ktora wyswietla aktualny czas
*/
function startClock(){
    // zatrzymuje odliczanie czasu
    stopClock();
    // ustawia biezaca date
    getTheDate();
    // wywoluje funkcje showtime(), ktora wyswietla aktualny czas
    showTime();
}

/*
* showtime
* Funkcja wyswietla aktualny czas w formacie hh:mm:ss AM/PM
* i ustawia timeout na 1 sekunde, by ponownie wywolac sama siebie
*/
function showTime(){
    var now = new Date(); // pobiera aktualny czas z systemu

    // wydobywa godzine, minuty i sekundy z now
    var hours = now.getHours();
    var minutes = now.getMinutes();
    var seconds = now.getSeconds();

    // tworzy lancuch znakow zgodny z formatem hh:mm:ss
    var timeValue = hours;
    timeValue += ((minutes < 10) ? ":0" : ":") + minutes;
    timeValue += ((seconds < 10) ? ":0" : ":") + seconds;
    document.getElementById("zegarek").innerHTML = timeValue; // ustawia wartosc timeValue w elemencie o id=zegarek w html
    timerID = setTimeout("showTime()",1000); // ustawia timeout na 1 sekunde i wywoluje ponownie showtime()
    timerRunnung = true; // ustawia flaga timerRunning na true, mowiaca, ze timer jest wlaczony
}