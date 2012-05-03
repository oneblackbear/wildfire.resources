<?
class Work extends WaxModel{

  public static $status_options = array('scheduled'=>'scheduled', 'completed'=>'completed');
  public static $work_types = array(''=>'-- Select --','admin'=>'Admin', 'meeting'=>'Meeting', 'testing'=>'Testing', 'amendmant'=>'Amends to existing', 'project'=>'Project');
  public static $cached = array();
  public function setup(){
    $this->define("title", "CharField", array('scaffold'=>true));
    $this->define("staff", "ForeignKey", array('target_model'=>"Staff", 'group'=>'relationships', 'scaffold'=>true));

    $this->define("depends_on", "ForeignKey", array('target_model'=>"Work", 'group'=>'relationships'));
    $this->define("date_start", "DateTimeField", array('label'=>'Start', 'scaffold'=>true, 'required'=>true));
    $this->define("date_end", "DateTimeField", array('label'=>'End', 'scaffold'=>true, 'required'=>true));
    $this->define("hours", "FloatField", array('maxlength'=>'12,2', 'scaffold'=>true, 'required'=>true));
    $this->define("hours_used", "FloatField", array('maxlength'=>'12,2', 'label'=>'Actual hours used so far'));
    $this->define("status", "CharField", array('widget'=>'SelectInput', 'choices'=>self::$status_options));
    $this->define("type", "CharField", array('widget'=>'SelectInput', 'choices'=>self::$work_types));

    $this->define("job", "ForeignKey", array('target_model'=>"Job", 'group'=>'relationships', 'scaffold'=>true));
    $this->define("department", "ForeignKey", array('target_model'=>"Department", 'group'=>'relationships'));
    $this->define("client", "ForeignKey", array('target_model'=>"Organisation", 'group'=>'relationships', 'scaffold'=>true,'required'=>true));

    $this->define("date_modified", "DateTimeField", array('export'=>true, 'scaffold'=>true, "editable"=>false));
    $this->define("date_created", "DateTimeField", array('export'=>true, "editable"=>false));
    $this->define("created_by", "IntegerField", array('widget'=>'HiddenInput'));
    $this->define("group_token", "CharField", array('widget'=>'HiddenInput', 'info_preview'=>1));
    $this->define("send_notification", "BooleanField");
    $this->define("notified", "IntegerField", array('editable'=>false));
  }

  public function before_insert(){
    $this->date_created = date("Y-m-d H:i:s");
    parent::before_insert();
  }
  public function is_editable(){
    $allowed = false;
    if(Session::get("LOGGED_IN_ROLE") == "owner" || Session::get("LOGGED_IN_ROLE") == "admin") return true;
    return $allowed;
  }

  public function before_save(){

    if(!$this->title) $this->title = "WORK";
    if(!$this->status) $this->status = array_shift(array_keys(self::$status_options));
    $this->date_modified = date("Y-m-d H:i:s");
    //check for idiots
    if($this->date_end < $this->date_start) $this->add_error("date_end", "End date must be after the start date.");

    if(($j = $this->row['job_id']) && ($job = new Job($j)) ){
      $this->title = $job->title;
      $this->organisation_id = $job->organisation_id;
      //if this has been joined to a job, check to make sure the time is before the end date of the job
      if(($end = date("Ymd", strtotime($job->date_go_live)))){
        $work_start = date("Ymd", strtotime($this->date_start));
        $work_end = date("Ymd", strtotime($this->date_end));
        if($end < $work_start) $this->add_error("date_start", "Work must start before the deadline (".date("jS M", strtotime($job->date_go_live)).")");
        if($end < $work_end) $this->add_error("date_end", "Work must end before the deadline (".date("jS M", strtotime($job->date_go_live)).")");
      }
    }
    parent::before_save();
  }

  public function notifications(){
    /**
     * send email alert to staff assigned to this job,
     * the clients account handler & person who raised the job
     */
    $emails = array();
    //make sure all the joins are set...
    if($this->notified == 0 && $this->send_notification){
      $notify = new ResourceNotify;
      //person assigned on the job
      if($staff) $emails[$staff->primval] = $staff;
      //the account handler for the client
      if($client && ($handler = $client->account_handler)) $emails[$handler->primval] = $handler;
      //the person who created the job
      if($creator = new Staff($job->created_by)) $emails[$creator->primval] = $creator;
      $this->update_attributes(array('notified'=>1));
      //send them out
      foreach($emails as $person) $notify->send_work_scheduled($this, $job, $person);
    }else if($this->notified == 1 && $this->status == "completed" && $this->send_notification){
      $notify = new ResourceNotify;
      $emails = array();
      //person assigned on the job
      if($staff = $this->staff) $emails[$staff->primval] = $staff;
      //the account handler for the client
      if(($client = $job->client) && ($handler = $client->account_handler)) $emails[$handler->primval] = $handler;
      //the person who created the job
      if($creator = new Staff($job->created_by)) $emails[$creator] = $creator;
      //send them out
      foreach($emails as $person) $notify->send_work_scheduled($this, $job, $person);
      $this->update_attributes(array('notified'=>2));
    }
  }

  public function who(){
    if($who = Work::$cached['who'][$this->primval]) return $who;
    else if(($staff = $this->staff) && $staff->count() && ($first = $staff->first())){
      Work::$cached[$this->primval]['who'] = $first->title;
      return $first->title;
    }
    else return "?";
  }
  public function colour($join="job", $weight=false, $func="lighten"){
    if($this->columns[$join][0] == "ForeignKey" && ($item = $this->$join) && ($item)) return $item->colour(false, $weight, $func);
    else if($items = $this->$join && ($item = $items->first())) return $item->colour(false, $weight, $func);
    else return "#ff0000";
  }

  public function hours_spent(){
    return $this->hours;
  }

  public function public_comments(){
    if($job = $this->job) return $job->comments;
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
    $start_short = date("j M", $start);

    if(date("Y") != date("Y", $start)){
      $start_txt .= " " .date("Y", $start);
      $start_short .= " " .date("Y", $start);
    }
    //if not on the same day
    if($smsd != $emed){
      $end_text = " - " .date("jS F", $end);
      $end_short = " - ". date("j M", $end);
    }
    if(date("Y", $start) != date("Y", $end)){
      $end_text .= " " .date("Y", $end);
      $end_short .= " " .date("Y", $end);
    }
    return array('string'=>$start_txt . $end_text, 'sysm'=>$sysm, 'start'=>$start, 'end'=>$end, 'short_string'=>$start_short. $end_short);
  }

  public function date_string(){
    $times = $this->start_end_times();
    return $times['short_string'];
  }
  /**
   * return when the due date for the job is, this is an educated guess
   * - looks for the first listed date that is after the start date of this job
   * and returns that
   */
  public function due_date($labels=false, $cols=false){
    if($job = $this->job) return $job->next_milestone(date("Ymd", strtotime($this->date_start)), $labels, $cols);
    return false;
  }
  /**
   * nice helper function to say how tight a class is
   */
  public function tightness(){
    if($tight = Work::$cached['tightness'][$this->primval]) return $tight;
    else if($compare = $this->due_date(false, array('date_go_live')) ){
      $start_date = date("Ymd", strtotime($this->date_end));
      $diff = date_diff(date_create($start_date), date_create($compare['day']));
      $val = $diff->format("%R%a");
      if($val <= 1) $tight = "gnats-ass";
      else if($val <= 3) $tight = "easy-peasy";
      else if($val <= 5) $tight = "room-to-spare";
      else $tight = "eon";
      Work::$cached['tightness'][$this->primval] = $tight;
      return $tight;
    }
    return "unkown";
  }

  public function between($start, $end){
    return $this->filter("((`date_start` BETWEEN '".$start."' AND '".$end."') OR (`date_end` BETWEEN '".$start."' AND '".$end."'))");
  }
}
?>