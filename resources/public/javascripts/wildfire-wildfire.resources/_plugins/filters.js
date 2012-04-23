function filter_list(trigger_element, replace){
  var form = trigger_element.closest("form"),
      data = {},
      fieldset = trigger_element.closest("fieldset.filters_container"),
      dest = fieldset.attr('data-action'),
      r = fieldset.attr('data-replace')
      ;
  form.addClass('loading');
  fieldset.find("input[type='text'], select").each(function(){
    var field = jQuery(this), nm = field.attr('name'), pl = field.attr('placeholder'), val = field.val();
    if(val != pl) data[nm] = val;
    else data[nm]='';
  });
  jQuery.ajax({
    url:dest,
    data:data,
    type:"get",
    success:function(res){
      form.removeClass("loading");
      console.log(replace);
      jQuery(replace).html(res);
      jQuery(window).trigger("filter.trigger");
    },
    error:function(){
    }
  });
}
jQuery(document).ready(function(){

  jQuery(window).bind("filter.bind", function(e, obj, parent_form, replace){
    var filter_listener = false;
    obj.unbind("change keyup").bind("change keyup", function(){ clearTimeout(filter_listener); filter_listener = setTimeout(function(){filter_list(obj, replace);}, 500);});
  });

  jQuery('form fieldset.filters_container').find("input[type='text'],select").each(function(){
    var obj = jQuery(this), parent_form = obj.closest("form"), replace = obj.closest("[data-replace]").attr("data-replace");
    jQuery(window).trigger("filter.bind", [obj, parent_form, replace]);
  });
});