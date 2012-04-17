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
    $this->define("flagged", "BooleanField");
    $this->define("comments", "ManyToManyField", array('target_model'=>"Comment", 'group'=>'relationships'));
    $this->define("schedule", "HasManyField", array('target_model'=>"Job", 'group'=>'relationships'));
    $this->define("fee", "ForeignKey", array('target_model'=>"Fee", 'group'=>'relationships'));
    $this->define("client", "ForeignKey", array('target_model'=>"Organisation", 'group'=>'relationships', 'scaffold'=>true));

  }

}
?>