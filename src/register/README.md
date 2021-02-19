# File in /register/

## Registro

### [register.php](register.php)
Pagina principale della sezione. Mostra le registrazioni attive effettuate per ogni atleta dell'utente e rimanda a sezioni utili della guida.

### [registration_add.php](registration_add.php)
Pagina frontend che permette la registrazione di atleti, sia singolarmente sia in gruppo. Sono selezionabili solo gli allenamenti con posti disponibili sufficienti per tutti gli atleti selezionati.

### [registration_add.js](registration_add.js)
File jQuery di supporto alla pagina php, permette di gestire il comportamento e il colore dei bottoni della pagina. Aggiorna il numero di posti disponibili attraverso i Server-Sent Events (SSE). 

### [registration_exe.php](registration_exe.php)
Script backend utilizzato per creare una registrazione. Si basa su una transazione che controlla che non vengano occupati più posti di quelli disponibili (in caso di registrazioni contemporanee). In caso di errori viene annullata e viene richiesto all'utente di ripetere l'operazione.

### [registration_del.php](registration_del.php)
Script di rimozione di una registrazione. Si appoggia a [fix_lib.php](../libraries/fix_lib.php) per aggiustare eventuali altre registrazioni dell'utente.

### [registration_sse.php](registration_sse.php)
Script utilizzato per mandare alle pagine di registro aggiornamenti sui posti disponibili. Le informazioni vengono stampate in formato JSON.

## Gestione atleti

### [athletes.php](athletes.php)
Pagina front-end usata per aggiungere, modificare e rimuovere atleti.

### [athlete_update.php](athlete_update.php)
Script di aggiornamento degli atleti. Cancella tutti gli atleti che non vengono inviati tramite POST e le relative registrazioni future (cosicché lo storico di partecipazione non viene modificato).

Gli atleti i cui dati vengono ricevuti vengono aggiunti o aggiornati.
