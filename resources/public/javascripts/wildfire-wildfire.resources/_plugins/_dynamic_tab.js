jQuery(document).ready(function(){
  var tab = jQuery("#dynamic-tab-content");

  jQuery(".dbl-click").live("click", function(e){
    e.preventDefault();
    var obj = jQuery(this),
        dest = (obj.attr('href') ? obj.attr("href") : obj.attr("data-href")) +".ajax"
        ;
    jQuery.ajax({
      url:dest,
      data:data,
      type:"get",
      success:function(res){

      }
    });
  });

});