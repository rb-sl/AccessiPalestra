$(function(){
    // Checks the equality of the two passwords, possibly
    // blocking the submit 
    $(".psw").keyup(function(){
    	if($("#psw").val() == $("#psw2").val())
    	{
    		$("#submit").removeAttr("disabled");
      		$("#err").text("");
    	}
    	else
    	{
      		$("#submit").attr("disabled",true);
   		   	$("#err").html("Le password inserite non coincidono!<br>");
    	}
	});

    // Removes spaces from user input
    $("#user").on("input", function() {
        this.value = this.value.replace(/\s/g, "");
    });
});