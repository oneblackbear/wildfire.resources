<?
class Organisation extends WildfireResource{
  public function setup(){
    parent::setup();
    $this->define("is_client", "BooleanField");
    $this->define("account_handler", "ForeignKey", array('target_model'=>"Staff", 'group'=>'relationships', 'scaffold'=>true));
    $this->define("departments", "ManyToManyField", array('target_model'=>"Department", 'group'=>'relationships'));
    $this->define("staff", "ManyToManyField", array('target_model'=>"Staff", 'group'=>'relationships'));
    $this->define("fee", "ForeignKey", array('target_model'=>"Fee", 'group'=>'relationships'));
  }


}
?>