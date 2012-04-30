function highlight_range(start, end){
  var start_cell = start.parents("td"),
      end_cell = end.parents("td"),
      start_row = start.parents("tr"),
      end_row = end.parents("tr"),
      table = end.parents("table"),
      rows = table.find("tr"),
      start_row_number = rows.index(start_row),
      end_row_number = rows.index(end_row),
      start_cell_number = start_row.find("td").index(start_cell),
      end_cell_number = end_row.find("td").index(end_cell)
      ;
  //loop over the rows
  for(var _row = start_row_number; _row <= end_row_number; _row++){
    if(_row == end_row_number) var _end = end_cell_number;
    else _end = table.find("tr:eq("+_row+") td").length;
    //loop over the cells in the row
    for(var _cell = start_cell_number; _cell <= _end; _cell++){
      table.find("tr:eq("+_row+") td:eq("+_cell+")").addClass("highlight");
    }
    //reset the cell number
    start_cell_number = 0;
  }

}


jQuery(document).ready(function(){
  var tab = jQuery("#dynamic-tab-content");

  jQuery(window).bind("dynamic-tab", function(e, obj, tab){
    var dest = (obj.attr('href') ? obj.attr("href") : obj.attr("data-href")) ;
    if(dest.indexOf("?") > 0) dest = dest.replace("?", ".ajax?");
    else dest += ".ajax";
    dest = dest.replace("#", "");

    jQuery.ajax({
      url:dest,
      type:"get",
      success:function(res){
        tab.find(".inside").html(res);
        if(tab.hasClass("show")) jQuery(".help_tab .help.button").trigger("click").trigger("click");
        else jQuery(".help_tab .help.button").trigger("click");
      }
    });
  });

  jQuery(".dbl-click").live("click", function(e){
    e.preventDefault();
    jQuery(window).trigger("dynamic-tab", [jQuery(this), tab]);
  });


  var range_start = false,
      range_end = false;
  jQuery(".range-click").live("click", function(e){
    e.preventDefault();
    if(range_start && !range_end){
      range_end = jQuery(this);
      range_end.parents("td").addClass("highlight");
      var range_filters = range_start.attr("data-col_start")+"="+range_start.attr("data-col_value")+"&"+range_end.attr("data-col_end")+"="+range_end.attr("data-col_value");

      highlight_range(range_start, range_end);
      range_end.attr("href", range_end.attr("href")+"?"+range_filters);
      jQuery(window).trigger("dynamic-tab", [range_end, tab]);
    }
    if(!range_start || (range_start && range_end)){
      if(tab.hasClass("show")) jQuery(".help_tab .help.button").trigger("click");
      range_start = jQuery(this);
      range_start.parents("td").addClass('highlight');
    }


  });

  jQuery(window).bind("closed-help-tab", function(e){
    jQuery(".highlight").removeClass("highlight");
    range_start = false;
    range_end = false;
  });

});