<?php
// Guide page, shows the manual of the application
// (Does not show non-relevant entries for the current user)
include $_SERVER['DOCUMENT_ROOT']."/libraries/general.php";
show_premain("Guida");

// Checks the permission level
if(isset($_SESSION['user']))
	if(isset($_SESSION['master']))
		$level = 0;
	else
		$level = 1;
else
	$level = 5;
?>

<div class="textwall">
	<h2>Guida all'applicazione</h2>

    <h3>Indice</h3>
	<ul class="nobul border">
		<li>
			<a href="#access">Accesso utenti</a>
			<ul class="nobul">
				<li><a href="#signup">Registrazione e login</a></li>
<?php
if($level <= 1)
	echo "<li><a href='#profile'>Modificare il profilo e disconnettersi</a></li>";
?>
			</ul>
		</li>
<?php
if($level <= 1)
{
	$sign = "";
	$log = "";
	$in = "";
	echo "<li>
			<a href='#register'>Registarsi a un allenamento</a></li>
			<ul class='nobul'>
				<li><a href='#athletes'>Aggiunta e modifica atleti</a></li>
				<li><a href='#register'>Registrazione</a></li>
				<li><a href='#delete'>Cancellare un'iscrizione</a></li>
			</ul>
			<a href='#before'>Prima degli allenamenti</a></li>
		</li>";
}
else
{
	$sign = "<a href='/user/signup.php'><u>";
	$log = "<a href='/user/login.php'><u>";
	$in = "</u></a>";
}
if($level == 0)
	echo "<li>
			<a href='#master'>Accesso maestro</a>
			<ul class='nobul'>
				<li><a href='#lists'>Visualizzare e scaricare le liste di partecipanti</a></li>
				<li><a href='#logs'>Visualizzare i log di utilizzo dell'applicazione</a></li>
				<li><a href='#dates'>Cancellare e ripristinare delle date</a></li>
				<li><a href='#slots'>Visualizzare e modificare giorni e orari</a></li>
				<li><a href='#prop'>Visualizzare e modificare il periodo di iscrizione</a></li>
				<li><a href='#user'>Visualizzare e gestire gli utenti</a></li>
			</ul>
		</li>"
?>
		<li><a href="https://github.com/rb-sl/AccessiPalestra">Codice sorgente e ulteriori informazioni&#128279;</a></li>
	</ul>
	<div id="access">
  		<h3 id="reg">Accesso all'applicazione</h3>
		
		<h4 id="signup">Registrazione e login</h4>
		<p>
			Per poter partecipare agli allenamenti bisogna iscriversi nell'apposita <?=$sign?>pagina<?=$in?>. 
			Le credenziali inserite dovranno essere utilizzate per i successivi accessi dalla pagina di <?=$log?>login<?=$in?>. 
		</p>
<?php
if(!isset($level) or $level > 1)
{
	echo "</div>
		</div>";
	show_postmain();
	exit;
}

connect();
$prop = get_prop();
?>
		<h4 id="profile">Modificare il profilo e disconnettersi</h4>
		<p>
			Per modificare nome utente e password premere sul proprio nome dal menu principale. 
			Dalla pagina raggiunta è possibile effettuare il logout e modificare le informazioni del profilo. 
		</p>
	</div>

	<br>
	<div id="register">
		<h3>Registarsi a un allenamento</h3>
		<p>
			Per registrarsi è necessario andare in <a href="/register/register.php"><u>Partecipazione allenamenti</u></a>. 
			Dalla pagina che si presenta è possibile aggiungere o modificare gli atleti che si desidera iscrivere e 
			visualizzare le registrazioni già effettuate.
		</p>

		<h4 id="athletes">Aggiunta e modifica atleti</h4>
		<p>
			Premendo il pulsante <span class="infocolor">Aggiungi/Modifica atleti</span> si accede a una pagina in cui
			inserire nomi e cognomi degli atleti da registrare. È anche possibile modificare o rimuovere i nomi 
			di atleti già inseriti.
			Premendo su <span class="primarycolor">Salva modifiche</span> si completa la modifica, mentre 
			<span class="warningcolor">Annulla</span> scarta tutti i cambiamenti e riporta alla pagina precedente.
		</p>

		<h4 id="register">Registrazione</h4>
		<p>
			Dalla pagina di registrazione è possibile selezionare <span class="primarycolor"> Nuova registrazione</span> 
			per iscrivere un singolo atleta, oppure <span class="primarycolor">Registra tutti</span>. 
			Premendo quindi su una delle date disponibili (fino ai successivi <?=$prop['days_before']?> giorni) vengono mostrati 
			gli orari in cui è possibile fare allenamento e, tra parentesi, il numero di posti ancora disponibili. Gli orari 
			non disponibili per il numero di atleti selezionati sono indicati in <span class="dangercolor">rosso</span>.
		</p>
		<p id="colors">
			Per registare gli atleti, selezionare gli orari desiderati. Nel giorno in questione, 
			<b>un orario <span class="successcolor">verde</span> garantisce l'iscrizione, mentre una serie di orari 
			<span class="warningcolor">gialli</span> indica che almeno un allenamento selezionato della giornata è garantito</b> 
			(a meno di cancellazioni di lezioni), al fine di permettere a tutti di poter partecipare. Per terminare la procedura 
			premere su <span class="primarycolor">Registra</span>; verrà mostrato lo stato delle registrazioni attive.
		</p>
		<p>
			La lista di partecipanti e orari sarà comunicata alla fine del periodo di registrazione (<?=$prop['hours_before']?> 
			ore prima dell'inizio degli allenamenti della giornata) sul gruppo. 
		</p>

		<h4 id="delete">Cancellare un'iscrizione</h4>
		<p>
			Se si desidera cancellare una registrazione, è sufficiente premere il pulsante <span class="dangercolor">Rimuovi</span> 
			dalla pagina di	<a href="/register/register.php"><u>partecipazione</u></a> e confermare la scelta.
		</p>
	</div>

	<div id="before">
		<h3 class="dangercolor">Prima degli allenamenti</h3>
		Alcune regole e informazioni necessarie per partecipare agli allenamenti, dopo aver effettuato la registrazione:
		<ul>
			<li>
				È necessaria l'autocertificazione, da consegnare ogni 14 giorni 
				(<a href="<?=AUTOCERT_PATH?>" target="_blank"><u>Scaricabile da qui</u></a>)
			</li>
			<li>Non è possibile fare allenamento senza essersi prenotati su questo sito</li>
			<li>Se si è già prenotato e non si può partecipare bisogna cancellarsi dall'allenamento</li>
			<li>Genitori e accompagnatori possono accompagnare gli atleti fino alla palestra, ma non possono restare</li>
			<li>Bisogna venire già vestiti per fare allenamento</li>
			<li>Gli orari di inizio lezione sono categorici</li>
			<li>Bisogna avere la mascherina, che andrà tenuta al braccio durante l'allenamento</li>
			<li>Le scarpe devono essere messe in un sacchetto di plastica, poi dentro il borsone</li>
			<li>All'entrata viene misurata la temperatura degli atleti; con più di 37.5°C non è possibile fare allenamento</li>
			<li>Le borse devono essere poste a distanza di 1 metro</li>
			<li>Non è possibile utilizzare gli spogliatoi e i servizi igienici degli stessi</li>
		</ul>
	</div>

<?php
if($level > 0)
{
	echo "</div>";
	show_postmain();
	exit;
}
?>
	<br>
	<div id="master">
		<h3>Accesso maestro</h3>
		Dalla pagina di amministrazione è possibile visualizzare gli elenchi di atleti registrati e gestire i 
		parametri dell'applicazione.

		<h4 id="lists">Visualizzare e scaricare le liste di partecipanti</h4>
		<p>
			Le liste visualizzabili sono divise in non definitive e partecipazioni. Una lista diventa definitiva 
			<b>5 minuti dopo</b> la chiusura del periodo di iscrizione (<?=$prop['hours_before']?> ore prima 
			dell'inizio degli allenamenti).
		</p>

		<h5>Liste non definitive</h5>
		<p>
			Selezionando una data e premendo il pulsante <span class="infocolor">Visualizza</span> si raggiunge 
			una pagina che mostra le informazioni sugli atleti registrati al momento. Lo stato dell'iscrizione 
			corrisponde a quanto descritto nella <a href="#colors">sezione precedente</a>.
		</p>

		<h5>Partecipazioni</h5>
		<p>
			Da questu menu è possibile scaricare le liste definitive della giornata selezionata: 
			<span class="primarycolor">Scarica lista da stampare</span>	genera un file pdf che rappresenta il 
			registro con le temperature per la giornata, mentre premendo <span class="primarycolor">Scarica 
			lista per il gruppo</span> si ottiene un documento con la sola lista dei partecipanti.
			Sono mostrate le liste dei precedenti <?=$prop['days_list']?> giorni.
		</p>

		<h4 id="logs">Visualizzare i log di utilizzo dell'applicazione</h4>
		<p>
			Premendo il pulsante Visualizza log di utilizzo si accede a una pagina dove è possibile selezionare 
			una data e vedere le azioni	degli utenti.
		</p>

		<h4 id="dates">Cancellare o ripristinare delle date</h4>
		<p>
			Premendo il pulsante <span class="warningcolor">Cancella o ripristina date</span> si raggiunge 
			la pagina di gestione delle date degli allenamenti.
		</p>

		<h5>Cancellare date</h5>
		<p>
			La procedura di rimozione consiste nel selezionare una serie di parametri:
			<ul>
				<li>
					<span class="infocolor">Singola data</span> o <span class="infocolor">Periodo</span>: 
					permette di rimuovere una sola giornata o tutte le giornate comprese tra le due date inserite
				</li>
				<li>
					Data/e: la data o il periodo da rimuovere
				</li>
				<li>
					<span class="infocolor">Una volta sola</span> o <span class="infocolor">Ogni anno</span>: 
					definisce se la rimozione avviene solo per la data indicata o se deve essere ripetuta ogni anno
				</li>
				<li>
					Orari: se viene rimossa una sola data per l'anno corrente è possibile selezionare quali orari 
					togliere. Per cancellare interamente la giornata selezionare	tutte le voci
				</li>
			</ul>
			Una volta selezionati i parametri desiderati premere il pulsante <span class="dangercolor">Rimuovi date e 
			orari selezionati</span>. 
			<b>Questa operazione cancellerà qualunque registrazione effettuata per i giorni selezionati</b>.
		</p>

		<h5>Ripristinare date</h5>
		<p>
			Se si desidera rendere di nuovo disponibile una data precedentemente cancellata, la sezione Date rimosse 
			e ripristino permette di mostrare le date cancellate (divise in rimosse una sola volta o tutti gli anni); 
			per riaggiungerle, selezionare date e orari desiderati e premere <span class="successcolor">Ripristina date 
			e orari selezionati</span>.
		</p>

		<h4 id="slots">Visualizzare e modificare giorni e orari</h4>
		<p>
			Per modificare gli allenamenti premere sul pulsante <span class="infocolor">Modifica giorni e orari 
			degli allenamenti</span>. Dalla pagina raggiunta si possono visualizzare, modificare o eliminare gli 
			allenamenti attualmente attivi, oltre che aggiungerne di nuovi. Una volta completate le modifiche desiderate, 
			premere su <span class="successcolor">Salva</span> per renderle effettive o <span class="warningcolor">
			Annulla</span> per scartarle. <b>Cancellando un allenamento tutte le registrazioni future associate saranno perse</b>.
		</p>

		<h4 id="prop">Visualizzare e modificare il periodo di iscrizione</h4>
		<p>
			È possibile visualizzare il periodo di apertura e chiusura delle iscrizioni, premendo su 
			<span class="primarycolor">Modifica periodo di iscrizione</span>. Se si desidera modificarlo è sufficiente 
			cambiare i parametri e premere su <span class="primarycolor">Salva</span> (Il massimo numero di giorni della prenotazione è 7); 
			da questa pagina è anche possibile cambiare il periodo in cui è possibile scaricare le liste di partecipanti.
		</p>

		<h4 id="user">Visualizzare e gestire gli utenti</h4>
		<p>
			Accedendo alla sezione vengono visualizzati gli utenti e il loro ultimo accesso. È possibile concedere 
			o revocare i privilegi per l'accesso maestro.
		<p>
	</div>
</div>
<?php show_postmain(); ?>