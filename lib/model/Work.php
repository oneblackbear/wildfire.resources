<?
class Work extends WaxModel{

  public function setup(){
    $this->define("job", "ForeignKey", array('target_model'=>"Job", 'group'=>'relationships'));
    $this->define("depends_on", "ForeignKey", array('target_model'=>"Work", 'group'=>'relationships'));
    $this->define("date_start", "DateTimeField", array('label'=>'Internal testing date'));
    $this->define("date_end", "DateTimeField", array('label'=>'Client testing date'));
    $this->define("flagged", "BooleanField");
  }

}
?>