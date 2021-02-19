# File in /master/
La sezione è protetta per controllare che solo gli utenti abilitati possano accedervi.

### [master.php](master.php)
Pagina principale della sezione. Permette di raggiungere tutte le funzioni dedicate all'Accesso Maestro e scaricare le liste di partecipazione.

## Log di utilizzo

### [log.php](log.php)
Pagina utilizzata per mostrare agli utenti i log, ottenuti tramite richieste AJAX.

### [log_reader.php](log_reader.php)
Script utilizzato per stampare in json il contenuto di un file di log.

## Gestione dei parametri

### [parameters.php](parameters.php)
Pagina di frontend per l'aggiornamento dei parametri del sistema (tabella `properties` del database).

### [parameters_update.php](parameters_update.php)
Script per l'update dei parametri.

## Gestione degli slot di allenamento

### [slot_manager.php](slot_manager.php)
Pagina che permette di modificare data e ora degli allenamenti; corrisponde a quanto mostrato nelle pagine di registro.

### [slot_update.php](slot_update.php)
Script per l'aggiornamento degli slot. Gli slot eliminati nel frontend vengono disabilitati per non perdere gli storici, mentre le loro registrazioni sono eliminate regolarmente. Non sono permessi slot sovrapposti.

## Gestione delle date (/master/date_mng/)

### [date_manager.php](date_mng/date_manager.php)
Pagina frontend di gestione delle date di allenamento. Permette di cancellare allenamenti (una volta sola o ogni anno) e ripristinare date.

### [date_manager.js](date_mng/date_manager.js)
File JavaScript per gestire l'interazione con l'utente per la rimozione delle date.

### [date_ajax.php](date_mng/date_ajax.php)
Permette di ottenere gli slot attivi in una data selezionata per la rimozione.

### [date_remove.php](date_mng/date_remove.php)
Script di inserimento nelle tabelle del database le date rimosse. L'esecuzione è organizzata su più branch in base alle scelte effettuate dall'utente.

### [date_restore.php](date_mng/date_restore.php)
Script utile a ripristare le date selezionate dall'utente. 

## Visualizzazione dei registri (/master/reg_mng/)

### [show_register.php](reg_mng/show_register.php)
Pagina frontend che mostra lo stato attuale di una giornata, con atleti registrati, orario e `probability` della prenotazione.

### [generate_list.php](reg_mng/generate_list.php)
Script FPDF per la generazione della lista di partecipazione da stampare. Comprende campi vuoti per temperatura e firma.

### [generate_forgroup.php](reg_mng/generate_forgroup.php)
Script FPDF per la generazione della lista di partecipanti.

## Gestione degli utenti (/master/user_mng/)

### [show_users.php](user_mng/show_users.php)
Pagina frontend, mostra gli utenti e i loro atleti; permette ai maestri di concedere o revocare (se parte del proprio sotoalbero di `granted_by`) l'Accesso Maestro di altri utenti.

### [grant_master.php](user_mng/grant_master.php)
Script utilizzato per concedere l'Accesso Maestro a un utente.

### [revoke_master.php](user_mng/revoke_master.php)
Script utilizzato per revocare l'Accesso Maestro a un utente.