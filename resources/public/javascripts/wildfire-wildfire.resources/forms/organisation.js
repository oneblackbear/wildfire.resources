jQuery(document).ready(function(){

  jQuery("#organisation_is_client").on("change", function(){
    var obj = jQuery(this),
        val = obj.val()
        ;
    if(val == 1) jQuery("#organisation_departments, #organisation_staff").hide().siblings("label").hide().parent("span").hide();
    else jQuery("#organisation_departments, #organisation_staff").show().siblings("label").show().parent("span").show();
  });

});