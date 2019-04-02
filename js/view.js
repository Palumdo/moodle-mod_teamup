$(function() {
    var maxwidth = 80;
    $(".answers").find("label").each(function() {
        if ($(this).width() > maxwidth) {
            maxwidth = $(this).width();
        }
    });

    $(".answers").each(function() {
        var table = $("<div />");
        $(this).find("label").each(function() {
            var div = $("<div />");
            div.addClass("response");
            div.width(maxwidth);
            div.append($(this));
            table.append(div);
        });
        $(this).empty();
        $(this).append(table);
        $(this).css("visbility", "visible");
    });

});

function validateForm(form) {

    var valid = true;
   // $(form).find(".atleastone, input[type='radio']").closest(".answers").each(function() {
    $(form).find(".atleastone").closest(".answers").each(function() {
      if ($(this).find(".atleastone:checked").length > 0) {
        $(this).closest("div.question").removeClass("ui-state-error");
        return true; // Equiv. to continue in each() loop.
      }

      if ($(this).find("input[type='radio']:checked").length > 0) {
        $(this).closest("div.question").removeClass("ui-state-error");
        return true; // Equiv. to continue in each() loop.
      }

      valid = false;
      $(this).closest("div.question").addClass("ui-state-error");
    });

    
    $(form).find("input[type='radio']").closest(".answers").each(function() {
      if ($(this).find("input[type='radio']:checked").length > 0) {
        $(this).closest("div.question").removeClass("ui-state-error");
        return true; // Equiv. to continue in each() loop.
      }

      valid = false;
      $(this).closest("div.question").addClass("ui-state-error");
    });    

    $(form).find(".two").closest(".answers").each(function() {      
        if ($(this).find(".two:checked").length == 2) {
            $(this).closest("div.question").removeClass("ui-state-error");
            return true; // Equiv. to continue in each() loop.
        }

      valid = false;
      $(this).closest("div.question").addClass("ui-state-error");
    });

    $(form).find(".three").closest(".answers").each(function() {
        if ($(this).find(".three:checked").length == 3) {
            $(this).closest("div.question").removeClass("ui-state-error");
            return true; // Equiv. to continue in each() loop.
        }

      valid = false;
      $(this).closest("div.question").addClass("ui-state-error");
    });

    $(form).find(".four").closest(".answers").each(function() {
        if ($(this).find(".four:checked").length == 4) {
            $(this).closest("div.question").removeClass("ui-state-error");
            return true; // Equiv. to continue in each() loop.
        }        
      valid = false;
      $(this).closest("div.question").addClass("ui-state-error");
    });

    $(form).find(".five").closest(".answers").each(function() {
        if ($(this).find(".five:checked").length == 5) {
            $(this).closest("div.question").removeClass("ui-state-error");
            return true; // Equiv. to continue in each() loop.
        }        
      valid = false;
      $(this).closest("div.question").addClass("ui-state-error");
    });
    
    
    
    return valid;
}