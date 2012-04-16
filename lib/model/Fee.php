<?
class Fee extends WildfireResource{

  public function setup(){
    parent::setup();
    $this->define("date_start", "DateTimeField", array('export'=>true, 'default'=>"now", 'output_format'=>"j F Y",'input_format'=> 'j F Y H:i', 'info_preview'=>1));
    $this->define("date_end", "DateTimeField", array('export'=>true, 'default'=>date("Y-m-d",mktime(0,0,0, date("m"), date("j"), date("y")-10 )), 'output_format'=>"j F Y", 'input_format'=> 'j F Y H:i','info_preview'=>1));

    $this->define("client", "ForeignKey", array('target_model'=>"Client", 'group'=>'relationships'));
    $this->define("jobs", "HasManyField", array('target_model'=>"Job", 'group'=>'relationships'));
  }

}
?>