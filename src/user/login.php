<?php
// Front end login page
include $_SERVER['DOCUMENT_ROOT']."/libraries/general.php";

// Login is disabled if there is an active already 
if(isset($_SESSION['id']))
{
    $_SESSION['alert'] = "Accesso già effettuato";
    header("Location: /");
	exit;
}

show_premain();
?>

<h1>Login</h1>
<form method="POST" action="/user/login_exe.php" class="flexcolumn marginunder">
	<input type="text" name="user" autofocus="autofocus" placeholder="Utente" onfocus="this.select()" required>
	<input type="password" name="psw" placeholder="Password" required>
	<input type="submit" class="btn btn-primary" value="Accedi">
</form>

<p>Oppure <a href="/user/signup.php"><u>Registrati<u></a></p>

<?php show_postmain(); ?>