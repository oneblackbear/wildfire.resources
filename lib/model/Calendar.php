<?
class Calendar{

  public $now;
  public $current_month;
  public $current_year;
  public $current_month_name;
  public $current_date;
  public $current_day;
  public $this_month;
  public $temp_month;
  public $total_days;


  public function __construct($year=false, $month=false){
    if(!$month) $month = date("m");
    if(!$year) $year = date("Y");
    //Purpose : Constructor of the class and responsible for Variable Initialization
    $this->current_month_name = date("F", mktime(0,0,0, $month, 1, $year));
    $this->current_day = 1;
    $this->current_month = $month;
    $this->current_year = date("Y", mktime(0,0,0,1,1,$year));
    $this->this_month = $this->current_month;
    $this->temp_month = date("m");
    $this->start_date = false;
    $this->end_date = false;
  }

  function generate(){
    $table_array = array('current_month_name'=>$this->current_month_name, 'current_year'=>$this->current_year, 'current_month'=>$this->current_month);
    //first day number for this month
    $table_array['first'] = date("z", mktime(0,0,0, $this->current_month, 1, $this->current_year) );
    //last day number of this month
    $table_array['last'] = date("z", mktime(0,0,0, $this->current_month+1, 1, $this->current_year) );
    //special case for december as the silly php doesnt!
    if($this->current_month == 12) $table_array['last'] = 365;
    //number of days in this month
    $table_array['days_in_current_month'] = $table_array['last'] - $table_array['first'];
    //day of week number for the first of day of the month (sunday=0, sat=6)
    $table_array['first_day_number'] = date("w", mktime(0,0,0, $this->current_month, 1, $this->current_year) );
    //first date - overlap from previous months
    $table_array['first_date_on_calendar'] = date("j", mktime(0,0,0, $this->current_month, 1-$table_array['first_day_number'], $this->current_year) );
    //last day of the month
    $table_array['last_day_of_month'] = $table_array['days_in_current_month'];
    //number of days in the month + offset
    $table_array['last'] = $table_array['days_in_current_month'] + $table_array['first_day_number'];
    //how many days do we need to add to the end
    $table_array['mod'] = fmod($table_array['last'],7);
    //last day to show on calendar - next month
    $table_array['last_date_on_calendar'] = 1 + (6-$table_array['mod']);
    //if its an exact match blank it
    if($table_array['last_date_on_calendar'] == 7) $table_array['last_date_on_calendar'] = 0;
    //total number of days to show on the calendar
    $table_array['days_on_calendar'] = $table_array['days_in_current_month'] + $table_array['first_day_number'] + $table_array['last_date_on_calendar'];
    $table_array['start_date'] = $this->start_date = date("Ymd", mktime(0,0,0, $this->current_month, 1-$table_array['first_day_number'], $this->current_year));
    $table_array['end_date'] = $this->end_date = date("Ymd", mktime(0,0,0, $this->current_month, $table_array['days_on_calendar'] - $table_array['first_day_number'], $this->current_year));
    $table_array['next'] = date("Ym", mktime(0,0,0, $this->current_month+1, 0, $this->current_year));
    $table_array['previous'] = date("Ym", mktime(0,0,0, $this->current_month-1, 0, $this->current_year));
    return $table_array;
  }


  public function range_filter($model, $year, $month, $start_col="date_start", $end_col="date_end"){
    if(!$year) $year = $this->current_year;
    if($month && $month < 10) $month = "0".str_replace("0", "", $month);
    $sql = "(";
    //exact match for start month
    $sql .= "(`$start_col` BETWEEN '$year".($month-1)."23' AND '$year".($month)."07') AND ";
    $sql .= "(`$end_col` BETWEEN '$year".($month-1)."23' AND '$year".($month)."07')";
    $sql .= ")";
    return $model->filter($sql);
  }

}
?>