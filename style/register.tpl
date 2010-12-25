<!--section-start::NEW_USER-->
<h3>Neuen Benutzer anlegen</h3>
<p>
    Um an der Online-Abrechnung mit iBill teilnehmen zu können, benötigen Sie ein Benutzerkonto. Bitte geben Sie dazu ihren realen Namen und ihre gültige E-Mail-Adresse an. Außerdem benötigen Sie zum Login einen Benutzernamen und ein Passwort, die Sie frei wählen können.
</p>
{..error..}
<form action="register.html" method="post">
    <fieldset>
        <h4>Ihr Name:</h4>
        <input type="text" name="new_real_name" maxlength="255">
        <span class="small">[min. 3, max. 255 Zeichen]</span>
        
        <h4>Ihr E-Mail-Adresse:</h4>
        <input type="text" name="new_mail" maxlength="255">
        <span class="small">[gültige E-Mail-Adresse]</span>
    
        <h4>Benutzername:</h4>
        <input type="text" name="new_name" maxlength="100">
        <span class="small">[min. 3, max. 100 Zeichen]</span>
        
        <h4>Passwort:</h4>
        <input type="password" name="new_pass" maxlength="100">
        <span class="small">[min. 6, max. 100 Zeichen]</span>
       
        <h4>Passwort wiederholen:</h4>
        <input type="password" name="wdh_pass" maxlength="100">
        <span class="small">[min. 6, max. 100 Zeichen]</span>
        
        <p>
            <button class="pointer" type="submit" name="register_send" value="1">Registrierung durchführen</button>
        </p>
   </fieldset>
</form>
<!--section-end::NEW_USER-->

<!--section-start::ERROR_USER-->
<h3 class="error_h">Es trat ein Fehler auf...</h3>
<p>
    Der angegebene Benutzername existiert bereits. Bitte wählen Sie einen anderen Benutzernamen.
</p>
<!--section-end::ERROR_USER-->

<!--section-start::ERROR_PASS-->
<h3 class="error_h">Es trat ein Fehler auf...</h3>
<p>
    Die angegebenen Passwörter sind nicht gleich. Bitte geben Sie ihr gewünschtes Passwort zweimal exakt gleich ein.
</p>
<!--section-end::ERROR_PASS-->

<!--section-start::ERROR_DATA-->
<h3 class="error_h">Es trat ein Fehler auf...</h3>
<p>
    Beim Überprüfen der angegebenen Daten trat ein Fehler auf. Bitte überprüfen Sie, ob Sie ihren realen Namen und eine gültige E-Mail-Adresse eingegeben haben. Kontrollieren Sie auch, ob alle eingegebenen Daten den jeweiligen Bedinungen entsprechen.
</p>
<!--section-end::ERROR_DATA-->
