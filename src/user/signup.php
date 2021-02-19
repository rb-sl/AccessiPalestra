<?php
// Page used for signup
include $_SERVER['DOCUMENT_ROOT']."/libraries/general.php";

// Does not allow a registration with an active profile
if(isset($_SESSION['id']))
{
    $_SESSION['alert'] = "Utente già registrato";
    header("Location: /");
	exit;
}

show_premain();
?>

<h1>Registrazione nuovo profilo</h1>
<form method="POST" action="/user/signup_exe.php" class="flexcolumn marginunder">
	<input type="text" id="user" name="user" autofocus="autofocus" placeholder="Utente" onfocus="this.select()" required>
	<input id="psw" type="password" class="psw" name="psw" placeholder="Password" required>
	<input id="psw2" type="password" class="psw" placeholder="Ripeti password" required>
	
    <span id="err" class="dangercolor marginunder"></span>
    <input type="submit" id="submit" class="btn btn-primary" value="Registrati">
</form>

<p>Hai già un profilo? <a href="/user/login.php"><u>Accedi</u></a></p>

<script src="/user/credentials.js"></script>

<?php show_postmain(); ?>