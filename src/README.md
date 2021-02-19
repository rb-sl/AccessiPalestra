[English version](README-en.md)
# Codice sorgente
In questa cartella e nelle sue sottocartelle è riportato il codice sorgente, assieme a una breve descrizione di ogni file nei README.

## Configurazione del server per il deploy
Perché il sito funzioni correttamente, la root dell'applicazione (rappresentata da questa cartella) deve essere associata ad un server web (es. Apache); in un sistema LAMP si consiglia di inserirla in `/var/www`.

## File da aggiungere o modificare
Segue una lista delle modifiche da apportare per garantire il funzionamento dell'applicazione

### server_conf.json
In una cartella (possibilmente non accessibile al web server) deve essere presente il file `server_conf.json`, contenente informazioni sul sistema, nella forma
```
{
    "dbuser": "$USERNAME",
    "dbpass": "$PASSWORD",
    "dbname": "accessi_palestra"
}
```
Con le corrette credenziali.

### [general.php](libraries/general.php)
Questo file contiene le informazioni principali dell'applicazione e deve essere adattato al contesto. Alcune costanti necessitano di modifiche:
* `5 - CONF_PATH`: Percorso al file `server_conf.json`
* `6 - LOG_PATH`: Cartella dove si desidera vengano salvati i log dell'applicazione
* `9 - BOOTSTRAP_CSS_PATH`: Percorso del sorgente css di Bootstrap
* `10 - BOOTSTRAP_JS_PATH`: Percorso del sorgente JavaScript di Bootstrap
* `11 - JQUERY_PATH`: Percorso del sorgente di jQuery
* `12 - FPDF_ROOT`: Cartella root di FPDF
* `14 - AUTOCERT_PATH`: Percorso al file pdf contenente l'autocertificazione da stampare
* `15 - DISPLAY_NAME`: Il nome della palestra (Da mostrare nel title della scheda e nel menu principale)

### [generate_list.php](master/reg_mng/generate_list.php)
Il file di generazione della lista di partecipazione da stampare necessita di modifiche alle righe `72-81`: consistono nelle informazioni sui maestri, dove è possibile inserire i valori corretti e replicare il blocco, se necessario.

### Cron job
È necessario impostare un cron job per lo script `conflict_solver.php`. La forma consigliata è:
```
2,17,32,47 * * * * sudo /usr/bin/php7.0 /var/www/accessi_palestra/libraries/conflict_solver.php
```
Ovvero 2 minuti dopo le ore di chiusura, ogni giorno. È possibile raffinarlo per funzionare solo nei giorni in cui è presente un allenamento.

## Struttura del sito
![Mappa](../images/accessi_palestra.png)

L'immagine mostra la struttura degli script php. Le pagine blu offrono un'interazione all'utente. Le frecce sono codificate in base al colore:
* Nero: Normale transizione
* Blu: Inclusione dello script
* Arancione: Chiamate tramite JavaScript

Sono omesse le inclusioni di `general.php`, utilizzata in tutte le pagine (eccetto `fix_lib.php`).

## File in /

### [index.php](index.php)
La prima pagina raggiunta dal server, contiene una introduzione al sito e suggerisce le prime azioni da compiere per iscriversi con i relativi link.

### [guide.php](guide.php)
Guida dell'applicazione; in base allo stato dell'utente (registrato in sessione) mostra informazioni diverse. Contiene un link a questa repository.