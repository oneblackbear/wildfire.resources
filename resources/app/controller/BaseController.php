<?
class BaseController extends WaxController{
  public $name = false;
  public $user_session_name = "staff";
  public $model_class = false;
  public $model_scope = "live";
  public $active_staff = false;
  public $per_page = 20;
  public $form_name = "model_form";
  public $model_saved = false;
  public $content_object_stack = array();
  public $redirect_formats = array("html");
  public $structure = array();
  public $navigation_links = array('index', 'create', 'listing');
  public $scaffold_columns = array();
  public $filter_fields=array(
                          'text' => array('columns'=>array('title'), 'partial'=>'_filters_text', 'fuzzy'=>true)
                        );
  public $permissions = array(
                          'create'=>array('owner', 'admin'),
                          'edit'=>array('owner', 'admin'),
                          'delete'=>array('owner')
                        );

  public function controller_global(){
    parent::controller_global();
    $this->_events();
    $this->_access();
    $this->cms_stacks();
  }

  protected function base_url(){
    return "/home/";
  }
  //the url of the controller
  protected function url(){
    return "/".str_replace("controller", "", strtolower(get_class($this))) ."/";
  }
  protected function cms_stacks(){
    $controller_class = new stdClass;
    $controller_class->title = ucwords(str_replace("controller", "", strtolower(get_class($this))));
    $this->content_object_stack[] = $controller_class;
    if($this->action != "index"){
      $action_class = new stdClass;
      $action_class->title = Inflections::humanize($this->action);
      $this->content_object_stack[] = $action_class;
    }
  }
  protected function _access(){
    $this->active_staff = $this->_staff_login();
    if($this->active_staff) $this->active_staff->update_attributes(array('date_active'=>date("Y-m-d H:i:s")));
    WaxEvent::run("user.access", $this);
  }
  protected function _events(){
    /**
     * hook in to the base template view events to remove other plugin views :|
     */
    WaxEvent::add("wax.after_plugin_view_paths", function(){
      $view = WaxEvent::data();
      foreach($view->template_paths as $i=>$path) if(strpos($path, "/plugins/") && !strpos($path, "/plugins/wildfire.")) unset($view->template_paths[$i]);
    });
    /**
     * permissions work on blacklist style
     * - an action in the permissions array is restricted to the user roles listed in its array
     * - if its not set in the permissions array then its publicly available
     * - find the users access list
     */
    WaxEvent::add("user.access", function(){
      $controller = WaxEvent::data();
      if($roles = $controller->permissions[$controller->action]){
        if(!$controller->active_staff || !in_array($controller->active_staff->role, $roles)) $controller->redirect_to("/dash/?no-access");
      }
      if($controller->active_staff) $controller->structure = $controller->active_staff->permissions();
    });
    /**
     * filter code nicked from cms controller
     */
    WaxEvent::add("model.filters", function(){
      $obj = WaxEvent::data();
      if(!$obj->model_filters) $obj->model_filters = Request::param('filters');
      $filterstring = "";
      foreach((array)$obj->model_filters as $name=>$value){
        $col_filter = "";
        if(strlen($value) && $filter = $obj->filter_fields[$name]){
          foreach($filter['columns'] as $col){
            if($opp = $filter['opposite_join_column']){
              $target = $obj->model->columns[$col][1]['target_model'];
              $join = new $target($value);
              $ids = array(0);
              foreach($join->$opp as $opposite) $ids[] = $opposite->primval;
              $col_filter .= "(`".$obj->model->primary_key."` IN(".implode(",",$ids).")) OR";
            }
            elseif($filter['fuzzy']) $col_filter .= "`$col` LIKE '%".($value)."%' OR";
            elseif($filter['fuzzy_right']) $col_filter .= "`$col` LIKE '".($value)."%' OR";
            elseif($filter['fuzzy_left']) $col_filter .= "`$col` LIKE '%".($value)."' OR";
            else $col_filter .= "`$col`='".($value)."' OR";
          }
          $filterstring .= "(".trim($col_filter, " OR").") AND ";
        }
      }

      if($filterstring) $obj->model->filter(trim($filterstring, " AND "));
    });
    WaxEvent::add("model.columns", function(){
      $obj = WaxEvent::data();
      $model = new $obj->model_class;
      foreach($model->columns as $col=>$info) if($info[1]['scaffold']) $obj->scaffold_columns[$col] = true;
    });
    /**
     * setup the pagination on the model
     */
    WaxEvent::add("model.pagination.setup", function(){
      $obj = WaxEvent::data();
      if(!Request::param('view_all')){
        if(!$obj->this_page = Request::param('page')) $obj->this_page = 1;
        if($per_page = Request::param('per_page')) $obj->per_page = $per_page;
      }else $obj->this_page = $obj->per_page = false;
    });
    WaxEvent::add("model.fetch", function(){
      $obj = WaxEvent::data();

      if($obj->this_page && $obj->per_page) $obj->cms_content = $obj->model->page($obj->this_page, $obj->per_page);
      else $obj->cms_content = $obj->model->all();

    });

    /**
     * event to handle model setups
     * - first looks for a valid id in the url (for /controller/edit/x/)
     * - then looks for filters applied for this model (like a listing)
     */
    WaxEvent::add('model.setup', function(){
      $controller = WaxEvent::data();
      if($id = Request::get("id")) $controller->model = new $controller->model_class($id);
      elseif(!$controller->model){
        $controller->model = new $controller->model_class($controller->model_scope);
        if($controller->model_filters || Request::param('filters')) WaxEvent::run("model.filters", $controller);
        WaxEvent::run("model.pagination.setup", $controller);
        WaxEvent::run("model.fetch", $controller);
      }
      WaxEvent::run("model.columns", $controller);
      WaxEvent::run("form.setup", $controller);
    });
    /**
     * handle saving a model
     */
    WaxEvent::add("form.save", function(){
      $controller = WaxEvent::data();
      if($form = $controller->{$controller->form_name}){
        //run the save
        if($model_saved = $form->save()){
          $controller->model_saved = $model_saved;
          WaxEvent::run("form.save.after", $controller);
        }
      }
    });
    //after save hook
    WaxEvent::add("form.save.after", function(){
      $controller = WaxEvent::data();
      $controller->model = $controller->model_saved;
      WaxEvent::run("form.save.joins", $controller);
    });
    WaxEvent::add("form.save.joins", function(){
      $controller = WaxEvent::data();
      $table = $controller->model_saved->table;
      $joins = Request::param("joins");
      foreach((array) $joins[$table] as $col=>$primary_keys){
        $class = $controller->model_saved->columns[$col][1]['target_model'];
        if(!is_array($primary_keys)) $primary_keys = array($primary_keys);
        $join = new $class;
        $controller->model_saved->$col = $join->filter($join->primary_key, $primary_keys)->all();
      }
    });
    /**
     * creating the form
     */
    WaxEvent::add("form.setup", function(){
      $controller = WaxEvent::data();
      if($controller->model->columns['group_token']) $controller->model->group_token = $controller->active_staff->group_token;
      if(!$controller->{$controller->form_name}) $controller->{$controller->form_name} = new WaxForm($controller->model);
    });
  }

  public function index(){
    WaxEvent::run("model.setup", $this);
  }

  public function create(){
    WaxEvent::run("model.setup", $this);
    WaxEvent::run("form.save", $this);
  }

  public function edit(){
    WaxEvent::run("model.setup", $this);
    WaxEvent::run("form.save", $this);
  }

  public function _summary(){
    $model = new $this->model_class;
    $this->all = $model->filter("group_token", $this->active_staff->group_token)->all();
  }

  public function _staff_login($email=false, $password=false, $hashed = false){
    $user_model = new Staff;
    if($email && $password && $hashed && ($found = $user_model->clear()->filter("email", $email)->filter("password", $password)->first()) ){
      Session::set($this->user_session_name, md5($email));
      Session::set("LOGGED_IN_ROLE", $found->role);
      return $found;
    }elseif($email && $password && ($found = $user_model->clear()->filter("email", $email)->filter("password", hash_hmac("sha1", $password, Staff::$salt))->first()) ){
      Session::set($this->user_session_name, md5($email));
      Session::set("LOGGED_IN_ROLE", $found->role);
      return $found;
    }elseif(($sess = Session::get($this->user_session_name)) && ($found = $user_model->clear()->filter("md5(`email`)", $sess)->first())){
      Session::set("LOGGED_IN_ROLE", $found->role);
      return $found;
    }
    return false;
  }

}
?>