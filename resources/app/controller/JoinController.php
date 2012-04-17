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
    WaxEvent::run("model.setup", $this);
    WaxEvent::run("form.save", $this);
  }
  protected function _events(){
    parent::_events();
    WaxEvent::clear("form.setup");
    WaxEvent::add("form.setup", function(){
      $controller = WaxEvent::data();
      $controller->model->columns['role'][1]['widget'] = "HiddenInput";
      $controller->{$controller->form_name} = new WaxForm($controller->model);
      $controller->{$controller->form_name}->role->value = "owner";
    });
  }

  public function create(){$this->redirect_to("/");}
  public function edit(){$this->redirect_to("/");}

}
?>