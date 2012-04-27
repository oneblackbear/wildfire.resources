<?
class Job extends WildfireResource{
  public function setup(){
    parent::setup();
    $this->define("hours_estimated", "FloatField", array('required'=>true, 'maxlength'=>"12,2", 'scaffold'=>true));
    $this->define("hours_actual", "FloatField", array('maxlength'=>"12,2", 'scaffold'=>true));
    $this->define("date_creative_required_for", "DateTimeField", array('label'=>'Creative required for'));
    $this->define("date_internal_testing", "DateTimeField", array('label'=>'Internal testing date'));
    $this->define("date_client_testing", "DateTimeField", array('label'=>'Client testing date'));
    $this->define("date_go_live", "DateTimeField", array('label'=>'Go live date', 'required'=>true, 'scaffold'=>true));
    $this->define("flagged", "BooleanField", array('editable'=>$this->is_editable(), 'scaffold'=>$this->is_editable()));
    $this->define("comments", "ManyToManyField", array('target_model'=>"Comment", 'group'=>'relationships'));
    $this->define("work", "HasManyField", array('target_model'=>"Work", 'group'=>'relationships'));
    $this->define("fee", "ForeignKey", array('target_model'=>"Fee", 'group'=>'relationships'));
    $this->define("client", "ForeignKey", array('target_model'=>"Organisation", 'group'=>'relationships', 'scaffold'=>true));
    $this->define("departments", "ManyToManyField", array('target_model'=>"Department", 'group'=>'relationships', 'scaffold'=>true));
  }

  public function is_editable(){
    $allowed = false;
    if(Session::get("LOGGED_IN_ROLE") == "owner" || Session::get("LOGGED_IN_ROLE") == "admin") return true;
    return $allowed;
  }

  public function completion_date($format = "jS F Y"){
    if($this->date_client_testing) return date($format, strtotime($this->date_client_testing));
    else return date($format, strtotime($this->date_go_live));
  }

  public function css_selector(){
    $base = parent::css_selector();
    $complete = $this->completion_date("Ymd");
    $now = date("Ymd");
    if($complete == $now) return $base . " due_now ";
    else if($complete < $now) return $base . " due_past ";
    else return $base ." due_future ";
  }

  public function times($format = "jS F Y", $use_label=true, $cols=array('date_creative_required_for', 'date_internal_testing', 'date_client_testing', 'date_go_live')){
    $dates = array();
    foreach($cols as $col){
      if($this->$col){
        if(!$use_label || (!$label = $this->columns[$col][1]['label'])) $label = $col;
        $dates[$label] = date($format, strtotime($this->$col));
      }
    }
    return $dates;
  }

  public function next_milestone($date=false, $labels=false){
    $times = $this->times("Ymd", $labels);
    if(!$date) $date = date("Ymd");
    $compare = false;
    foreach($times as $col=>$day){
      if($day > $date){
        $compare = array('day'=>$day, 'col'=>$col);
        break;
      }
    }
    return $compare;
  }
}
?>