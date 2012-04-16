<?
class Fee extends WildfireResource{

  public function setup(){
    parent::setup();
    $this->define("client", "ForeignKey", array('target_model'=>"Client", 'group'=>'relationships'));
    $this->define("job", "ManyToManyField", array('target_model'=>"Job", 'group'=>'relationships'));
  }

}
?>