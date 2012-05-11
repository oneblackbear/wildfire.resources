<?
class Staff extends WildfireResource{
  public static $days_of_week = array('monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday');
  public static $roles = array('standard'=>'client', 'staff'=>'staff', 'admin'=>'admin', 'owner'=>'owner');
  public static $permission_cache = array();
  public static $hours_per_day_cache = array();
  public static $work_cache = array();

  public function setup(){
    parent::setup();
    $this->columns['saved_colour'][1]['editable'] = true;
    $this->define("organisations", "ManyToManyField", array('target_model'=>"Organisation", 'group'=>'relationships','scaffold'=>true));
    $this->define("departments", "ManyToManyField", array('target_model'=>"Department", 'group'=>'relationships', 'scaffold'=>true));
    $this->define("work", "HasManyField", array('target_model'=>"Work", 'group'=>'relationships', 'editable'=>false));
    foreach(self::$days_of_week as $day) $this->define("hours_on_".$day, "FloatField", array('maxlength'=>"5,2", 'default'=>0, 'group'=>'hours and rates', 'hours_col'=>true));
    $this->define("rate", "FloatField", array('maxlength'=>"5,2", 'default'=>0, 'group'=>'hours and rates'));
    $this->define("telephone", "CharField", array('scaffold'=>true, 'export'=>true));
    $this->define("email", "CharField", array('required'=>true,'scaffold'=>true, 'export'=>true, 'unique'=>true));
    $this->define("original_email", "CharField", array('editable'=>false));
    $this->define("password", "PasswordField", array('label'=>'Enter your password', 'group'=>'password', 'editable'=>false));
    $this->define("role", "CharField", array('widget'=>'SelectInput', 'choices'=>self::get_roles()));
    $this->define("date_active", "DateTimeField", array('editable'=>false));
    $this->define("invited", "BooleanField", array('editable'=>false, 'default'=>0));
    $this->define("password_token", "CharField", array('editable'=>false));
    $this->define("api_tokens", "HasManyField", array('target_model'=>'AccessToken', 'editable'=>false,"eager_loading"=>true));
    $this->columns['send_notification'][1]['editable'] = true;
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

  /**
   * total number of hours this person works in a week
   */
  public function weekly_hours(){
    $hrs = 0;
    foreach($this->columns as $col=>$info) if($info[1]['hours_col']) $hrs += $this->$col;
    return $hrs;
  }
  /**
   * hours available on a day
   */
  public static function hours_available($day_of_week="monday", $token, $filters=array()){
    $model = new Staff;
    if($id = $filters['staff']) $model = $model->filter("id", $id);
    else if($filters['department'] && ($dep = new Department($filters['department'])) ){
      $ids = array(0);
      foreach($dep->staff as $s) $ids[] = $s->primval;
      $model = $model->filter("id", $ids);
    }
    $day_of_week = strtolower($day_of_week);
    $time = 0;
    if($time = Staff::$hours_per_day_cache[$day_of_week]) return $time;
    else{
      foreach($model->filter("group_token", $token)->filter("`hours_on_".$day_of_week."` > 0.0")->all()as $r) $time += $r->row["hours_on_".$day_of_week];
      Staff::$hours_per_day_cache[$day_of_week] = $time;
    }
    return $time;
  }

  public function before_insert(){
    parent::before_insert();
    $this->original_email = $this->email;
    if($this->password) $this->password = $this->hash("password", self::$salt);
  }
  public function before_save(){
    parent::before_save();
    if($this->primval) $this->api_access();
  }
  public function notifications(){
    if($this->send_notification && !$this->password && !$this->invited && ($depts = $this->departments) && ($orgs = $this->organisations)){
      $this->password_token = $this->token();
      $notify = new ResourceNotify;
      $notify->send_staff_invite($this);
      //set invite so dont get lots of these
      $this->update_attributes(array('invited'=>1));
    }
  }
  public function api_access(){
    if(($api = $this->api_tokens) && !$api->count()) $this->api_tokens = AccessToken::generate($this);
  }
  public function token(){
    return $this->hash("email", time());
  }
  public function hash($col=false, $salt, $value=false){
    if($col) return hash_hmac("sha1", $this->$col, $salt);
    else if($value) return hash_hmac("sha1", $value, $salt);
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


  public function work_by_date_and_department($start, $end, $departments=array(0), $col="hours"){
    $work = array();
    if($cache = Staff::$work_cache[$this->primval][$start.$end]) return $cache;
    elseif(($worked = $this->work) && ($worked = $worked->filter("department_id", $departments)->between(date("Y-m-d", strtotime($start)),date("Y-m-d", strtotime($end)))->all())){
      $cal = new Calendar;
      foreach($worked as $row){
        $work[$row->primval]['title'] = $row->title;
        $range = array_intersect(array_keys($cal->date_range_array($start, $end)), array_keys($cal->date_range_array($row->date_start, $row->date_end)) );
        foreach($range as $index){
          $work[$row->primval]['hours'][$index] = $row->$col;
          $d = date("Ymd", strtotime($d+1));
        }
      }
    }
    Staff::$work_cache[$this->primval][$start.$end] = $work;
    return $work;
  }

  public function work_by_date($start, $end){
    return $this->work_by_date_and_department($start,$end, $this->department_id());
  }

  public function hours_worked_by_date_and_department($start, $end, $department){
    $time = 0 ;
    $hrs = $this->work_by_date_and_department($start, $end, $department, "hours_used");
    foreach($hrs as $job_id => $details) $time += array_shift($details['hours']);
    return $time;
  }
  /**
   * persons work usage
   */
  public function usage($start, $end){
    if($cache = Staff::$work_cache['totals'][$start.$end]) return $cache;
    $work = $this->work_by_date($start, $end);
    $total_work = 0;
    foreach($work as $day) foreach($day['hours'] as $t) $total_work+= $t;
    return $total_work;
  }

  /**
   * permission helpers
   */
  public function owner($exact=false){
    return ($this->role == "owner");
  }
  public function admin($exact=false){
    return ($this->role == "admin" || $this->role == "owner");
  }
  public function privileged($exact=true){
    if($exact) return ($this->role == "staff");
    else return ($this->admin() || $this->role == "staff");
  }
  public function standard($exact=true){
    if($exact) return ($this->role == "standard");
    else return ($this->admin() || $this->privileged(true) || $this->role == "standard");
  }

  /**
   * find staff deparment ids
   */
  public function department($func="all"){
    if(($depts = $this->departments) && ($res = $depts->$func()) ) return $res;
    else return false;
  }
  public function department_id($func="all"){
    $ids = array(0);
    if($dept= $this->department($func)){
      if($dept instanceOf WaxRecordset) foreach($dept as $d) $ids[] = $d->primval;
      else $ids = $dept->primval;
    }
    return $ids;
  }

  public function search_details(){
    return $this->title . " <span>$this->telephone</span><span>$this->email</span>";
  }
}
?>