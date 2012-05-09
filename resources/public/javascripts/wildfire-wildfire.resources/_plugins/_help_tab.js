jQuery(document).ready(function(){
  var start_pos = jQuery(".help_tab").offset().top;
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
  jQuery(window).on("resize", function(){
    jQuery(".help_tab .inside").css({"height":jQuery(window).height()-(start_pos), "overflow":"auto"});
  });

  jQuery(document).scroll(function(e){
    jQuery(document).scrollTop();
    if(jQuery(document).scrollTop() > start_pos) jQuery(".help_tab").css({"position":"fixed", "top":0});
    else jQuery(".help_tab").css({"position":"absolute", "top":""});
  });
  jQuery(window).trigger("resize");

});