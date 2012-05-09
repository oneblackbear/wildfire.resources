<?
class AccessToken extends WildfireResource{

  public function setup(){
    $this->define("title", "CharField", array('required'=>true, 'maxlength'=>255, 'scaffold'=>true, 'label'=>"Name", 'info_preview'=>1) );
    $this->define("content", "TextField", array('widget'=>"TinymceTextareaInput", 'label'=>'Description'));
    $this->define("date_modified", "DateTimeField", array('export'=>true, 'scaffold'=>true, "editable"=>false));
    $this->define("date_created", "DateTimeField", array('export'=>true, "editable"=>false));
    $this->define("created_by", "IntegerField", array('widget'=>'HiddenInput'));
    $this->define("group_token", "CharField", array('widget'=>'HiddenInput', 'info_preview'=>1));
    parent::setup();
    $this->define("send_notification", "BooleanField", array('editable'=>false, 'default'=>1));
    $this->define("staff", "ForeignKey", array('target_model'=>'Staff'));
  }
}
?>