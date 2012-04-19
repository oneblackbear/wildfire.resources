<?

class LoginController extends BaseController{

  public $permissions = array();
  public $model_class = "Staff";
  public $form_name = "login_form";
  public $active = false;
  public $navigation_links = array();

  public function index(){
    //if the user is logged in, redirect to the dash board
    if($this->active_staff) $this->redirect_to("/dash/");
    $this->model = new $this->model_class;
    WaxEvent::run("form.setup", $this);
  }

  public function create(){$this->redirect_to("/");}
  public function edit(){$this->redirect_to("/");}

}
?>