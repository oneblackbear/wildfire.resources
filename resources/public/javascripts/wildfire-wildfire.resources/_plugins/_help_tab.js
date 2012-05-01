jQuery(document).ready(function(){

  jQuery(".help_tab .help.button").on("click", function(e){
    e.preventDefault();
    var obj = jQuery(this),
        parent = obj.parent(),
        close = parent.hasClass("show")
        ;
    if(close){
      parent.removeClass("show");
      jQuery(window).trigger("closed-help-tab");
    }else parent.addClass("show");

  });

  jQuery(window).on("keyup", function(e){
    //escape
    if(e.which == 27) jQuery(".help_tab .button").trigger("click");
  });

});