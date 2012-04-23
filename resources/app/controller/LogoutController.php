<?

class LogoutController extends BaseController{

  public $permissions = array();
  public $model_class = "Staff";
  public $form_name = "login_form";
  public $active = false;
  public $navigation_links = array();

  public function index(){

  }

  public function create(){$this->redirect_to("/");}
  public function edit(){$this->redirect_to("/");}

}
?>