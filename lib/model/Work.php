<?
class Work extends WaxModel{

  public static $status_options = array('scheduled'=>'scheduled', 'completed'=>'completed');
  public static $cache = array();
  public function setup(){
    $this->define("title", "CharField", array('scaffold'=>true));
    $this->define("staff", "ManyToManyField", array('target_model'=>"Staff", 'group'=>'relationships', 'scaffold'=>true));

    $this->define("depends_on", "ForeignKey", array('target_model'=>"Work", 'group'=>'relationships'));
    $this->define("date_start", "DateTimeField", array('label'=>'Start', 'scaffold'=>true));
    $this->define("date_end", "DateTimeField", array('label'=>'End', 'scaffold'=>true));
    $this->define("hours", "FloatField", array('maxlength'=>'12,2', 'scaffold'=>true));
    $this->define("hours_used", "FloatField", array('maxlength'=>'12,2', 'label'=>'Actual hours used so far'));
    $this->define("status", "CharField", array('widget'=>'SelectInput', 'choices'=>self::$status_options));


    $this->define("jobs", "ManyToManyField", array('target_model'=>"Job", 'group'=>'relationships', 'scaffold'=>true));
    $this->define("departments", "ManyToManyField", array('target_model'=>"Department", 'group'=>'relationships'));
    $this->define("clients", "ManyToManyField", array('target_model'=>"Organisation", 'group'=>'relationships', 'scaffold'=>true));

    $this->define("date_modified", "DateTimeField", array('export'=>true, 'scaffold'=>true, "editable"=>false));
    $this->define("date_created", "DateTimeField", array('export'=>true, "editable"=>false));
    $this->define("created_by", "IntegerField", array('widget'=>'HiddenInput'));
    $this->define("group_token", "CharField", array('widget'=>'HiddenInput', 'info_preview'=>1));
  }

  public function before_insert(){
    $this->date_created = date("Y-m-d H:i:s");
  }
  public function before_save(){
    parent::before_save();
    if(!$this->title) $this->title = "WORK";
    $this->date_modified = date("Y-m-d H:i:s");

    //if this has been joined to a job, check to make sure the time is before the end date of the job
    if(($job = $this->job()) && ($end = date("Ymd", strtotime($job->date_go_live)))){
      if($end < date("Ymd", strtotime($this->date_start))) $this->add_error("date_start", "Work cannot start after the deadline for '$job->title' ($job->date_go_live)");
      if($end < date("Ymd", strtotime($this->date_end))) $this->add_error("date_end", "Work end date must be before the deadline of '$job->title' ($job->date_go_live)");
    }
  }

  public function who(){
    if($who = Work::$cache['who'][$this->primval]) return $who;
    else if(($staff = $this->staff) && $staff->count() && ($first = $staff->first())){
      Work::$cache[$this->primval]['who'] = $first->title;
      return $first->title;
    }
    else return "?";
  }
  public function colour($join="jobs", $weight=false, $func="lighten"){
    if($colour = Work::$cache['colour'][$join][$weight][$func][$this->primval]) return $colour;
    else if(($items = $this->$join) && ($item = $items->first())){
      Work::$cache['colour'][$join][$weight][$func][$this->primval] = $item->colour(false, $weight, $func);
      return Work::$cache['colour'][$join][$weight][$func][$this->primval];
    }else return "#ececec";
  }

  public function job(){
    if($j = Work::$cache['job'][$this->primval]) return new Job($j);
    else if(($jobs = $this->jobs) && ($job = $jobs->first())){
      Work::$cache['job'][$this->primval] = $job->primval;
      return $job;
    }
    return false;
  }

  public function public_comments(){
    if($job = $this->job()) return $job->comments;
    return false;
  }
  public function private_comments(){
    return $this->comments;
  }

  public function start_end_times($sd = false, $ed = false){
    if($sd) $start = $sd;
    else $start = strtotime($this->date_start);
    if($ed) $end = $ed;
    else $end = strtotime($this->date_end);

    $sysm = date("F Y", $start);
    $smsd = date("md", $start);
    $emed = date("md", $end);
    $end_short = $start_short = "";
    $start_txt = date("jS F", $start);
    $start_short = date("D j M", $start);

    if(date("Y") != date("Y", $start)){
      $start_txt .= " " .date("Y", $start);
      $start_short .= " " .date("Y", $start);
    }
    //if not on the same day
    if($smsd != $emed){
      $end_text = " - " .date("jS F", $end);
      $end_short = " - ". date("D j M", $end);
    }
    if(date("Y", $start) != date("Y", $end)){
      $end_text .= " " .date("Y", $end);
      $end_short .= " " .date("Y", $end);
    }
    return array('string'=>$start_txt . $end_text, 'sysm'=>$sysm, 'start'=>$start, 'end'=>$end, 'short_string'=>$start_short. $end_short);
  }

  public function date_string(){
    $times = $this->start_end_times();
    return $times['string'];
  }
  /**
   * return when the due date for the job is, this is an educated guess
   * - looks for the first listed date that is after the start date of this job
   * and returns that
   */
  public function due_date($labels=false){
    if($job = $this->job()) return $job->next_milestone(date("Ymd", strtotime($this->date_start)), $labels);
    return false;
  }
  /**
   * nice helper function to say how tight a class is
   */
  public function tightness(){
    if($tight = Work::$cache['tightness'][$this->primval]) return $tight;
    else if($compare = $this->due_date()){
      $start_date = date("Ymd", strtotime($this->date_start));
      $diff = date_diff(date_create($start_date), date_create($compare['day']));
      $val = $diff->format("%R%a");
      if($val <= 1) $tight = "gnats-ass";
      else if($val <= 3) $tight = "eye-of-needle";
      else if($val <= 5) $tight = "breath-easy";
      else $tight = "eon";
      Work::$cache['tightness'][$this->primval] = $tight;
      return $tight;
    }
    return "unkown";
  }
}
?>