<?
class Responsibility extends WildfireResource{

  public static $scope_cache = array();
  public function setup(){
    parent::setup();
    $this->columns['id'][1]['scaffold']=true;
    $this->columns['title'][1]['editable']=false;
    $this->columns['title'][1]['scaffold']=false;
    $this->columns['content'][1]['editable']=false;
    $this->define("client", "ForeignKey", array('target_model'=>"Organisation", 'group'=>'allocations', 'scaffold'=>true, 'eager_load'=>false));
    $this->define("staff", "ManyToManyField", array('target_model'=>"Staff", 'group'=>'relationships', 'scaffold'=>true));
    $this->define("project_lead", "ForeignKey", array('target_model'=>"Staff", 'group'=>'relationships', 'scaffold'=>true));
  }

  public function before_save(){
    if(!$this->title) $this->title = "Responsibility";
    parent::before_save();
  }


}
?>