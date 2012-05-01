jQuery(document).ready(function(){

  //only allows one running timer
  var timer = false,
      seconds_passed = 0
      ;

  //every 10 mins, update the field
  jQuery(window).bind("job.timer.start", function(e, job){
    var field = job.siblings("input[placeholder='hours used']"),
        start_seconds = parseFloat(field.val()) * (3600) //so 1.5 hrs = 5400 seconds
        ;
    seconds_passed = 0;
    job.addClass("on");
    clearInterval(timer);
    job.data("field", field);
    job.data("time", start_seconds);
    job.data("timer", timer);
    job.data("title", job.siblings("label").text());

    timer = setInterval(function(){
      var t = parseFloat(job.data("time")) + 5,
          field = job.data("field"),
          hours = (t/3600).toFixed(3)
          ;
      job.data("time", t);
      seconds_passed += 5;
      //if a multiple of an 300 (5mins) has passed, update the field
      if(seconds_passed%300 == 0) field.val();
      //if its been an hour, trigger an alert
      if(seconds_passed%3600 == 0) jQuery(window).trigger("alert", [job.data("title"), "is still running.. "+hours+"hrs"]);
    }, 5000);

  });

  jQuery(window).bind("job.timer.stop", function(e, job){
    var seconds = job.data("time"),
        field = job.data("field"),
        hours = (seconds/3600).toFixed(3) //convert seconds to hours
        ;
    clearInterval(timer);
    field.val(hours);
    job.addClass("off").removeClass("on");
  });

  jQuery(".button.timer").live("click", function(e){
    e.preventDefault();
    var job = jQuery(this);
    if(job.hasClass("on")) jQuery(window).trigger("job.timer.stop",[job]);
    else jQuery(window).trigger("job.timer.start",[job]);
  });
});