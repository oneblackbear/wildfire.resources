<?
class Staff extends WildfireResource{
  public static $days_of_week = array('monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday');
  public function setup(){
    parent::setup();
    $this->define("departments", "ManyToManyField", array('target_model'=>"Department", 'group'=>'relationships', 'scaffold'=>true));
    $this->define("organisations", "ManyToManyField", array('target_model'=>"Organisation", 'group'=>'relationships'));
    $this->define("clients", "ManyToManyField", array('target_model'=>"Client", 'group'=>'relationships'));
    foreach(self::$days_of_week as $day) $this->define("hours_on_".$day, "IntegerField", array('maxlength'=>2, 'default'=>0));
    $this->define("telephone", "CharField", array('scaffold'=>true, 'export'=>true));
    $this->define("email", "CharField", array('scaffold'=>true, 'export'=>true));
    $this->define("password", "PasswordField", array('label'=>'Enter your password', 'group'=>'password'));
  }



}
?>