jQuery(document).ready(function(){

  jQuery("select[name='redirect']").on("change", function(e){
    e.preventDefault();
    var val = jQuery(this).val();
    window.location = val;
  });

});