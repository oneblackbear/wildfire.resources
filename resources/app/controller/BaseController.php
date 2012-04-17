<?
class BaseController extends WaxController{

  public $model_class = false;
  public $model_scope = "live";
  public $staff = false;
  public $per_page = 15;
  public $form_name = "model_form";
  public $permissions = array(
                          'create'=>array('owner', 'admin'),
                          'edit'=>array('owner', 'admin'),
                          'delete'=>array('owner'),
                          'archive'=>array('owner', 'admin')
                        );

  public function controller_global(){
    parent::controller_global();
    $this->_events();
    $this->_access();
  }

  public function base_url(){
    return "/home/";
  }
  //the url of the controller
  public function url(){
    return "/".str_replace("controller", "", strtolower(get_class($this))) ."/";
  }
  protected function _access(){
    WaxEvent::run("user.access", $this);
  }
  protected function _events(){
    /**
     * hook in to the base template view events to remove other plugin views :|
     */
    WaxEvent::add("wax.after_plugin_view_paths", function(){
      $view = WaxEvent::data();
      foreach($view->template_paths as $i=>$path) if(strpos($path, "/plugins/") && !strpos($path, "/plugins/wildfire.resources/")) unset($view->template_paths[$i]);
    });
    /**
     * permissions work on blacklist style
     * - an action in the permissions array is restricted to the user roles listed in its array
     * - if its not set in the permissions array then its publicly available
     */
    WaxEvent::add("user.access", function(){
      $controller = WaxEvent::data();
      if($roles = $controller->permissions[$controller->action]){
        if(!$controller->staff || !in_array($controller->staff->role, $roles)) $controller->redirect_to($controller->base_url()."?no-access");
      }
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
              $ids = array();
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
      else{
        $controller->model = new $controller->model_class($controller->model_scope);
        if($controller->model_filters || Request::param('filters')) WaxEvent::run("model.filters", $controller);
        WaxEvent::run("model.pagination.setup", $controller);
        WaxEvent::run("model.fetch", $controller);
      }
      WaxEvent::run("form.setup", $controller);
    });
    /**
     * handle saving a model
     */
    /**
     * creating the form
     */
    WaxEvent::add("form.setup", function(){
      $controller = WaxEvent::data();
      $controller->{$controller->form_name} = new WaxForm($controller->model);
    });
  }

  public function index(){}

  public function create(){
    WaxEvent::run("model.setup", $this);
  }

  public function edit(){
    WaxEvent::run("model.setup", $this);
    WaxEvent::run("model.save", $this);
  }

}
?>