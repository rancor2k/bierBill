<!--section-start::DOCTYPE--><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd"><!--section-end::DOCTYPE-->

<!--section-start::BODY-->
    <body>
        <div id="main">

            <div id="header">
                <h1>
                    <a href="?go=start" title="zur Startseite">
                        iBill - Abrechnung leicht gemacht
                    </a>
                </h1>
            </div>

            <div id="menu_left">
                {..menu1..}
            </div>

            <div id="content">
                <div id="content_inner">
                    {..content..}
                </div>
            </div>

            <div id="footer" class="center">
                <span class="small">
                    &copy; 2010 - iBill-Systems - <a href="?go=admin">Impressum</a>
                </span>
            </div>

        </div>
    </body>
<!--section-end::BODY-->

<!--section-start::MENU1-->
            <h4>Login</h4>
            <form action="login.html" method="post">
				<ul style="padding-left:0px;">
					<li>
						<p>
							<label class="pointer" for="user_id">Benutzername:</label><br>
							<input type="text" name="user" id="user_id" size="15" maxlength="100" value="">
							<label class="pointer" for="pass_id">Passwort:</label><br>
							<input type="password" name="pass" id="pass_id" size="15" maxlength="100" value="">
						</p>
					</li>
					<li>
						<p>
							<button class="pointer" type="submit">Anmelden</button>
						</p>
					</li>
				</ul>
            </form>
            <h4>Service</h4>
            <ul>
                <li><a href="register.html">Registrieren</a></li>
                <li><a href="info.html">Informationen</a></li>
                <li><a href="imprint.html">Impressum</a></li>
            </ul>
<!--section-end::MENU1-->

<!--section-start::MENU2-->
            <h4>iBill</h4>
            <ul>
                <li><a href="dash.html">Dashboard</a></li>
                <li><a href="account.html">Kontostand</a></li>
                <li><a href="bills.html">Rechnungen</a></li>
                <li><a href="payments.php">Zahlungen</a></li>
            </ul>
            <h4>Account</h4>
            <ul>
                <li><a href="settings.html">Profil/Einstellungen</a></li>
                <li><a href="email.html">E-Mail ändern</a></li>
                <li><a href="password.html">Passwort ändern</a></li>
                <li><a href="logout.html">Logout</a></li>
            </ul>
<!--section-end::MENU2-->

<!--section-start::START-->
<h2>Herzlich Willkommen ...</h2>
<h3>... im High Noon Kino Tübingen</h3>
<p>
    Auf dieser Internetseite präsentieren wir Ihnen unser Programm und unser Kino. Viel Spaß!
</p>
<p>
    <a href="?go=programm">Jetzt im Kino</a><br>
    <a href="?go=vorschau">Demnächst im Kino</a><br>
    <a href="?go=reservierung">Online-Reservierung</a><br>
    <a href="?go=saal">Der Saal</a><br>
    <a href="?go=preise">Preise</a><br>
</p>
<h3>Zurzeit läuft unter anderem dieser Film:</h3>
{..movie..}
<!--section-end::START-->
