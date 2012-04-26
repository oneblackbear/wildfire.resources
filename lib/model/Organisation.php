<?
class Organisation extends WildfireResource{
  public function setup(){
    parent::setup();
    $this->define("is_client", "BooleanField");
    $this->define("account_handler", "ForeignKey", array('target_model'=>"Staff", 'group'=>'relationships', 'scaffold'=>true));
    $this->define("main_contact", "ForeignKey", array('target_model'=>"Staff", 'group'=>'relationships', 'col_name'=>"main_contact"));

    $this->define("departments", "ManyToManyField", array('target_model'=>"Department", 'group'=>'relationships'));
    $this->define("staff", "ManyToManyField", array('target_model'=>"Staff", 'group'=>'relationships'));
    $this->define("work", "ManyToManyField", array('target_model'=>"Work", 'group'=>'relationships', 'editable'=>false));
    $this->define("fee", "ForeignKey", array('target_model'=>"Fee", 'group'=>'relationships'));

    //advanced fields
    $this->define("postcode", "CharField");
    $this->define("address", "TextField");
    $this->define("telephone", "CharField");
    $this->define("email", "CharField", array('required'=>true));
    $this->define("comments", "ManyToManyField", array('target_model'=>"Comment", 'group'=>'relationships')); //used for notes about organisation
  }


}
?>