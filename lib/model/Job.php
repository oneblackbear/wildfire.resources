<?
class Job extends WildfireResource{

  public static $scope_cache = array();
  public function setup(){
    parent::setup();
    $this->columns['id'][1]['scaffold']=true;
    $this->define("approved_cost_estimate", "FloatField", array('maxlength'=>"12,2", 'scaffold'=>true));
    $this->define("hours_estimated", "FloatField", array('required'=>true, 'maxlength'=>"12,2", 'scaffold'=>true, 'group'=>'hours', 'label'=>'Estimated hours <span class="required">*</span>'));
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
    //this is flag for admin jobs like holidays etc
    $this->define("permanent_job", "BooleanField");
    $this->define("dead", "BooleanField", array('editable'=>true));
    $this->define("complete", "BooleanField", array('editable'=>true, 'scaffold'=>true));
    $this->define("billed", "BooleanField", array('editable'=>true, 'scaffold'=>true));
  }

  public function before_insert(){
    $this->notified = 0;
    parent::before_insert();
  }

  public function before_save(){
    parent::before_save();


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
    return $this->filter("group_token", Session::get("GROUP"))->order("title ASC");
  }

  public function scope_ordered(){
    return $this->order("date_created DESC");
  }
  //find work that has nothing attached to it
  public function unscheduled($department_id=false){
    $ids = array(0);
    $model = new Work;
    if($department_id) $model->filter("department_id", $department_id);
    foreach($model->filter("group_token", $this->group_token)->filter("job_id > 0")->group("job_id")->all() as $w) $this->filter("id", $w->job_id, "!=");
    return $this->filter("dead", 0)->filter("complete", 0);
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