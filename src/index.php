<?php
// Home page of the application
include $_SERVER['DOCUMENT_ROOT']."/libraries/general.php";
show_premain();
?>
<h2>Gestione degli accessi alla palestra</h2>
<p>
    A causa dei provvedimenti dovuti all'emergenza covid-19 è 
    necessario gestire gli accessi alla palestra; per fare allenamento
    bisogna quindi registrarsi su questo sito.
</p>

<p>
    Per iniziare, iscriversi nell'apposita <a href = "/user/signup.php"><u>pagina</u></a>
    e seguire le indicazioni contenute nella <a href = "/guide.php"><u>guida</u></a>.
</p>
<?php show_postmain(); ?>