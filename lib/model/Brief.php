<?
class Brief extends WildfireResource{
  public function setup(){
    $this->define("title", "CharField", array('required'=>true, 'maxlength'=>255, 'scaffold'=>true, 'label'=>"Name", 'info_preview'=>1) );
    $this->define("content", "TextField", array('widget'=>"TinymceTextareaInput", 'label'=>'Description'));
    $this->define("media", "ManyToManyField", array('editable'=>false,'target_model'=>"WildfireMedia", "eager_loading"=>true, "join_model_class"=>"WildfireOrderedTagJoin", "join_order"=>"join_order", 'group'=>'media', 'module'=>'media'));
    $this->define("date_modified", "DateTimeField", array('export'=>true, 'scaffold'=>true, "editable"=>false));
    $this->define("date_created", "DateTimeField", array('export'=>true, "editable"=>false));
    $this->define("created_by", "IntegerField", array('widget'=>'HiddenInput'));
    $this->define("group_token", "CharField", array('widget'=>'HiddenInput', 'info_preview'=>1));
    parent::setup();
  }


}
?>