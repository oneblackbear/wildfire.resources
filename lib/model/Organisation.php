<?
class Organisation extends WildfireResource{
  public function setup(){
    parent::setup();
    $this->columns['content'][1]['editable'] = true;
    $this->define("is_client", "BooleanField");
    $this->define("account_handler", "ForeignKey", array('target_model'=>"Staff", 'group'=>'relationships', 'scaffold'=>true));

    $this->define("departments", "ManyToManyField", array('target_model'=>"Department", 'group'=>'relationships'));
    $this->define("staff", "ManyToManyField", array('target_model'=>"Staff", 'group'=>'relationships'));
    $this->define("work", "HasManyField", array('target_model'=>"Work", 'group'=>'relationships', 'editable'=>false));
    $this->define("fee", "ForeignKey", array('target_model'=>"Fee", 'group'=>'relationships'));
    $this->define("jobs", "HasManyField", array('target_model'=>"Job", 'group'=>'relationships', 'editable'=>false));

    //advanced fields
    $this->define("postcode", "CharField");
    $this->define("address", "TextField");
    $this->define("telephone", "CharField");
    $this->define("email", "CharField", array('required'=>true));
    $this->define("comments", "ManyToManyField", array('target_model'=>"Comment", 'group'=>'relationships')); //used for notes about organisation
  }


}
?>