<?
class Department extends WildfireResource{

  public function setup(){
    parent::setup();
    $this->define("organisations", "ManyToManyField", array('target_model'=>"Organisation", 'group'=>'relationships'));
    $this->define("staff", "ManyToManyField", array('target_model'=>"Staff", 'group'=>'relationships'));
  }

}
?>