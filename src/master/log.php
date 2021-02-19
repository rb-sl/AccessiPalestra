<?php
// Front end page to display logs
include $_SERVER['DOCUMENT_ROOT']."/libraries/general.php";
chk_access(true);
connect();
show_premain();
?>

<h2>Log di utilizzo</h2>

<div id="par" class="row">
	<div id="list" class="column">
<?php
// Gets the log dates from the server
$loglist = array_diff(scandir(LOG_PATH, SCANDIR_SORT_DESCENDING), array("..", ".", "solver"));
$i = 0;
foreach($loglist as $log)
{
  echo "<span id='sp$i' class='splog'>".substr($log, 4, 10)."<br></span>";
  $i++;
}
?>
	</div>
	<div id="txtcont">
		<textarea id="txt"></textarea>
	</div>
</div>

<script>
$(function(){
	var active;
    // Performs an ajax request to get the log content
	$(".splog").click(function(){
       	$.ajax({                                      
        	url: "/master/log_reader.php",
        	data: "date=" + $(this).text(),
        	dataType: "json",                
        	success: function(data) {
          		$("#txt").text(data);
        	} 
      	});
      
    	$("#" + active).css("color", "black");
      	$(this).css("color", "red");
      	active = $(this).attr("id");
      	$("#del").attr("disabled",false);
   });
});
</script>

<?php show_postmain(); ?>