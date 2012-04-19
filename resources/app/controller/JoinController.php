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

  public function index(){
    //if the user is logged in, redirect to the dash board
    if($this->active_staff) $this->redirect_to("/dash/");

    //replace the form setup to fake the user role
    WaxEvent::clear("form.setup");
    WaxEvent::add("form.setup", function(){
      $controller = WaxEvent::data();
      $controller->model->columns['role'][1]['widget'] = "HiddenInput";
      $controller->model->role = "owner";
      $controller->{$controller->form_name} = new WaxForm($controller->model);
    });

    //hook in the to the after save to do a redirect to the organisation form
    WaxEvent::add("form.save.after", function(){
      $controller = WaxEvent::data();
      //log the person in
      $controller->active_staff = $controller->staff_login($controller->model_saved->email, $controller->model_saved->password, true);
      $controller->model_saved->update_attributes(array('created_by'=>$controller->active_staff));
      if(!$controller->use_format || in_array($controller->use_format, $controller->redirect_formats)) $controller->redirect_to("/organisation/setup/");
    });

    WaxEvent::run("model.setup", $this);
    WaxEvent::run("form.save", $this);
  }

  protected function _events(){
    parent::_events();
  }

  public function create(){$this->redirect_to("/");}
  public function edit(){$this->redirect_to("/");}

}
?>