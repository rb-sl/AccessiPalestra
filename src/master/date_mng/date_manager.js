$(function(){
    // Toggles the colors of radiobuttons
    $(".rad").click(function(){
        $("[name='" + $(this).attr("name") + "']").closest("label").removeClass("btn-info");
        $("[name='" + $(this).attr("name") + "']").closest("label").addClass("btn-secondary");
        
        $(this).closest("label").removeClass("btn-secondary");
        $(this).closest("label").addClass("btn-info");    
    });

    // Shows the period div and toggles the period
    $(".multidate").click(function(){
        $("#when").show();

        if($(this).attr("id") === "rmulti")
        {
            $("#from").text("Dal");
            $("#to").show();
            $("#toremove2").removeAttr("disabled");
            $("#toremove2").attr("required", true);
            $("#slots").hide();
            $("#subremove").removeAttr("disabled");
        }
        else
        {
            $("#from").text("Data");
            $("#to").hide();
            $("#toremove2").attr("disabled", true);
            $("#toremove2").removeAttr("required");
            if($("#toremove1").val())
            {
                $("#freq").show();
                if($("#once").is(":checked"))
                {
                    $("#slots").show();
                    loadSlots();
                }
            }
        }
    });

    // Shows the frequency div
    $(".date").change(function(){
        if($("#rsingle").is(":checked") || $("#toremove1").val() && $("#toremove2").val())
        {
            $("#freq").show();
            if($("#rsingle").is(":checked") && $("#once").is(":checked"))
                loadSlots();
        }    
    });

    // Shows the final elements
    $(".perm").change(function(){
        $("#subremove").show();
        if($("#once").is(":checked") && $("#rsingle").is(":checked"))
        {
            $("#slots").show();
            loadSlots();
        }
        else
        {
            $("#slots").hide();
            $("#subremove").removeAttr("disabled");
        }
    });

    function loadSlots() {
        $("#loading").show();
        $(".crem").attr("disabled", true);

        // Ajax request to know a day's slots
        $.ajax({                                      
            url: "/master/date_mng/date_ajax.php",   
            data: "date=" + $("#toremove1").val(),
            dataType: "json",
            success: function(data){
                $("#slotlist").html("");
                if(data !== null)
                    $.each(data, function(i){
                        $("#slotlist").append("<label id='slot" + i + "' class='btn btn-secondary marginunder'>"
                        + "<input type='checkbox' id='chk" + i + "' class='chk crem' name='slot[" + i +"]'> "
                        + data[i]['start_time'].substr(0, 5) + "-" + data[i]['end_time'].substr(0,5) + "</label>");
                    });
                else
                    $("#slotlist").html("<u>Nessun allenamento nella data selezionata</u>");
            },
            error: function(xhr, status, error){
                alert(status + ": " + error);
            }
        });
       $("#loading").hide();
       $("#subremove").attr("disabled", true);
    } 

    // Defines the behaviour of slots to be removed and of the connected submit button
    $("#slotlist").on("click", ".crem", function(){
        if($(this).is(":checked"))
        {
            $(this).closest("label").removeClass("btn-secondary");
            $(this).closest("label").addClass("btn-danger");
        }
        else
        {
            $(this).closest("label").removeClass("btn-danger");
            $(this).closest("label").addClass("btn-secondary");
        }

        if($(".crem:checked").length > 0)
            $("#subremove").removeAttr("disabled");
        else
            $("#subremove").attr("disabled", true);
    });

    // Defines the behaviour of slots to be restored and of the connected submit button
    $(".cres").click(function(){
        if($(this).is(":checked"))
        {
            $(this).closest("label").removeClass("btn-secondary");
            $(this).closest("label").addClass("btn-success");
        }
        else
        {
            $(this).closest("label").removeClass("btn-success");
            $(this).closest("label").addClass("btn-secondary");
        }

        if($(".cres:checked").length > 0)
            $("#subrestore").removeAttr("disabled");
        else
            $("#subrestore").attr("disabled", true);
    });

    // Shows and hides the removed lists
    $("#showonce").click(function(){
        $("#remsingle").toggle();
    });
    $("#showperm").click(function(){
        $("#remperm").toggle();
    });
});
