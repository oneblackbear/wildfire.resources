<?
class Department extends WildfireResource{

  public function setup(){
    parent::setup();
    $this->define("organisations", "ManyToManyField", array('target_model'=>"Organisation", 'group'=>'relationships'));
    $this->define("staff", "ManyToManyField", array('target_model'=>"Staff", 'group'=>'relationships'));

    $this->define("postcode", "CharField");
    $this->define("address", "TextField");
    $this->define("telephone", "CharField");
    $this->define("email", "CharField");
    $this->define("comments", "ManyToManyField", array('target_model'=>"Comment", 'group'=>'relationships')); //used for notes about organisation
  }

}
?>