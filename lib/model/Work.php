<?
class Work extends WaxModel{

  public static $status_options = array('scheduled'=>'scheduled', 'completed'=>'completed');
  public static $work_types = array(''=>'-- Select --','admin'=>'Admin', 'meeting'=>'Meeting', 'testing'=>'Testing', 'amendmant'=>'Amends to existing', 'project'=>'Project');
  public static $cached = array();
  public function setup(){
    $this->define("title", "CharField", array('scaffold'=>true));
    $this->define("staff", "ForeignKey", array('target_model'=>"Staff", 'group'=>'relationships', 'scaffold'=>true));
    $this->define("content", "TextField", array('widget'=>"TinymceTextareaInput", 'label'=>'Description'));

    $this->define("depends_on", "ForeignKey", array('target_model'=>"Work", 'group'=>'relationships', 'eager_load'=>true));
    $this->define("date_start", "DateTimeField", array('label'=>'Start', 'scaffold'=>true, 'required'=>true));
    $this->define("date_end", "DateTimeField", array('label'=>'End', 'scaffold'=>true, 'required'=>true));
    $this->define("hours", "FloatField", array('maxlength'=>'12,2', 'scaffold'=>true));
    $this->define("hours_used", "FloatField", array('maxlength'=>'12,2', 'label'=>'Actual hours used so far'));
    $this->define("status", "CharField", array('widget'=>'SelectInput', 'choices'=>self::$status_options));
    $this->define("type", "CharField", array('widget'=>'SelectInput', 'choices'=>self::$work_types));

    $this->define("job", "ForeignKey", array('target_model'=>"Job", 'group'=>'relationships', 'scaffold'=>true, 'eager_load'=>true));
    $this->define("department", "ForeignKey", array('target_model'=>"Department", 'group'=>'relationships', 'eager_load'=>true));
    $this->define("client", "ForeignKey", array('target_model'=>"Organisation", 'group'=>'relationships', 'scaffold'=>true, 'eager_load'=>true));
    $this->define("fee", "ForeignKey", array('target_model'=>"Fee", 'group'=>'relationships', 'eager_load'=>true));

    $this->define("date_modified", "DateTimeField", array('export'=>true, 'scaffold'=>true, "editable"=>false));
    $this->define("date_created", "DateTimeField", array('export'=>true, "editable"=>false));
    $this->define("date_completed", "DateTimeField", array('export'=>true, "editable"=>false));
    $this->define("created_by", "IntegerField", array('widget'=>'HiddenInput'));
    $this->define("group_token", "CharField", array('widget'=>'HiddenInput', 'info_preview'=>1));
    $this->define("send_notification", "BooleanField", array('default'=>1));
    $this->define("notified", "IntegerField", array('editable'=>false));
    $this->define("notified_of_invite", "IntegerField", array('editable'=>false));
    $this->define("adhoc", "BooleanField", array('editable'=>false));
    //an invite mechanism, so you can add meetings etc to multiple people
    //$this->define("invite", "ManyToManyField", array('target_model'=>"Staff", 'group'=>'relationships', 'scaffold'=>true));

  }
  public function scope_live(){
    return $this->order("date_start ASC");
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

  public function total(){
    $diff = date_diff(date_create($this->date_end), date_create($this->date_start));
    $val = $diff->format("%d");
    return ($val*$this->hours)+$this->hours;
  }

  public function before_save(){
    $this->send_notification = 1;
    if(!$this->status) $this->status = array_shift(array_keys(self::$status_options));
    if(!$this->hours && $this->hours_used) $this->hours = $this->hours_used;
    $this->date_modified = date("Y-m-d H:i:s");
    //check for idiots
    if($this->date_end < $this->date_start) $this->add_error("date_end", "End date must be after the start date.");

    if(($j = $this->row['job_id']) && ($job = new Job($j)) ){
      if(!$this->title) $this->title = $job->title;
      if(!$this->organisation_id) $this->organisation_id = $job->organisation_id;
      if(!$this->fee_id) $this->fee_id = $job->fee_id;

    }
    parent::before_save();
  }

  public function notifications(){
    /**
     * send email alert to staff assigned to this job,
     * the clients account handler & person who raised the job
     */
    $emails = array();
    $notify = new ResourceNotify;
    //make sure all the joins are set...
    if($this->notified == 0 && $this->send_notification && ($job = $this->job) && ($client = $this->client) && ($staff = $this->staff) && ($dep = $this->department)){
      $emails = $this->contact_emails();
      $this->update_attributes(array('notified'=>1));
      //send them out
      // foreach($emails as $person) $notify->send_work_scheduled($this, $job, $person, $emails);
    //a completed job
    }else if($this->notified == 1 && $this->status == "completed" && $this->send_notification){
      $emails = $this->contact_emails();
      //send them out
      foreach($emails as $person) $notify->send_work_completed($this, $job, $person, $emails);
      $this->update_attributes(array('notified'=>2, 'date_completed'=>date("Y-m-d H:i:s")));
    //an updated job
    }else if($this->notified == 1 && $this->send_notification){
      $emails = $this->contact_emails();
      //send them out
      // foreach($emails as $person) $notify->send_work_updated($this, $job, $person, $emails);
    }
  }

  public function contact_emails(){
    $emails = array();
    //person assigned on the job
    if($staff = $this->staff) $emails[$staff->primval] = $staff;
    //the account handler for the client
    if(($client = $job->client) && ($handler = $client->account_handler)) $emails[$handler->primval] = $handler;
    //the person who created the job
    if($creator = new Staff($job->created_by)) $emails[$creator] = $creator;
    //the head of the department
    if($dept = new Department($this->department_id)) if(($admins = $dept->admins()) && $admins && $admins->count()) foreach($admins as $staff) $emails[$staff->primval] = $staff;
    return $emails;
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
    else return false;
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
    return $this->filter("((`date_start` <= '".$end."') AND (`date_end` >= '".$start."'))");
  }

  public function by_staff($start, $end){
    $times = array();
    $cal = new Calendar;
    //filter dates
    foreach($this->between($start, $end)->all() as $row){
      foreach($cal->date_range_array($row->date_start, $row->date_end) as $date=>$bool) $times[$row->staff_id][$date] += $row->hours;
    }
    return $times;
  }
}
?>