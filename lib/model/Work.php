<?
class Work extends WaxModel{

  public function setup(){
    $this->define("job", "ForeignKey", array('target_model'=>"Job", 'group'=>'relationships', 'scaffold'=>true));
    $this->define("depends_on", "ForeignKey", array('target_model'=>"Work", 'group'=>'relationships', 'scaffold'=>true));
    $this->define("date_start", "DateTimeField", array('label'=>'Start', 'scaffold'=>true));
    $this->define("date_end", "DateTimeField", array('label'=>'End', 'scaffold'=>true));
    $this->define("duration", "FloatField", array('maxlength'=>'12,2', 'scaffold'=>true));
    $this->define("staff", "ManyToManyField", array('target_model'=>"Staff", 'group'=>'relationships'));
  }

}
?>