jQuery(document).ready(function(){
  function _placeholders(){
    var ua_placeholder = (jQuery.browser.msie)?false:true;
    if(!ua_placeholder){
      jQuery('input[type="text"]').each(function(){
        if(jQuery(this).attr('placeholder') && (jQuery(this).val() == jQuery(this).attr('placeholder') || jQuery(this).val() == '' || jQuery(this).val() == 0 || jQuery(this).val() == 0.0 )) jQuery(this).val(jQuery(this).attr('placeholder'));
      }).live("focus", function(){
        if(jQuery(this).attr('placeholder')  && jQuery(this).val() == jQuery(this).attr('placeholder')) jQuery(this).val('');
      }).live("blur", function(){
        if(jQuery(this).attr('placeholder')  && jQuery(this).val().length == 0) jQuery(this).val(jQuery(this).attr('placeholder'));
      });
      jQuery("form").live("submit", function(){
        jQuery(this).find('input[type="text"]').each(function(){ if(jQuery(this).attr('placeholder') && jQuery(this).val() ==  jQuery(this).attr('placeholder') ) jQuery(this).val(''); });
      });
    }
  }

  _placeholders();
});