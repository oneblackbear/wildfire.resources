<?
class Job extends WildfireResource{

  public function setup(){
    parent::setup();
    $this->define("date_internal_testing", "DateTimeField", array('label'=>'Internal testing date'));
    $this->define("date_client_testing", "DateTimeField", array('label'=>'Client testing date'));
    $this->define("date_go_live", "DateTimeField", array('label'=>'Go live date'));
    $this->define("flagged", "BooleanField");
    $this->define("comments", "ManyToManyField", array('target_model'=>"Comment", 'group'=>'relationships'));
    $this->define("schedule", "HasManyField", array('target_model'=>"Job", 'group'=>'relationships'));
    $this->define("fee", "ForeignKey", array('target_model'=>"Fee", 'group'=>'relationships'));
  }

}
?>