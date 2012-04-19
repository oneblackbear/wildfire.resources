<?
class DashController extends BaseController{
  public $name = "Home";
  public $permissions = array(
                          'index'=>array('standard'=>'standard', 'privileged'=>'privileged', 'admin'=>'admin', 'owner'=>'owner')
                        );

  public function index(){

  }
}
?>