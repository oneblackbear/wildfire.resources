jQuery(document).ready(function(){

  jQuery(window).bind("colourise", function(){
    jQuery(".js-colour").each(function(){
      var obj = jQuery(this),
          data = obj.data()
          ;
      for(var i in data){
        obj.css(i, data[i]);
      }
    });
  });

  jQuery(window).trigger("colourise");
  jQuery(document).ajaxComplete(function(){
    jQuery(window).trigger("colourise");
  });

});