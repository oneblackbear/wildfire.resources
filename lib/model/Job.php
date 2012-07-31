<?
class Job extends WildfireResource{

  public static $scope_cache = array();
  public function setup(){
    parent::setup();
    $this->columns['id'][1]['scaffold']=true;
    $this->define("hours_estimated", "FloatField", array('required'=>true, 'maxlength'=>"12,2", 'scaffold'=>true, 'group'=>'hours', 'label'=>'Estimated hours <span class="required">*</span>'));
    $this->define("hours_actual", "FloatField", array('maxlength'=>"12,2", 'scaffold'=>true, 'group'=>'hours', 'editable'=>false));
    $this->define("date_go_live", "DateTimeField", array('label'=>'Due date <span class="required">*</span>', 'required'=>true, 'scaffold'=>true, 'date_col'=>true, 'group'=>'hours'));
    $this->define("date_creative_required_for", "DateTimeField", array('label'=>'Creative required for', 'date_col'=>true, 'group'=>'hours'));
    $this->define("date_internal_testing", "DateTimeField", array('label'=>'Internal testing date', 'date_col'=>true, 'group'=>'hours'));
    $this->define("date_client_testing", "DateTimeField", array('label'=>'Client testing date', 'date_col'=>true, 'group'=>'hours'));
    $this->define("flagged", "BooleanField", array('editable'=>false, 'scaffold'=>$this->is_editable()));
    $this->define("comments", "ManyToManyField", array('target_model'=>"Comment", 'group'=>'allocations','editable'=>false));
    $this->define("work", "HasManyField", array('target_model'=>"Work", 'group'=>'allocations', 'eager_load'=>true, 'editable'=>false));
    $this->define("fee", "ForeignKey", array('target_model'=>"Fee", 'group'=>'allocations', 'eager_load'=>false));
    $this->define("client", "ForeignKey", array('target_model'=>"Organisation", 'group'=>'allocations', 'scaffold'=>true, 'eager_load'=>false));
    $this->define("departments", "ManyToManyField", array('target_model'=>"Department", 'group'=>'allocations', 'scaffold'=>true, 'eager_load'=>false));
    $this->define("notified", "BooleanField", array('editable'=>false));
    $this->define("rating", "IntegerField", array('editable'=>false, 'scaffold'=>$this->is_editable(), 'widget'=>'SelectInput', 'choices'=>range(0, 5)) );
    //this is flag for admin jobs like holidays etc
    $this->define("permanent_job", "BooleanField");
    $this->define("dead", "BooleanField", array('editable'=>$this->is_editable()));
    $this->define("complete", "BooleanField", array('editable'=>$this->is_editable()));
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

    if(($depts) && ($depts->count()) ){
      $golive = date("Ymd", strtotime($this->date_go_live));
      foreach($depts as $d){
        $j = new Job("live");
        if($this->primval); $j->filter("id", $this->primval, "!=");
        $found = $j->for_department($d->primval)->filter("DATE_FORMAT(date_go_live, '%Y%m%d') = '$golive'")->all();
        if($golive && ($found) && ($found->count() > $d->deadlines_allowed)){
          $job_id_string = "";
          foreach($found as $f) $job_id_string .= "#".$f->primval.", ";
          $this->add_error("date_go_live", $d->title." has too many deadlines for  ".date("jS F", strtotime($this->date_go_live))." (".$found->count()." / ".$d->deadlines_allowed.") - ". trim($job_id_string, ", "));
        }
      }
    }
    //check the content/description of this job
    $words = explode(" ", trim($this->content));
    $words = array_unique($words);
    //disgard any <=3 letter words
    foreach($words as $i=>$w) if(strlen($w) <= 3) unset($words[$i]);
    if(count($words) < 7) $this->add_error("content", "Please provide a better description of the job (".count($words).")");
    //make sure its a deadline during working week and make sure deadline is the latest day
    $go_live = date("Ymd", strtotime($this->date_go_live));
    foreach($this->get_date_cols() as $name=>$details){
      if(($val = $this->$name) && ($day = date("N", strtotime($val))) && $day > 5) $this->add_error($name, "Must be within the working week.");
      if($name != "date_go_live" && ($val = $this->$name) && ($comp = date("Ymd", strtotime($val))) && $comp > $go_live) $this->add_error($name, "$name ($comp) cannot be after the go live date ($go_live)");
    }

    $this->send_notification = 1;
  }

  public function notifications(){
    if(!$this->notified && $this->created_by && $this->send_notification){
      // $notify = new ResourceNotify;
      // $emails = $this->contact_emails();
      // foreach($emails as $staff) $notify->send_job_creation($this, $staff, $emails);
      $this->update_attributes(array('notified'=>1));
    }else if($this->created_by && $this->send_notification){
      // $notify = new ResourceNotify;
      // $emails = $this->contact_emails();
      // foreach($emails as $staff) $notify->send_job_updated($this, $staff, $emails);
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
        if($job->permanent_job) $ids[] = $job->primval;
        else if(!$job->dead && !$job->complete && ($work = $job->work) && ($all = $work->count())){
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
    foreach($model->filter("dead", 0)->filter("complete", 0)->filter("group_token", $this->group_token)->filter("job_id > 0")->group("job_id")->all() as $w) $this->filter("id", $w->job_id, "!=");
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

  public function get_date_cols(){
    $cols = array();
    foreach($this->columns as $col=>$info) if($info[1]['date_col']) $cols[$col] = $info;
    return $cols;
  }

  public function times($format = "jS F Y", $use_label=true, $cols=false){
    if(!$cols) $cols = array_keys($this->get_date_cols());
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

  public function search_details(){
    return "#".$this->primval." - ".parent::search_details();
  }


}
?>