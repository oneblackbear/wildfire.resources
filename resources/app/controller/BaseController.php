<?
class BaseController extends WaxController{

  public $model_class = false;
  public $staff = false;
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
    WaxEvent::add('model.setup', function(){
      $controller = WaxEvent::data();

    });
  }

  public function index(){}

  public function create(){
    WaxEvent::run("model.setup", $this);
    WaxEvent::run("model.save", $this);
  }

  public function edit(){
    WaxEvent::run("model.setup", $this);
    WaxEvent::run("model.save", $this);
  }

}
?>