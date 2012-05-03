jQuery(document).ready(function(){

  jQuery(window).bind("datepicker", function(){
    jQuery('.date_field').datepicker({changeMonth: true, changeYear:true});
  });

  jQuery(window).trigger("datepicker");
  jQuery(document).ajaxComplete(function(){
    jQuery(window).trigger("datepicker");
  });

});