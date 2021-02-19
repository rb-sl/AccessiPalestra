# File in /libraries/
La cartella `libraries` contiene script utili a più pagine e il file di risoluzione dei conflitti.

## [general.php](general.php)
Questo file contiene funzioni di base dell'applicazione, riunite per evitare eccessive ripetizioni.

### `errors()`
Funzione di debug, attivata settando `e` nel GET.

### `writelog($action)`
Permette di aggiungere ai log la stringa `$action`, completa di orario, utente e suo indirizzo IP.

### `writesolver($action)`
Funzione di log per lo script `conflict_solver.php`

### `chk_access($master)`
Controllo accessi, blocca il caricamento della pagina se l'utente non è autorizzato.

### `get_server_conf()`
Ritorna un oggetto contenente le informazioni del file di configurazione

### `connect()`
Svolge le operazioni di connessione al DBMS e imposta l'oggetto `$mysqli` per effettuare le query.

### `getWeekdayIT($weekday)`
Funzione per ottenere il giorno della settimana dato il numero, in italiano.

### `getWeekdayEN($weekday)`
Funzione per ottenere il giorno della settimana dato il numero, in inglese.

### `confirm($quest)`
Stampa la richiesta in un tag HTML.

### `time_diff($first, $second)`
Ritorna la differenza in ore tra due istanti di tempo.

### `get_prop()`
Ritorna un oggetto contenente le informazioni della tabella `properties` del database.

### `prepare_stmt($query)`
Data una query SQL, ritorna un prepared statement.

### `execute_stmt($stmt)`
Esegue il prepared statement `$stmt`.

### `query_error($stage, $query)`
Stampa alcune informazioni in caso di errore SQL.

### `show_premain($title)`
Stampa le informazioni statiche iniziali della pagina e setta il title della scheda. Stampa anche eventuali errori scritti nella sessione e li consuma.

### `show_postmain()`
Stampa le informazioni finali della pagina e un'eventuale alert.

## [fix_lib.php](fix_lib.php)
Questo file contiene funzioni utili a correggere le `probability` delle registrazioni in seguito ad azioni degli utenti in maniera ricorsiva

### `adjust_probability($date, $slot, $log, $avoidprob)`
Questa funzione permette di ridurre la somma delle probabilità al numero di posti disponibili, selezionando un'iscrizione con `probability` < 1 ed eliminandola (eseguendo l'operazione tante volte quanto necessario). Dopo l'eliminazione chiama la `fix_on_delete`.
Accetta la data e opzionalmente lo slot (se non indicato esegue per tutti) da bilanciare, il log temporaneo e un flag che se settato permette la cancellazione anche di iscrizioni con probabilità 1.

### `fix_on_delete($athl, $date, $prob, $log, $avoidprob)`
Funzione richiamata dopo una cancellazione; aumenta la probabilità delle iscrizioni dello stesso atleta e controlla l'integrità. Richiama poi `adjust_probability` per correggere le modifiche.

### `adjust_slot($slot)`
Funzione richiamata in caso di modifica dei posti disponibili, per cui è necessario rimuovere alcune iscrizioni.

## [conflict_solver.php](conflict_solver.php)
Questo script viene attivato come Cron Job, e permette, dopo la chiusura delle iscrizioni, di impostare a 1 la `probability` del maggior numero di registrazioni possibile, richiamando le funzioni di `fix_lib` per non eccedere il numero di posti disponibili.