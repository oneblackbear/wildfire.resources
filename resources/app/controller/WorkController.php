<?
class WorkController extends BaseController{
  public $model_class = "Work";
  public $form_name = "work_form";
  public $name = "Work";
  public $filter_fields=array(
                          'department' => array('columns'=>array('department'), 'partial'=>'_filters_select', 'opposite_join_column'=>'work'),
                          'staff' => array('columns'=>array('staff'), 'partial'=>'_filters_select', 'opposite_join_column'=>'work')
                        );
  public $navigation_links = array('index', 'create', 'listing');
  public $permissions = array(
                          'create'=>array('owner', 'admin'),
                          'edit'=>array('owner', 'admin'),
                          'delete'=>array('owner', 'admin'),
                          'listing'=>array('owner'),
                          'index'=>array('owner', 'admin', 'privileged'),
                          'details'=>array('owner', 'admin', 'privileged')
                        );



  public function listing(){
    parent::index();
  }

  //like the form, but non-editable
  public function details(){
    WaxEvent::run("model.setup", $this);
  }

  public function index(){
    //if no filters, default to active member of staffs department
    if(!Request::param('filters') && ($depts = $this->active_staff->departments) && ($dept = $depts->first()) ){
      $this->model_filters['departments'] = $dept->primval;
    }
    //no pagination
    $this->per_page = false;
    parent::index();
    //default to current date
    if((!$month = Request::param("month")) && (!$year = Request::param("year"))){
      $month = date("m");
      $year = date("Y");
    }
    $this->use_view = "index";
    $this->calendar = new Calendar($year, $month);
    $this->table = $this->calendar->generate();
    $this->months_events = array();

    //generate month_events list
    $this->calendar_content = array();
    $model = $this->calendar->range_filter($this->model, $year, $month);
    $this->month_events = array();
    foreach($model->all() as $row){
      $this->calendar_content[$row->primval] = $row;
      $start = date("Ymd", strtotime($row->date_start));
      $end = date("Ymd", strtotime($row->date_end));
      for($i=$start; $i<=$end; $i++){
        $index = date("Y-m-d", strtotime($i));
        $this->month_events[$index][$row->primval] = $row->primval;
      }
    }
  }
}
?>