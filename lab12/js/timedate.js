

// gettheDate 
// Funkcja pobiera biezaca date i ustawia ja na stronie
function gettheDate(){
    Todays = new Date();
    TheDate = "" + Todays.getDate() + "."+ (Todays.getMonth()+1) + "." + Todays.getFullYear();
    document.getElementById("data").innerHTML = TheDate; // ustawia biezaca date w elemencie o id=data

}


var timerID = null;
var timerRunning = false;


// stopclock
// Funkcja zatrzymuje odliczanie czasu
function stopclock(){
    if(timerRunning)
        clearTimeout(timerID);
    timerRunning = false;
}


/*
* startclock
* Funkcja startuje odliczanie czasu. Najpierw zatrzymuje je, potem ustawia biezaca date,
* a na koncu wywoluje funkcje showtime(), ktora wyswietla aktualny czas
*/
function startclock(){
    // zatrzymuje odliczanie czasu
    stopclock();
    // ustawia biezaca date
    gettheDate();
    // wywoluje funkcje showtime(), ktora wyswietla aktualny czas
    showtime();
}

/*
* showtime
* Funkcja wyswietla aktualny czas w formacie hh:mm:ss AM/PM
* i ustawia timeout na 1 sekunde, by ponownie wywolac sama siebie
*/
function showtime(){
    
    var now = new Date(); // pobiera aktualny czas z systemu
    
    // wydobywa godzine, minuty i sekundy z now
    var hours = now.getHours();
    var minutes = now.getMinutes();
    var seconds = now.getSeconds();
    
    // tworzy lancuch znakow zgodny z formatem hh:mm:ss 
    var timeValue = hours
    timeValue += ((minutes < 10) ? ":0" : ":") + minutes;
    timeValue += ((seconds < 10) ? ":0" : ":") + seconds;
    

    document.getElementById("zegarek").innerHTML = timeValue;     // ustawia wartosc timeValue w elemencie o id=zegarek w html
    
    
    timerID = setTimeout("showtime()",1000); // ustawia timeout na 1 sekunde i wywoluje ponownie showtime()
    
    
    timerRunning = true;   // ustawia flaga timerRunning na true, mowiaca, ze timer jest wlaczony
}

