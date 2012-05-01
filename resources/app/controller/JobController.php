<?
class JobController extends BaseController{
  public $model_class = "Job";
  public $form_name = "job_form";
  public $name = "Jobs";
  public $filter_fields=array(
                          'text' => array('columns'=>array('title'), 'partial'=>'_filters_text', 'fuzzy'=>true),
                          'departments' => array('columns'=>array('department'), 'partial'=>'_filters_select', 'opposite_join_column'=>'jobs')
                        );
  public $navigation_links = array('index', 'create', 'listing');
  public $permissions = array(
                          'create'=>array('owner', 'admin', 'privileged'),
                          'edit'=>array('owner', 'admin', 'privileged'),
                          'listing'=>array('owner', 'admin', 'privileged'),
                          'delete'=>array('owner')
                        );

  protected function _events(){
    parent::_events();
    //filter the visible jobs to your organisation if you are a standard user
    WaxEvent::add("model.filters", function(){
      $controller = WaxEvent::data();
      if($controller->active_staff->standard()){
        $orgs = array(0);
        foreach($controller->active_staff->organisations as $o) $orgs[] = $o->primval;
        $controller->model = $controller->model->filter("organisation_id", $orgs);
      }
    });
  }

  public function _summary(){
    $model = new $this->model_class;
    //standard staff (clients) can only see jobs attached to them
    if(($controller->active_staff) && $controller->active_staff->standard()){
      $orgs = array(0);
      foreach($controller->active_staff->organisations as $o) $orgs[] = $o->primval;
      $controller->model = $controller->model->filter("organisation_id", $orgs);
    }
    $this->all = $model->filter("group_token", $this->active_staff->group_token)->all();
  }

  public function listing(){
    parent::index();
  }
  /**
   * listing of active jobs
   */
  public function index(){
    //fetch the same data as listing, it will just be displayed differently, but no pagination
    $this->this_page = false;
    parent::index();
    $this->use_view = "index";
    //restrict the data to the ongoing versions
    $this->cms_content = $this->cms_content->scope("live")->order("date_go_live ASC")->all();
  }

}
?>