<?php
class Contact {

    function PokazKontakt(){
        $wynik = '
        <div class="contact">
            <form method = "post" name="LoginForm" enctype="multipart/form-data" action"'.$_SERVER['REQUEST_URI'].'">
                <table class="contact">
                    <tr><td class="zaw">Email: </td><td><input type="text"  class="contact" /></td></tr>
                    <tr><td class="zaw">Tytuł: </td><td><input type="text"  class="contact" /></td></tr>
                    <tr>
                    <td>Zawartość:</td>
                    <td class="zaw"><textarea></textarea></td>
                    </tr>
                    <tr><td></td><td><input type="submit" class="send-button" value="wyślij" /></td></tr>
                </table>
            </form>
        </div>
        
        ';
        return $wynik;
    }

    function PokazHaslo(){
        $wynik = '
        <div class="passrecov">
			<div class="passrecov">
				<form method="post" name="LoginForm" enctype="multipart/form-data" action="'.$_SERVER['REQUEST_URI'].'">
					<table class="passrecov">
						<tr><td class="log4_t">Email: </td><td><input type="text" name="email_recov" class="passrecov" /></td></tr>
						<tr><td></td><td><input type="submit" name="x1_submit" class="passrecov" value="wyślij" /></td></tr>
					</table>
				</form>
			 </div>
		</div>
		';
		return $wynik;
    }

    function WyslijMailKontakt($odbiorca){
        if(empty($_POST['email']) || empty($_POST['title']) || empty($_POST['content'])) {
            echo $this->PokazKontakt();
        }
        else {
            $mail['subject']   = $_POST['temat'];
            $mail['body']      = $_POST['tresc'];
            $mail['sender']    = $_POST['email'];
            $mail['recipient'] = $odbiorca;
            
            $header  = "From: Fromularz kontaktowy <".$mail['sender'].">\n";
            $header .= "MIME-Version: 1.0\nContent-Type: text/plain; charset-utf-8\nContent-Transfer-Encoding: ";
			$header .= "X-Sender: <".$mail['sender'].">\n";
			$header .= "X-Mailer: prapwww mail 1.2\n";
			$header .= "X-Priority: 3\n";
			$header .= "Return-Path: <".$mail['sender'].">\n";

            mail($mail['recipient'],$mail['subject'],$mail['body'], $header);

            echo 'Wiadomość wysłana';
        }
    }
    
    
    
    function PrzypomnijHaslo($odbiorca){
        if(empty($_POST['email_recovery'])) {														// Czy nie został wprowadzony email?
			echo $this->PokazHaslo();															// Wyświetl pole emaila do wypełnienia
		}

        else {
			$mail['sender']			= $_POST['email_recovery'];
			$mail['subject']		= "Password Recovery";
			$mail['body']			= "Password = haslo";
			$mail['recipient']		= $odbiorca;
			
			$header  = "From: Forumularz kontaktowy <".$mail['sender'].">\n";
			$header .= "MIME-Version: 1.0\nContent-Type: text/plain; charset-utf-8\nContent-Transfer-Encoding: ";
			$header .= "X-Sender: <".$mail['sender'].">\n";
			$header .= "X-Mailer: prapwww mail 1.2\n";
			$header .= "X-Priority: 3\n";
			$header .= "Return-Path: <".$mail['sender'].">\n";
			
			mail($mail['recipient'],$mail['subject'],$mail['body'],$header);					// Wysłanie emaila z hasłem
			
			echo 'Hasło wysłane';
		}

    }
}
?>