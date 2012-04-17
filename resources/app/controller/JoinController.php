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
  }

  public function create(){$this->redirect_to("/");}
  public function edit(){$this->redirect_to("/");}

}
?>