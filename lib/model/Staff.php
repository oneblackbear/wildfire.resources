<?
//do admin by staff being a member of a 'super admin' user group - they can then do scheduling etc for the dept. they are in
//add in passwords, email, usernames & general contact details
class Staff extends WildfireResource{
  public static $days_of_week = array('monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday');
  public function setup(){
    parent::setup();
    $this->define("departments", "ManyToManyField", array('target_model'=>"Department", 'group'=>'relationships'));
    $this->define("organisations", "ManyToManyField", array('target_model'=>"Organisation", 'group'=>'relationships'));
    $this->define("clients", "ManyToManyField", array('target_model'=>"Client", 'group'=>'relationships'));
    foreach(self::$days_of_week as $day) $this->define("hours_on_".$day, "IntegerField", array('maxlength'=>2, 'default'=>0));
  }

}
?>