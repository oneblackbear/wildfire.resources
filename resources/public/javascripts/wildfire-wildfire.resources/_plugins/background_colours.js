jQuery(document).ready(function(){

  jQuery(window).bind("colourise", function(){
    jQuery(".js-colour").each(function(){
      var obj = jQuery(this),
          attr = obj.attr("data-type"),
          val = "#"+obj.attr("data-colour")
          ;
      obj.css(attr,val);
    });
  });

  jQuery(window).trigger("colourise");
  jQuery(document).ajaxComplete(function(){
    jQuery(window).trigger("colourise");
  });

});