<?
class WorkController extends BaseController{
  public $model_class = "Work";
  public $form_name = "work_form";
  public $name = "Work";
  public $filter_fields=array(
                          'department' => array('columns'=>array('department'), 'partial'=>'_filters_select', 'opposite_join_column'=>'work'),
                          'staff' => array('columns'=>array('staff'), 'partial'=>'_filters_select', 'opposite_join_column'=>'work'),
                          'job' => array('columns'=>array('job'), 'partial'=>'_filters_select', 'opposite_join_column'=>'work')
                        );
  public $navigation_links = array('index', 'listing', 'to_do', 'graphs');
  public $permissions = array(
                          'create'=>array('owner', 'admin', 'staff'),
                          'edit'=>array('owner', 'admin'),
                          'delete'=>array('owner', 'admin'),
                          'listing'=>array('owner'),
                          'index'=>array('owner', 'admin', 'staff'),
                          'details'=>array('owner', 'admin', 'staff'),
                          'to_do'=>array('owner', 'admin', 'staff'),
                          'update'=>array('owner', 'admin', 'staff'),
                          'graphs'=>array('owner', 'admin', 'staff')
                        );


  public function listing(){
    parent::index();
  }


  public function index(){
    //if no filters, default to active member of staffs department
    if(!Request::param('filters') && ($id = $this->active_staff->department_id("first")) ){
      $this->model_filters['department'] = $id;
    }
    //no pagination
    $this->per_page = false;
    parent::index();
    //default to current date
    if((!$month = Request::param("month")) && (!$year = Request::param("year"))){
      $month = date("m");
      $year = date("Y");
    }
    if(!$this->month_events){
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
        $range = $this->calendar->date_range_array($row->date_start, $row->date_end);
        foreach($range as $index=>$bool) $this->month_events[$index][$row->primval] = $row->primval;
      }

      //find all jobs within this time period as well
      $job = new Job("live");
      foreach($job->for_department($this->active_staff->department_id())->all() as $row){
        $this->calendar_content["j".$row->primval] = $row;
        $end = date("Y-m-d", strtotime($row->date_go_live));
        $this->month_events[$end]["j".$row->primval] = $row->primval;
      }
    }
    print_r($this->month_events);
    ksort($this->month_events);
  }

  public function update(){
    WaxEvent::run("model.setup", $this);
    WaxEvent::run("form.save", $this);
  }

  public function to_do(){
    unset($this->filter_fields['department'], $this->filter_fields['job']);
    //add in the days filter
    $this->filter_fields['days'] = array(
                                    'columns'=>array('date_start', 'date_end'),
                                    'partial'=>'_filters_range',
                                    'dates'=>true,
                                    'choices'=> array(
                                                '-1 to +1'=>array('min'=>'yesterday', 'max'=>'tomorrow'),
                                                '-1 to +4'=>array('min'=>'yesterday', 'max'=>'+4 days'),
                                                '-1 to +7'=>array('min'=>'yesterday', 'max'=>'+7 days'),
                                                '-3 to +3'=>array('min'=>'-3 days', 'max'=>'+3 days'),
                                                '-5 to +5'=>array('min'=>'-5 days', 'max'=>'+5 days'),
                                                '-15 to +15'=>array('min'=>'-15 days', 'max'=>'+15 days')
                                                )
                                    );
    if(!Request::param('filters')){
      $this->model_filters['days'] = '-1 to +1';
      $this->model_filters['staff'] = $this->active_staff->primval;
    }
    WaxEvent::run("model.setup", $this);
    WaxEvent::run("form.save", $this);
    if($this->cms_content) $this->cms_content = $this->cms_content->order("date_start ASC")->all();
  }

  public function adhoc(){
    $this->edit();
  }

  /**
   * all for filtering of data by department but all work merged together
   */
  public function graphs(){
    WaxEvent::run("model.setup", $this);
    //set the filters to just by departmental
    $this->filter_fields = array('department'=>$this->filter_fields['department']);
    //default to current date
    if((!$month = Request::param("month")) && (!$year = Request::param("year"))){
      $month = date("m");
      $year = date("Y");
    }
    $this->calendar = new Calendar($year, $month);
    $this->table = $this->calendar->generate();
  }

}
?>