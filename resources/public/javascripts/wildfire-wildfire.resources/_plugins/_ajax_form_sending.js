jQuery(document).ready(function(){


  jQuery(".async").live("submit", function(e){
    e.preventDefault();
    var form = jQuery(this),
        parent = form.parent(),
        cloned = form.clone(),
        dest = form.attr("data-action"),
        method = form.attr("method"),
        data = form.serialize()
        ;
    jQuery.ajax({
      url:dest,
      data:data,
      type:method.toUpperCase(),
      success:function(res){
        if(res.indexOf('user_errors') >= 0){
          form.replaceWith(res);
        }else if(form.hasClass("duplicate")){
          cloned.find("input[type=text],input.toggle").val("");
          cloned.find(".toggle").removeClass("on").addClass("off");
          cloned.find("select").prop('selectedIndex', -1);
          cloned.find("input[type=checkbox],input[type=radio]").removeAttr('checked');
          cloned.find("ul.user_errors").remove();
          cloned.find(".error_field").removeClass("error_field");

          form.remove();
          parent.append(res).prepend(cloned);
        }else{
          form.replaceWith(res);
        }
      }
    });

  });


});