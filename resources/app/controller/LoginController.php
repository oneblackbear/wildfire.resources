<?

class LoginController extends BaseController{

  public $permissions = false;
  public $model_class = "Staff";
  public $form_name = "login_form";
  public $active = false;
  public $navigation_links = array();

  public function index(){

    //if the user is logged in, redirect to the dash board
    if($this->active_staff){
      $format = "";
      if($this->use_format != "html") $format = ".".$this->use_format;
      if($token = Request::param('token')) $this->redirect_to("/dash/index$format?token=".$token);
      $this->redirect_to("/dash/index$format");
    }
    $this->model = new $this->model_class;
    $this->model->columns['password'][1]['editable']=true;

    WaxEvent::run("form.setup", $this);
    if(($sent = Request::param('staff')) && ($password = $sent['password']) && ($email = $sent['email']) && $this->_staff_login($email, $password, false, false, Request::param("remember")) ) $this->redirect_to("/dash/");

  }

  public function create(){$this->redirect_to("/");}
  public function edit(){$this->redirect_to("/");}

}
?>