<?
class Staff extends WildfireResource{

  public static $days_of_week = array('monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday');
  public static $roles = array('standard'=>'standard', 'privileged'=>'privileged', 'admin'=>'admin', 'owner'=>'owner');
  public static $permission_cache = array();
  public function setup(){
    parent::setup();
    $this->define("departments", "ManyToManyField", array('target_model'=>"Department", 'group'=>'relationships', 'scaffold'=>true));
    $this->define("organisations", "ManyToManyField", array('target_model'=>"Organisation", 'group'=>'relationships'));
    foreach(self::$days_of_week as $day) $this->define("hours_on_".$day, "IntegerField", array('maxlength'=>2, 'default'=>0));
    $this->define("telephone", "CharField", array('scaffold'=>true, 'export'=>true));
    $this->define("email", "CharField", array('required'=>true,'scaffold'=>true, 'export'=>true, 'unique'=>true));
    $this->define("original_email", "CharField", array('editable'=>false));
    $this->define("password", "PasswordField", array('label'=>'Enter your password', 'group'=>'password'));
    $this->define("role", "CharField", array('widget'=>'SelectInput', 'choices'=>self::$roles));
  }

  public function before_insert(){
    parent::before_insert();
    $this->original_email = $this->email;
    if($this->password) $this->password = hash_hmac("sha1", $this->password, self::$salt);
  }

  //find all chunks of the site that this user has access to
  public function permissions($for_navigation=true){
    if(Staff::$permission_cache[$this->primval]) return Staff::$permission_cache[$this->primval];
    $permissions = array();
    //find all controller directories
    foreach(AutoLoader::controller_paths() as $path){
      //find all controller class files
      foreach(glob($path."*Controller.php") as $obj){
        //from filename, create class name
        $name = basename($obj, ".php");
        //create a controller
        $controller = new $name(false,false);
        $methods = array();
        //if its a basecontroller then look for its public methods
        if($name != "BaseController" && $controller instanceOf BaseController){
          $reflection = new ReflectionClass($controller);
          foreach($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $info){
            //if the class that has the method is either are basecontroller or the class itself
            if(($info->class == "BaseController" || $info->class == $name) && $info->name[0] != "_" && $info->name != "controller_global"){
              if(!($roles = $controller->permissions[$info->name]) || ($roles && in_array($this->role, $roles) ) ){
                if(($for_navigation && in_array($info->name, $controller->navigation_links)) || !$for_navigation){
                  if($info->name == "index") $methods[] = "Overview";
                  else $methods[$info->name] = Inflections::humanize($info->name);
                }
              }
            }
          }
          if(count($methods)) $permissions[strtolower(basename($name,"Controller"))] = array('options'=>$methods, 'name'=>(($controller->name)?$controller->name : basename($name,"Controller")));
        }
      }
    }
    ksort($permissions);
    Staff::$permission_cache[$this->primval] = $permissions;
    return $permissions;
  }

}
?>