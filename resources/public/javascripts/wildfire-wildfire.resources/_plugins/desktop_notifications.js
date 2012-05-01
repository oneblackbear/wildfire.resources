jQuery(document).ready(function(){
  var favicon = "//"+window.location.hostname+"/favicon.ico";

  if(window.webkitNotifications && window.webkitNotifications.checkPermission() == 0) jQuery(".allow_notifications").hide();
  else jQuery(".allow_notifications").on("click", function(e){ e.preventDefault(); window.webkitNotifications.requestPermission();});

  jQuery(window).bind("alert", function(e, title, copy){
    if(window.webkitNotifications && window.webkitNotifications.checkPermission() == 0) window.webkitNotifications.createNotification(favicon, title , copy).show();
  });

});