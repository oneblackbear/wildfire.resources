jQuery(document).ready(function(){

  jQuery(window).bind("stylize", function(){
    jQuery(".stylize").each(function(){
      var obj = jQuery(this),
          data = obj.data()
          ;
      for(var i in data){
        if(i.substring(0,3) == "css"){
          var prop = i.replace("css", "").toLowerCase();
          obj.css(prop, data[i]);
        }
      }
    });
  });

  jQuery(window).trigger("stylize");
  jQuery(document).ajaxComplete(function(){
    jQuery(window).trigger("stylize");
  });

});