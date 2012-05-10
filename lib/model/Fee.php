<?
class Fee extends WildfireResource{

  public function setup(){
    parent::setup();
    $this->define("date_start", "DateTimeField", array('scaffold'=>true, 'default'=>"now", 'output_format'=>"j F Y",'input_format'=> 'j F Y H:i', 'info_preview'=>1));
    $this->define("date_end", "DateTimeField", array('scaffold'=>true, 'default'=>date("Y-m-d",mktime(0,0,0, date("m"), date("j"), date("y")-10 )), 'output_format'=>"j F Y", 'input_format'=> 'j F Y H:i','info_preview'=>1));
    $this->define("hours", "FloatField", array('maxlength'=>"12,2", 'scaffold'=>true));
    $this->define("client", "ForeignKey", array('target_model'=>"Organisation", 'group'=>'relationships'));
    $this->define("jobs", "GroupHasManyField", array('target_model'=>"Job", 'group'=>'relationships'));
  }

}
?>