<?
class Organisation extends WildfireResource{
  public function setup(){
    parent::setup();
    $this->define("departments", "ManyToManyField", array('target_model'=>"Department", 'group'=>'relationships'));
    $this->define("staff", "ManyToManyField", array('target_model'=>"Staff", 'group'=>'relationships'));
  }


}
?>