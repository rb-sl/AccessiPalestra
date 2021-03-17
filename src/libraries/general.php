<?php
// Main application library, contains functions common to most pages
session_start();

const CONF_PATH = "/var/www/server_conf.json";
const LOG_PATH = "/var/log/accessi_palestra/";
const LOG_SOLVER_PATH = LOG_PATH."solver/";

const BOOTSTRAP_CSS_PATH = "/ui/bootstrap.css";
const BOOTSTRAP_JS_PATH = "/ui/bootstrap.js";
const JQUERY_PATH = "/ui/jquery.js";
const FPDF_ROOT = "/var/www/accessi_palestra/fpdf/";

const AUTOCERT_PATH = "/user/autocertificazione.pdf";
const DISPLAY_NAME = "NomePalestra";

// Debugging function
function errors()
{
	ini_set("display_errors", 1);
	ini_set("display_startup_errors", 1);
	error_reporting(E_ALL);
}
if(isset($_GET['e']))
	errors();

// Normal logging function
function writelog($action)
{
	file_put_contents(LOG_PATH."log_".date("Y-m-d").'.txt', 
		date("H:i:s", time())." [".$_SERVER['REMOTE_ADDR']."] - [".$_SESSION['user']."] $action\n\n", FILE_APPEND);
}

// Logging function for the conflict solver
function writesolver($action)
{
	file_put_contents(LOG_SOLVER_PATH."log_".date("Y-m-d").'.txt', 
		date("H:i:s", time())." -  $action\n\n", FILE_APPEND);
}

// Access and privilege control
function chk_access($master = false)
{
	if(!isset($_SESSION['user']))
    {
    	$_SESSION['err'] = 1;
    	header('Location: /');
    	exit;
    }
	if($master and !isset($_SESSION['master']))
    {
    	$_SESSION['err'] = 3;
    	header('Location: /');
    	exit;
    }
}

// Obtains the server's configuration
function get_server_conf()
{
	$conf = fopen(CONF_PATH, "r") or die("Unable to open configuration file");
	$serv = fread($conf, filesize(CONF_PATH));
	fclose($conf);
	return json_decode($serv);
}

// Connection to MySQL DB and error handling
function connect()
{
	global $mysqli;
	$conf = get_server_conf();
    $mysqli = new mysqli("localhost", $conf->dbuser, $conf->dbpass, $conf->dbname);
	if ($mysqli->connect_errno) 
    {
    	echo "Connection error ".$mysqli->connect_errno.": ".$mysqli->connect_error;
    	writelog("[conn_err] ".$mysqli->connect_errno.": ".$mysqli->connect_error);
		show_postmain();
        exit();
	}
}                                                                                                                                                                                                        

// Maps a day number to the italian name 
function getWeekdayIT($weekday) {
	switch($weekday) 
	{
		case 1:
			return "Lunedì";
		break;
		case 2:
			return "Martedì";
		break;
		case 3:
			return "Mercoledì";
		break;
		case 4:
			return "Giovedì";
		break;
		case 5:
			return "Venerdì";
		break;
		case 6:
			return "Sabato";
		break;
		case 0:
			return "Domenica";
		break;
	}
}

// Maps a day number to the english name 
function getWeekdayEN($weekday) {
	switch($weekday) 
	{
		case 1:
			return "Monday";
		break;
		case 2:
			return "Tuesday";
		break;
		case 3:
			return "Wednesday";
		break;
		case 4:
			return "Thursday";
		break;
		case 5:
			return "Friday";
		break;
		case 6:
			return "Saturday";
		break;
		case 0:
			return "Sunday";
		break;
	}
}

// Function to request the confirmation of a client-side action
function confirm($quest)
{
	return "onclick=\"return confirm('".addslashes(quoteHTML($quest))."');\"";
}

// Changes " in &quot; for visualization purposes
function quoteHTML($str)
{
	return str_replace("\"", "&quot;", $str);
}

// Returns the difference between two given times
function time_diff($first, $second) 
{
    return (strtotime($first) - strtotime($second)) / 3600;
}

// Gets the properties from the db
function get_prop()
{
	$s = prepare_stmt("SELECT * FROM properties");
	$p = execute_stmt($s);
	$s->close();
	return $p->fetch_assoc();
}

// Statement preparation and error handling
function prepare_stmt($query)
{
	global $mysqli;
	if(!($stmt = $mysqli->prepare($query)))                                                                                                                                                                                                    
		query_error("prepare", $query);
	return $stmt;
}

// Statement execution and error handling
function execute_stmt($stmt)
{
	$stmt->execute();
	if($stmt->errno !== 0)
		echo "Execute failed: (".$stmt->errno.") ".$stmt->error;

	$res = $stmt->get_result();
    if($stmt->errno !== 0)
		echo "Getting result set failed: (".$stmt->errno.") ".$stmt->error;
		
	return $res;
}

function query_error($stage, $query)
{
	global $mysqli;
	echo "<div class='row border'>".$mysqli->errno."<br>".$mysqli->error."<br>$query</div>";
	writelog("[query_err] [$stage] [".$mysqli->errno."] ".$mysqli->error."\n>>$query");
	show_postmain();
	exit();
}

// Shows static page information
function show_premain($title = "")
{
	if($title != "")
		$title .= " -";
    
  	echo "<!DOCTYPE html> 
	<html> 
    	<head> 
			<meta charset='utf-8'>
    		<meta name='viewport' content='width=device-width, initial-scale=1, shrink-to-fit=no'>
    		<meta name='description' content=''>
    		<meta name='author' content=''>

    		<title>$title ".DISPLAY_NAME."</title>

    		<!-- Bootstrap core CSS -->
    		<link href='".BOOTSTRAP_CSS_PATH."' rel='stylesheet'>
            
    		<!-- Bootstrap core JavaScript -->
    		<script src='".JQUERY_PATH."'></script>
			<script src='".BOOTSTRAP_JS_PATH."'></script>
            
            <!-- Custom graphical styles -->
            <link href='/ui/custom.css' rel='stylesheet'>
		</head> 
		
        <nav class='navbar navbar-expand-lg navbar-dark bg-dark static-top ztop'>
			<div class='container'>
       			<a class='navbar-brand' href='/'>".DISPLAY_NAME." - Accessi</a>
         		<button class='navbar-toggler' type='button' data-toggle='collapse' data-target='#navbarResponsive' aria-controls='navbarResponsive' aria-expanded='false' aria-label='Toggle navigation'>
          			<span class='navbar-toggler-icon'></span>
        		</button>
        		<div class='collapse navbar-collapse' id='navbarResponsive'>
          			<ul class='navbar-nav ml-auto'>
            			<li><a class='nav-link' href='/register/register.php'>Partecipazione allenamenti</a></li>
						<li><a class='nav-link' href='/guide.php'>Guida</a></li>";
	if(isset($_SESSION['master']))
		echo "<li><a class='nav-link' href='/master/master.php'>Accesso maestro</a></li>";

	// Prints options based on the login status					
    if(!isset($_SESSION['user']))
        echo "<li class='nav-item'><a class='nav-link' href='/user/login.php'>Login</a></li>";
	else
		echo "<li class='nav-item'><a class='nav-link' href='/user/profile.php'>".$_SESSION['user']."</a></li>";

    echo "			</ul>
            	</div>
        	</div>
    	</nav>
    	<div class='container' id='main'>";
		
	// Shows error messages
	if(isset($_SESSION['err']) and $_SESSION['err'] != "")
	{
		  echo "<h3 style='color:red'>Attenzione</h3>";
		  $stop = false;
  		switch($_SESSION['err'])
  		{
    		case 1:
				echo "<h4>Per accedere a questa funzione effettuare il <a href='/user/login.php'><u>login</u></a></h4>";
				$stop = true;
      			break;
    		case 2:
      			echo "<h4>Login errato</h4>";
      			break;
    		case 3:
				echo "<h4>Utente non autorizzato</h4>";
				$stop = true;
     			break;
        	case 4:
				echo "<h4>Login disabilitato</h4>";
				$stop = true;
     			break;
			default:
				$stop = true;
      			break;
  		}
		$_SESSION['err']="";
		
		if($stop)
		{
			show_postmain();
			exit;
		}
	}

	// Shows a message in the page
	if(isset($_SESSION['msg']) and $_SESSION['msg'] != "" )
	{
    	echo "<h3>".$_SESSION['msg']."</h3>";
    	$_SESSION['msg']="";
	}
}

// Final static elements, common to all pages
function show_postmain()
{
	global $mysqli;
	// Shows a message through an alert
	if(isset($_SESSION['alert']) and $_SESSION['alert'] != "")
	{
		echo "<script>
    		$(function(){
    			$(document).ready(function(){
    				alert(\"".$_SESSION['alert']."\");
  				});
  	  		});
		</script>";
    	$_SESSION['alert']="";
	}

	// Prints the page's closing elements
	echo "</div>
    	<div class='footer'>
			&rho;B
		</div>
	</html>";

	if(isset($mysqli))
		$mysqli->close();
}
?>