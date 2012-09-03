jQuery(document).ready(function(){

  var timed_search = false;


  jQuery(window).bind("autocomplete", function(e, field, val, form, replace){
    var dest = form.attr("action")+".ajax";
    jQuery.ajax({
      url:dest,
      data:form.serialize(),
      type:form.attr("method").toUpperCase(),
      success:function(res){
        replace.html(res);
      }
    });

  });

  jQuery("input.autocomplete").keyup(function(e){
    var field = jQuery(this),
        val = field.val(),
        form = field.parents("form"),
        replace = form.find(field.attr("data-replace"))
        ;
    clearTimeout(timed_search);
    if(e.which != 27){
      timed_search = setTimeout(function(){
        jQuery(window).trigger("autocomplete", [field, val, form, replace]);
      }, 800);
    }
  });

  jQuery(window).on("keyup", function(e){
    //escape
    if(e.which == 27 && jQuery(".inline_search_results .search_results").length){
      jQuery(".inline_search_results .search_results").remove();
      clearTimeout(timed_search);
    }
  });

});