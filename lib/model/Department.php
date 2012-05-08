<?
class Department extends WildfireResource{

  public function setup(){
    parent::setup();
    $this->define("organisations", "ManyToManyField", array('scaffold'=>true,'target_model'=>"Organisation", 'group'=>'relationships'));
    $this->define("staff", "ManyToManyField", array('target_model'=>"Staff", 'group'=>'relationships'));
    $this->define("jobs", "ManyToManyField", array('target_model'=>"Job", 'group'=>'relationships', 'editable'=>false));
    $this->define("work", "HasManyField", array('target_model'=>"Work", 'group'=>'relationships', 'editable'=>false));
    $this->define("main_contact", "ForeignKey", array('target_model'=>"Staff", 'group'=>'relationships', 'col_name'=>"main_contact"));

    $this->define("postcode", "CharField");
    $this->define("address", "TextField");
    $this->define("telephone", "CharField");
    $this->define("email", "CharField");
    $this->define("is_production", "BooleanField");
    $this->define("comments", "ManyToManyField", array('target_model'=>"Comment", 'group'=>'relationships')); //used for notes about organisation
  }

  public function admins(){
    $staff = $this->staff;
    $model = new Staff;
    $admins= array(0);
    foreach($staff->filter("role", array("owner", "admin"))->all() as $admin) $admins[] = $admin;
    return $model->filter("id", $admins)->all();
  }

  public function work_by_staff($start, $end){
    $times = array();
    $cal = new Calendar;
    //filter dates
    foreach($this->staff->all() as $staff){
      $times[$staff->primval] = array();

      foreach($staff->work->between($start,$end)->all() as $row){
        foreach($cal->date_range_array($row->date_start, $row->date_end) as $date=>$bool) $times[$staff->primval][$date] += $row->hours;
      }
    }
    return $times;
  }

}
?>