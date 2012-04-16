<?
class Client extends WildfireResource{

  public function setup(){
    parent::setup();
    $this->define("account_handler", "ManyToManyField", array('target_model'=>"Staff", 'group'=>'relationships', 'scaffold'=>true));
  }

}
?>