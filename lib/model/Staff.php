<?
class Staff extends WildfireResource{

  public function setup(){
    parent::setup();
    $this->define("departments", "ManyToManyField", array('target_model'=>"Department", 'group'=>'relationships'));
    $this->define("organisations", "ManyToManyField", array('target_model'=>"Organisation", 'group'=>'relationships'));
  }


}
?>