<?
class Job extends WildfireResource{

  public static $scope_cache = array();
  public function setup(){
    parent::setup();
    $this->define("hours_estimated", "FloatField", array('required'=>true, 'maxlength'=>"12,2", 'scaffold'=>true));
    $this->define("hours_actual", "FloatField", array('maxlength'=>"12,2", 'scaffold'=>true));
    $this->define("date_creative_required_for", "DateTimeField", array('label'=>'Creative required for'));
    $this->define("date_internal_testing", "DateTimeField", array('label'=>'Internal testing date'));
    $this->define("date_client_testing", "DateTimeField", array('label'=>'Client testing date'));
    $this->define("date_go_live", "DateTimeField", array('label'=>'Go live date', 'required'=>true, 'scaffold'=>true));
    $this->define("flagged", "BooleanField", array('editable'=>$this->is_editable(), 'scaffold'=>$this->is_editable()));
    $this->define("comments", "ManyToManyField", array('target_model'=>"Comment", 'group'=>'relationships','editable'=>false));
    $this->define("work", "HasManyField", array('target_model'=>"Work", 'group'=>'relationships'));
    $this->define("fee", "ForeignKey", array('target_model'=>"Fee", 'group'=>'relationships'));
    $this->define("client", "ForeignKey", array('target_model'=>"Organisation", 'group'=>'relationships', 'scaffold'=>true));
    $this->define("departments", "ManyToManyField", array('target_model'=>"Department", 'group'=>'relationships', 'scaffold'=>true));
    $this->define("notified", "BooleanField", array('editable'=>false));
    $this->columns['send_notification'][1]['editable'] = true;
  }

  public function before_insert(){
    $this->notified = 0;
    parent::before_insert();
  }

  public function before_save(){
    parent::before_save();
    $model = new Job;
    //check the amount of go lives for the department on this day, if its more than the number of staff in the department, flag an error
    if(($depts = $this->departments) && ($dept = $depts->filter("is_production", 1)->first()) && ($staff = $dept->staff)){

      if(($golive = date("Ymd", strtotime($this->date_go_live))) && ($found = $model->for_department($dept->primval)->filter("DATE_FORMAT(date_go_live, '%Y%m%d') = '$golive'")->all()) && $found->count()+1 > $staff->count() ){
        $this->add_error("date_go_live", $dept->title." has too many deadlines for that day.");
      }
    }

  }

  public function notifications(){
    if(!$this->notified && $this->created_by && $this->send_notification){
      $notify = new ResourceNotify;
      $emails = array();
      foreach($depts as $dept) if(($admins = $dept->admins()) && $admins && $admins->count()) foreach($admins as $staff) $emails[] = $staff;
      foreach($emails as $staff) $notify->send_job_creation($this, $staff, $emails);
      $this->update_attributes(array('notified'=>1));
    }
  }
  /**
   * from all data we now have find those that either have no work attached,
   * or who have work items that aren't set as complete
   */
  public function scope_live(){
    if($cached = Job::$scope_cache["live"]) return $this->filter("id", $cached);
    $jobs = new Job;
    $ids = array(0);
    foreach($jobs->all() as $job){
      $work = $job->work;
      if($work && ($all = $work->count())){
        $complete = $work->filter("status", "completed")->all()->count();
        if($complete >= $all) $this->filter("id", $job->primval, "!=");
      }
    }
    Job::$scope_cache["live"] = $ids;
    return $this->order("date_go_live ASC");
  }
  //find work that has nothing attached to it
  public function scope_unscheduled(){
    $ids = array(0);
    $model = new Work;
    foreach($model->filter("job_id > 0")->group("job_id")->all() as $w) $this->filter("id", $w->job_id, "!=");
    return $this;
  }

  public function scope_filters_select(){
    return $this->scope_live();
  }

  public function completion_date($format = "jS F Y"){
    return date($format, strtotime($this->date_go_live));
  }

  public function css_selector(){
    $base = parent::css_selector();
    $complete = $this->completion_date("Ymd");
    $now = date("Ymd");
    if($complete == $now) return $base . " due_now ";
    else if($complete < $now) return $base . " due_past ";
    else return $base ." due_future ";
  }

  public function times($format = "jS F Y", $use_label=true, $cols=false){
    if(!$cols) $cols = array('date_creative_required_for', 'date_internal_testing', 'date_client_testing', 'date_go_live');
    $dates = array();
    foreach($cols as $col){
      if($this->$col){
        if(!$use_label || (!$label = $this->columns[$col][1]['label'])) $label = $col;
        $dates[$label] = date($format, strtotime($this->$col));
      }
    }
    return $dates;
  }

  public function next_milestone($date=false, $labels=false, $cols=false){
    $times = $this->times("Ymd", $labels, $cols);
    if(!$date) $date = date("Ymd");
    $compare = false;
    foreach($times as $col=>$day){
      if($day >= $date){
        $compare = array('day'=>$day, 'col'=>$col);
        break;
      }
    }
    return $compare;
  }
  public function deadline(){
    return $this->completion_date("d M");
  }

  public function get_rgb(){
    return array(rand(0,124), rand(0,124), rand(0,124));
  }
  public function between($start, $end){
    return $this->filter("((`date_go_live` BETWEEN '".$start."' AND '".$end."') )");
  }



}
?>