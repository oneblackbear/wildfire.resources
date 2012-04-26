<?
class Work extends WaxModel{

  public static $status_options = array('scheduled'=>'scheduled', 'pending-approval'=>'pending approval', 'completed'=>'completed');

  public function setup(){
    $this->define("title", "CharField", array('scaffold'=>true));
    $this->define("staff", "ManyToManyField", array('target_model'=>"Staff", 'group'=>'relationships', 'scaffold'=>true));

    $this->define("depends_on", "ForeignKey", array('target_model'=>"Work", 'group'=>'relationships'));
    $this->define("date_start", "DateTimeField", array('label'=>'Start', 'scaffold'=>true));
    $this->define("date_end", "DateTimeField", array('label'=>'End', 'scaffold'=>true));
    $this->define("hours", "FloatField", array('maxlength'=>'12,2', 'scaffold'=>true));
    $this->define("hours_used", "FloatField", array('maxlength'=>'12,2', 'label'=>'Actual hours'));
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
    if(!$this->title) $this->title = "WORK";
    $this->date_modified = date("Y-m-d H:i:s");
  }

  public function who(){
    if(($staff = $this->staff) && $staff->count() && ($first = $staff->first())) return $first->title;
    else return "?";
  }
  public function colour($join="jobs"){
    if(($items = $this->$join) && ($item = $items->first())){
      if($item->primval%3 == 0) $pattern = "ff0000";
      else if($item->primval%5 == 0) $pattern = "00ff00";
      else $pattern = "0000ff";
      $colours = new CSSColour;
      $remainder = $item->primval%9;
      return $colours->lighten($pattern, $remainder/10);
    }else return "ececec";
  }
}
?>