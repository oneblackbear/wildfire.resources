<?
class Staff extends WildfireResource{
  public static $salt = "0bb";
  public static $days_of_week = array('monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday');
  public static $roles = array('standard'=>'standard', 'privileged'=>'privileged', 'admin'=>'admin', 'owner'=>'owner');
  public function setup(){
    parent::setup();
    unset($this->columns['person']);
    $this->define("departments", "ManyToManyField", array('target_model'=>"Department", 'group'=>'relationships', 'scaffold'=>true));
    $this->define("organisations", "ManyToManyField", array('target_model'=>"Organisation", 'group'=>'relationships'));
    foreach(self::$days_of_week as $day) $this->define("hours_on_".$day, "IntegerField", array('maxlength'=>2, 'default'=>0));
    $this->define("telephone", "CharField", array('scaffold'=>true, 'export'=>true));
    $this->define("email", "CharField", array('required'=>true,'scaffold'=>true, 'export'=>true));
    $this->define("original_email", "CharField", array('editable'=>false));
    $this->define("password", "PasswordField", array('label'=>'Enter your password', 'group'=>'password'));
    $this->define("role", "CharField", array('widget'=>'SelectInput', 'choices'=>self::$roles));
  }

  public function before_insert(){
    $this->original_email = $this->email;
    if($this->password) $this->password = hash_hmac("sha1", $this->password, self::$salt);
  }


}
?>