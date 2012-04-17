<?
class Department extends WildfireResource{

  public function setup(){
    parent::setup();
    $this->define("organisations", "ManyToManyField", array('target_model'=>"Organisation", 'group'=>'relationships'));
    $this->define("staff", "ManyToManyField", array('target_model'=>"Staff", 'group'=>'relationships'));
    $this->define("main_contact", "ForeignKey", array('target_model'=>"Staff", 'group'=>'relationships', 'col_name'=>"main_contact"));

    $this->define("postcode", "CharField");
    $this->define("address", "TextField");
    $this->define("telephone", "CharField");
    $this->define("email", "CharField");
    $this->define("comments", "ManyToManyField", array('target_model'=>"Comment", 'group'=>'relationships')); //used for notes about organisation
  }

}
?>