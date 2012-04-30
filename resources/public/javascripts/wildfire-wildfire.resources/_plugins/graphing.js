jQuery(document).ready(function(){

  jQuery(window).bind("graph.draw", function(){
    jQuery(".graph").each(function(){
      var table = jQuery(this),
          config = table.data(),
          chart_div = table.siblings(".chart"),
          data = [['Date']],
          chart=false,
          chart_type = config.type,
          tmp = {}
          ;

      table.find("tbody tr:not(.totals)").each(function(){
        var row = jQuery(this),
            job = row.find("th").text(),
            cells = row.find("td")
            ;
        data[0].push(job);
        cells.each(function(){
          var cell = jQuery(this),
              pos = cells.index(cell),
              time = parseFloat(cell.text()),
              date = cell.attr("data-i")
              ;
          if(typeof tmp[date] == "undefined") tmp[date] = [];
          tmp[date].push(time);
        });

      });
      for(var d in tmp){
        var _day = [d];
        for(var x in tmp[d]) _day.push(tmp[d][x]);
        data.push(_day);
      }
      if(data && data.length > 1){
        chart = new google.visualization[chart_type](document.getElementById(chart_div.attr("id")));
        chart.draw(google.visualization.arrayToDataTable(data), {isStacked:true});
      }
      table.hide();
    });
  });

  jQuery(window).trigger("graph.draw");
  jQuery(document).ajaxComplete(function(){
    jQuery(window).trigger("graph.draw");
  });

});