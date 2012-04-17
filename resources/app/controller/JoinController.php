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

  public function index(){
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
      print_r($controller->active_staff);
      exit;
      $controller->redirect_to($controller->url()."organisation/");
    });

    WaxEvent::run("model.setup", $this);
    WaxEvent::run("form.save", $this);
  }

  protected function _events(){
    parent::_events();

  }

  public function organisation(){
    $this->model_class = "Organisation";
    $this->form_name = "organisation_form";
    WaxEvent::run("model.setup", $this);
    WaxEvent::run("form.save", $this);
  }

  public function create(){$this->redirect_to("/");}
  public function edit(){$this->redirect_to("/");}

}
?>