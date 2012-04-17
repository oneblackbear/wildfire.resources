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
      if(!$controller->use_format || in_array($controller->use_format, $controller->redirect_formats)) $controller->redirect_to($controller->url()."organisation/");
    });

    WaxEvent::run("model.setup", $this);
    WaxEvent::run("form.save", $this);
  }

  protected function _events(){
    parent::_events();

  }

  public function organisation(){
    if(!$this->active_staff) $this->redirect_to("/");
    $this->model_class = "Organisation";
    $this->form_name = "organisation_form";
    //after save of the form, join to the user and redirect to department
    WaxEvent::add("form.save.after", function(){
      $controller = WaxEvent::data();
      $controller->model_saved->staff = $controller->active_staff;
      Session::set("organisation", $controller->model_saved);
      if(!$controller->use_format || in_array($controller->use_format, $controller->redirect_formats)) $controller->redirect_to($controller->url()."department/");
    });
    WaxEvent::run("model.setup", $this);
    WaxEvent::run("form.save", $this);
  }

  public function department(){
    if(!$this->active_staff) $this->redirect_to("/");
    $this->organisation = new Organisation(Session::get("organisation"));
    $this->model_class = "Department";
    $this->form_name = "deparment_form";
    WaxEvent::add("form.save.after", function(){
      $controller = WaxEvent::data();
      $controller->model_saved->staff = $controller->active_staff;
      if(!$controller->use_format || in_array($controller->use_format, $controller->redirect_formats)) $controller->redirect_to($controller->url()."staff/");
    });
    WaxEvent::run("model.setup", $this);
    WaxEvent::run("form.save", $this);
  }

  public function staff(){
    if(!$this->active_staff) $this->redirect_to("/");
    $this->model_class = "Staff";
    $this->form_name = "staff_form";
    WaxEvent::clear("form.setup");
    WaxEvent::add("form.setup", function(){
      $controller = WaxEvent::data();
      $controller->model->columns['organisations'][1]['widget'] = "SelectInput";
      $controller->model->columns['organisations'][1]['choices'] = $controller->active_staff->organisations;

      $controller->{$controller->form_name} = new WaxForm($controller->model);
    });

    WaxEvent::add("form.save.after", function(){
      $controller = WaxEvent::data();
      if(!$controller->use_format || in_array($controller->use_format, $controller->redirect_formats)) $controller->redirect_to($controller->url()."dash/");
    });
    WaxEvent::run("model.setup", $this);
    WaxEvent::run("form.save", $this);
  }

  public function create(){$this->redirect_to("/");}
  public function edit(){$this->redirect_to("/");}

}
?>