<?
class Work extends WaxModel{

  public static $status_options = array('scheduled'=>'scheduled', 'in-progress'=>'in-progress', 'pending-approval'=>'pending approval', 'completed'=>'completed');

  public function setup(){
    $this->define("title", "CharField");
    $this->define("job", "ForeignKey", array('target_model'=>"Job", 'group'=>'relationships', 'scaffold'=>true));
    $this->define("depends_on", "ForeignKey", array('target_model'=>"Work", 'group'=>'relationships', 'scaffold'=>true));
    $this->define("assigned_to", "ManyToManyField", array('target_model'=>"Staff", 'group'=>'relationships', 'scaffold'=>true));
    $this->define("date_start", "DateTimeField", array('label'=>'Start', 'scaffold'=>true));
    $this->define("date_end", "DateTimeField", array('label'=>'End', 'scaffold'=>true));
    $this->define("hours", "FloatField", array('maxlength'=>'12,2', 'scaffold'=>true));
    $this->define("status", "CharField", array('widget'=>'SelectInput', 'choices'=>self::$status_options));
  }

  public function before_save(){
    if(!$this->title) $this->title = "WORK";
  }

}
?>