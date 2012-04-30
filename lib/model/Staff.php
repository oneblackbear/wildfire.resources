<?
class Staff extends WildfireResource{

  public static $days_of_week = array('monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday');
  public static $roles = array('standard'=>'client', 'privileged'=>'staff', 'admin'=>'admin', 'owner'=>'owner');
  public static $permission_cache = array();
  public static $hours_per_day_cache = array();
  public static $work_cache = array();
  public function setup(){
    parent::setup();
    $this->define("organisations", "ManyToManyField", array('target_model'=>"Organisation", 'group'=>'relationships','scaffold'=>true));
    $this->define("departments", "ManyToManyField", array('target_model'=>"Department", 'group'=>'relationships', 'scaffold'=>true));
    $this->define("work", "HasManyField", array('target_model'=>"Work", 'group'=>'relationships', 'editable'=>false));
    foreach(self::$days_of_week as $day) $this->define("hours_on_".$day, "FloatField", array('maxlength'=>"5,2", 'default'=>0, 'group'=>'hours and rates'));
    $this->define("rate", "FloatField", array('maxlength'=>"5,2", 'default'=>0, 'group'=>'hours and rates'));
    $this->define("telephone", "CharField", array('scaffold'=>true, 'export'=>true));
    $this->define("email", "CharField", array('required'=>true,'scaffold'=>true, 'export'=>true, 'unique'=>true));
    $this->define("original_email", "CharField", array('editable'=>false));
    $this->define("password", "PasswordField", array('label'=>'Enter your password', 'group'=>'password', 'editable'=>false));
    $this->define("role", "CharField", array('widget'=>'SelectInput', 'choices'=>self::get_roles()));
    $this->define("date_active", "DateTimeField", array('editable'=>false));
  }

  public static function get_roles(){
    $roles = self::$roles;
    if($role = Session::get("LOGGED_IN_ROLE")){
      $keys = array_flip(array_keys($roles));
      $pos = $keys[$role];
      $roles = array_slice($roles, 0, $pos+1);
    }
    return $roles;
  }

  public static function hours_available($day_of_week="monday", $token){
    $day_of_week = strtolower($day_of_week);
    $time = 0;
    if($time = Staff::$hours_per_day_cache[$day_of_week]) return $time;
    else{
      $model = new Staff;
      foreach($model->filter("group_token", $token)->filter("`hours_on_".$day_of_week."` > 0.0")->all()as $r) $time += $r->row["hours_on_".$day_of_week];
      Staff::$hours_per_day_cache[$day_of_week] = $time;
    }
    return $time;
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
          if(count($methods)) $permissions[strtolower(basename($name,"Controller"))] = array('options'=>$methods, 'class'=>$name,'name'=>(($controller->name)?$controller->name : basename($name,"Controller")));
        }
      }
    }
    ksort($permissions);
    Staff::$permission_cache[$this->primval] = $permissions;
    return $permissions;
  }

  public function work_by_date($start, $end){
    $work = array();
    if($cache = Staff::$work_cache[$this->primval][$start.$end]) return $cache;
    elseif(($worked = $this->work) && ($worked = $worked->between($start,$end)->all())){
      foreach($worked as $row){
        $work[$row->primval]['title'] = $row->title;
        $d = $_start = date("Ymd", strtotime($row->date_start));
        $_end = date("Ymd", strtotime($row->date_end));
        while($d <= $_end){
          $index = date("Y-m-d", strtotime($d));
          $work[$row->primval]['hours'][$index] = ($row->hours_used) ? $row->hours_used : $row->hours;
          $d = date("Ymd", strtotime($d+1));
        }
      }
    }
    Staff::$work_cache[$this->primval][$start.$end] = $work;
    return $work;
  }

  public function usage($start, $end){
    if($cache = Staff::$work_cache['totals'][$start.$end]) return $cache;
    $work = $this->work_by_date($start, $end);
    $total_work = 0;
    foreach($work as $day) foreach($day['hours'] as $t) $total_work+= $t;
    return $total_work;
  }

}
?>