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
    $this->define("comments", "GroupManyToManyField", array('target_model'=>"Comment", 'group'=>'relationships','editable'=>false));
    $this->define("work", "GroupHasManyField", array('target_model'=>"Work", 'group'=>'relationships', 'eager_load'=>true));
    $this->define("fee", "GroupForeignKey", array('target_model'=>"Fee", 'group'=>'relationships', 'eager_load'=>false));
    $this->define("client", "GroupForeignKey", array('target_model'=>"Organisation", 'group'=>'relationships', 'scaffold'=>true, 'eager_load'=>false));
    $this->define("departments", "GroupManyToManyField", array('target_model'=>"Department", 'group'=>'relationships', 'scaffold'=>true, 'eager_load'=>false));
    $this->define("notified", "BooleanField", array('editable'=>false));
  }

  public function before_insert(){
    $this->notified = 0;
    parent::before_insert();
  }

  public function before_save(){
    parent::before_save();
    $model = new Job;
    //check the amount of go lives for the department on this day, if its more than the number of staff in the department, flag an error
    $depts = false;
    if(($posted = Request::param($this->table)) && ($posted = $posted['departments'])){
      $d = new Department;
      $depts = $d->filter("id", $posted)->all();
    }elseif(($departmentjoin = $this->departments) && $departmentjoin->count()) $depts = $departmentjoin;

    if(($depts) && ($dept = $depts->first()) ){
      $golive = date("Ymd", strtotime($this->date_go_live));
      $found = $model->for_department($dept->primval)->filter("DATE_FORMAT(date_go_live, '%Y%m%d') = '$golive'")->all();
      if($golive && ($found) && ($found->count() > $dept->deadlines_allowed)){
        $this->add_error("date_go_live", $dept->title." has too many deadlines for that day.");
      }
    }
    $this->send_notification = 1;

  }

  public function notifications(){
    if(!$this->notified && $this->created_by && $this->send_notification){
      $notify = new ResourceNotify;
      $emails = $this->contact_emails();
      foreach($emails as $staff) $notify->send_job_creation($this, $staff, $emails);
      $this->update_attributes(array('notified'=>1));
    }else if($this->created_by && $this->send_notification){
      $notify = new ResourceNotify;
      $emails = $this->contact_emails();
      foreach($emails as $staff) $notify->send_job_updated($this, $staff, $emails);
    }
  }

  public function contact_emails(){
    $emails = array();
    foreach($this->departments as $dept) if(($admins = $dept->admins()) && $admins && $admins->count()) foreach($admins as $staff) $emails[$staff->primval] = $staff;
    if($this->created_by && ($st = new Staff($this->created_by))) $emails[$st->primval] = $st->email;
    return $emails;
  }
  /**
   * from all data we now have find those that either have no work attached,
   * or who have work items that aren't set as complete
   */
  public function scope_live(){
    $ids = array(0);
    if(!$ids = Job::$scope_cache["live"]){
      $ids = array(0);
      $jobs = new Job;
      foreach($jobs->filter("group_token", $this->group_token)->all() as $job){
        $work = $job->work;
        if($work && ($all = $work->count())){
          $complete = $work->filter("status", "completed")->all()->count();
          if($complete >= $all) $ids[] = $job->primval;
        }
      }
    }

    if(count($ids)){
      if(count($ids) > 1) Job::$scope_cache["live"] = $ids;
      foreach($ids as $id) $this->filter("id", $id, "!=");
    }
    return $this->order("title ASC");
  }

  public function scope_ordered(){
    return $this->order("date_created DESC");
  }
  //find work that has nothing attached to it
  public function scope_unscheduled(){
    $ids = array(0);
    $model = new Work;
    foreach($model->filter("group_token", $this->group_token)->filter("job_id > 0")->group("job_id")->all() as $w) $this->filter("id", $w->job_id, "!=");
    return $this;
  }

  public function scheduled(){
    $model = new Work;
    $res = $model->filter("job_id", $this->primval)->all();
    $count = $res->count();
    return ($count) ? $count : 0;
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