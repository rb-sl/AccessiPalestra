<?php 
// Frontend page used to update a user's profile
include $_SERVER['DOCUMENT_ROOT']."/libraries/general.php";
chk_access();
connect();
show_premain("Profilo");

$stmt = prepare_stmt("SELECT * FROM users WHERE user_id=?");
$stmt->bind_param("i", $_SESSION['id']);
$ret = execute_stmt($stmt);

$user=$ret->fetch_assoc();
?>
<h2>Profilo di <?=$_SESSION['user']?> <a href="/user/logout.php" class="btn btn-primary">Logout</a></h2>

<form method="POST" action="profile_update.php">
	Nome utente:<br>
    <input id="user" style="margin-bottom: 5px;" type="text" name="user" value="<?=$user['username']?>"><br>

	<button  style="margin-bottom: 5px;" type="button" id="btnpass" class="btn btn-warning">Modifica password</button><br>
	<div id="pass" class="column tohide">
        Nuova password:<br>
        <input  style="margin-bottom: 5px;" id="psw" class="psw" type="password" name="psw"><br>
        Conferma password:<br>
        <input  style="margin-bottom: 5px;" id="psw2" class="psw" type="password"><br>
    	<span id="err" style="color:red;"></span>
  	</div>
  	<input type="submit" id="submit" class="btn btn-primary" id="submit" value="Aggiorna profilo"><br>
</form>

<script src="/user/credentials.js"></script>

<script>
$(function(){
	// Shows and makes required the password fields if the user wishes to modify it
	$("#btnpass").click(function(){
		if($("#pass").is(":visible"))
    	{
      		$("#pass").hide();
      		$(".psw").removeAttr("required");
      		$(".psw").val("");
      		$("#submit").removeAttr("disabled");
      		$("#err").text("");
      		$("#btnpass").html("Modifica password");
    	}
    	else
    	{
      		$("#pass").show();
      		$(".psw").attr("required", true);
      		$("#btnpass").html("Annulla");
    	}
  	}); 
});
</script>

<?php show_postmain(); ?>