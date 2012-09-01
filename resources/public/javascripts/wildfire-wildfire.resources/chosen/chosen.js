jQuery(document).ready(function($){
  $(window).bind("select.chosen", function(){
    console.log("here");
    $("select").chosen();
  }).trigger("select.chosen");
});