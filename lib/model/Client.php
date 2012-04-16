<?
class Client extends WildfireResource{

  public function setup(){
    parent::setup();
    $this->define("staff", "ManyToManyField", array('target_model'=>"Staff", 'group'=>'relationships'));
  }

}
?>