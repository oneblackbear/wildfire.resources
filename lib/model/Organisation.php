<?
class Organisation extends WildfireResource{
  public function setup(){
    parent::setup();
    $this->columns['content'][1]['editable'] = false;
    $this->define("is_client", "BooleanField");
    $this->define("account_handler", "GroupForeignKey", array('target_model'=>"Staff", 'group'=>'relationships', 'scaffold'=>true));

    $this->define("departments", "ManyToManyField", array('target_model'=>"Department", 'group'=>'relationships'));
    $this->define("staff", "ManyToManyField", array('target_model'=>"Staff", 'group'=>'relationships'));
    $this->define("work", "GroupHasManyField", array('target_model'=>"Work", 'group'=>'relationships', 'editable'=>false));
    $this->define("fee", "GroupForeignKey", array('target_model'=>"Fee", 'group'=>'relationships'));
    $this->define("jobs", "GroupHasManyField", array('target_model'=>"Job", 'group'=>'relationships', 'editable'=>false));

    //advanced fields
    $this->define("postcode", "CharField");
    $this->define("address", "TextField");
    $this->define("telephone", "CharField");
    $this->define("email", "CharField", array('required'=>true));
    $this->define("comments", "ManyToManyField", array('target_model'=>"Comment", 'group'=>'relationships')); //used for notes about organisation
  }


}
?>