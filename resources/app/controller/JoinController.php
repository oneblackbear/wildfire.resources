<?
/**
 * the main creation controller, this creates the first user as the owner,
 * redirects to the organisation controllers create function
 *
 */
class JoinController extends BaseController{

  public $permissions = array();
  public $model_class = "Staff";
  public $form_name = "join_form";
  public $active = false;
  public $navigation_links = array();

  public function index(){
    //if the user is logged in, redirect to the dash board
    if($this->active_staff) $this->redirect_to("/dash/");

    //replace the form setup to fake the user role
    WaxEvent::clear("form.setup");
    WaxEvent::add("form.setup", function(){
      $controller = WaxEvent::data();
      $controller->model->columns['role'][1]['widget'] = "HiddenInput";
      $controller->model->columns['password'][1]['editable'] = true;
      $controller->model->role = "owner";
      $controller->{$controller->form_name} = new WaxForm($controller->model);
    });

    //hook in the to the after save to do a redirect to the organisation form
    WaxEvent::add("form.save.after", function(){
      $controller = WaxEvent::data();
      //log the person in
      $controller->active_staff = $controller->_staff_login($controller->model_saved->email, $controller->model_saved->password, true);
      $controller->model_saved->update_attributes(array('created_by'=>$controller->active_staff->primval));
      if(!$controller->use_format || in_array($controller->use_format, $controller->redirect_formats)) $controller->redirect_to("/organisation/setup/");
    });

    WaxEvent::run("model.setup", $this);
    WaxEvent::run("form.save", $this);
  }

  protected function _events(){
    parent::_events();
  }


  public function invited(){
    $model = new $this->model_class;
    if($this->model = $model->filter("password_token", Request::param("token"))->first()){
      $this->model->columns['password'][1]['editable'] = true;
      $this->{$this->form_name} = new WaxForm($this->model);
      if($password = $_REQUEST['staff']['password']){
        $saved = $this->model->update_attributes(array('password_token'=> $this->model->token(), 'password'=>$this->model->hash(false, Staff::$salt, $password) ) );
        $this->active_staff = $this->_staff_login($saved->email, $saved->password, true);
        $this->redirect_to("/dash/");
      }
    }
  }
  public function create(){$this->redirect_to("/");}
  public function edit(){$this->redirect_to("/");}

}
?>