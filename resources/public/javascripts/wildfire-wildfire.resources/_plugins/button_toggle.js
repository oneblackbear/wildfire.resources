jQuery(document).ready(function(){

  jQuery(".on.toggle").live("click", function(e){
    e.preventDefault();
    var obj = jQuery(this),
        input_selector = "#"+obj.attr("data-for")
        ;
    obj.removeClass("on").addClass("off");
    jQuery(input_selector).val(0);
  });
  jQuery(".off.toggle").live("click", function(e){
    e.preventDefault();
    var obj = jQuery(this),
        input_selector = "#"+obj.attr("data-for"),
        val = obj.attr("data-val")
        ;
    obj.addClass("on").removeClass("off");
    jQuery(input_selector).val(val);
  });
});