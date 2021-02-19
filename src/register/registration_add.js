$(function(){
    // Toggles the visibility of the slots
    $(".btnday").click(function(){
        var conn = "#day" + $(this).attr("id").substring(3);
        if($(conn).is(":hidden"))
            $(conn).show();
        else
            $(conn).hide();
    });

    // Defines the behaviour when a slot is selected:
    // Only one in a day => green slot
    // More slots the same day => yellow slots
    $(".chk").click(function(){
        var checkedgroup = ".day" + $(this).attr("day") + ":checked";
        if($(this).is(":checked"))
        {
            $(this).closest("label").removeClass("btn-secondary");
            if($(checkedgroup).length > 1 || $(this).hasClass("multiple"))
                $(checkedgroup).closest("label").addClass("btn-warning");
            else
                $(this).closest("label").addClass("btn-success");
        }
        else
        {
            $(this).closest("label").removeClass("btn-success btn-warning");
            $(this).closest("label").addClass("btn-secondary");

            if($(checkedgroup).length == 1 && !$(this).hasClass("multiple")) 
            {
                $(checkedgroup).closest("label").removeClass("btn-warning");
                $(checkedgroup).closest("label").addClass("btn-success");
            }
        }

        colorDay();
        disableSubmit();
    });
        
    // Disables submit if there isn't at least one checkbox active for athletes and slots
    let disableSubmit = function(){
        $("input:submit").prop("disabled", !$(".cslot").is(":checked"));
    }

    // Colors the day label green if at least one of its slots is selected
    let colorDay = function() {
        $(".btnday").each(function(){
            if($(".day" + $(this).attr("id").substring(3)).is(":checked"))
            {
                $(this).removeClass("btn-secondary");
                $(this).addClass("btn-success");
            }
            else if(!$(this).hasClass("multiple"))
            {
                $(this).removeClass("btn-success");
                $(this).addClass("btn-secondary");
            }
        });
    }

    // Updates the places left and disables some options if a contemporary registration has been submitted
    var source = new EventSource("/register/registration_sse.php");
    if(typeof(EventSource) !== "undefined") {
        source.onmessage = function(event) {
            var data = JSON.parse(event.data);
            if(data != null)
                data.forEach(d => updateSlots(d));
        };
    }

    // Prepares the number of athletes to check with the places left
    var n;
    if($("#n").length)
        n = $("#n").html();
    else
        n = 0;

    let updateSlots = function(data) {
        // If the dates do not coincide it means the registration period has expired
        if($("#chk" + data.slotId).attr("date") === data.date)
            $("#left" + data.slotId).html(data.placesLeft);
        else
            $("#left" + data.slotId).html(0);

        if(data.placesLeft == 0 || parseInt(data.placesLeft) < parseInt(n) || $("#chk" + data.slotId).attr("date") !== data.date)
        {
            $("#slot" + data.slotId).removeClass("btn-success btn-warning");
            $("#slot" + data.slotId).addClass("btn-danger disabled");
            $("#chk" + data.slotId).prop("disabled", true);
            $("#chk" + data.slotId).prop("checked", false);
            colorDay();
            disableSubmit();
        }
        else
        {
            $("#slot" + data.slotId).removeClass("btn-danger disabled");
            $("#slot" + data.slotId).addClass("btn-secondary");
            $("#chk" + data.slotId).prop("disabled", false);
        }
    }
});