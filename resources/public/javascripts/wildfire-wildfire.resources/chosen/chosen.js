jQuery(document).ready(function($){
  $(window).bind("select.chosen", function(){
    $("select").chosen();
  }).trigger("select.chosen");
});