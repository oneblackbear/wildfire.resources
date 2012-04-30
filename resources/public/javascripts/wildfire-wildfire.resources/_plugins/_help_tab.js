jQuery(document).ready(function(){

  jQuery(".help_tab .help.button").on("click", function(){
    var obj = jQuery(this),
        parent = obj.parent(),
        close = parent.hasClass("show")
        ;
    if(close){
      parent.removeClass("show");
      jQuery(window).trigger("closed-help-tab");
    }else parent.addClass("show");

  });

});