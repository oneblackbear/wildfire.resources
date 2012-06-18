<?
class WorkController extends BaseController{
  public $model_class = "Work";
  public $form_name = "work_form";
  public $name = "Work";
  public $filter_fields=array(
                          'department' => array('columns'=>array('department'), 'partial'=>'_filters_select', 'opposite_join_column'=>'work'),
                          'staff' => array('columns'=>array('staff'), 'partial'=>'_filters_select', 'opposite_join_column'=>'work'),
                          'job' => array('columns'=>array('job'), 'partial'=>'_filters_select', 'opposite_join_column'=>'work'),
                          'client' => array('columns'=>array('client'), 'partial'=>'_filters_select', 'opposite_join_column'=>'work'),
                          'fee' => array('columns'=>array('fee'), 'partial'=>'_filters_select', 'opposite_join_column'=>'work')
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
  public static $searchable = false;

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
    $this->calendar = new Calendar($year, $month);
    $this->table = $this->calendar->generate();
    $this->use_view = "index";
    if(!count($this->month_events)){
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
      $job = new Job;
      foreach($job->for_department($this->active_staff->department_id())->all() as $row){
        $this->calendar_content["j".$row->primval] = $row;
        $end = date("Y-m-d", strtotime($row->date_go_live));
        $this->month_events[$end]["j".$row->primval] = $row->primval;
      }
    }
    ksort($this->month_events);
  }

  public function update(){
    WaxEvent::run("model.setup", $this);
    WaxEvent::run("form.save", $this);
  }

  public function to_do(){
    unset($this->filter_fields['department'], $this->filter_fields['job'], $this->filter_fields['client']);
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
    WaxEvent::add("form.save.after", function(){
      $controller = WaxEvent::data();
      if($save = $controller->model_saved->update_attributes(array('adhoc'=>1))) $controller->model_saved = $controler->model = $save;
    });
    $this->edit();
  }

  /**
   * all for filtering of data by department but all work merged together
   */
  public function graphs(){
    $this->model_class = "Department";
    //set the filters to just be departmental
    $this->filter_fields = array();
    //if no filters, default to active member of staffs department
    if(!Request::param('filters') && ($id = $this->active_staff->department_id("first")) ) $this->model_filters['department'] = $id;
    //default to current date
    if((!$month = Request::param("month")) && (!$year = Request::param("year"))){
      $month = date("m");
      $year = date("Y");
    }
    WaxEvent::run("model.setup", $this);
    $this->calendar = new Calendar($year, $month);
    $this->table = $this->calendar->generate();
  }


  public function weekly_summary(){
    $tokens = array($this->active_staff->group_token);
    $model = new Department("live");

    //work out start and end date of this current week
    $dow = date("N");
    $diff = 1-$dow;
    //1 is monday, 5 is friday (-7 for a week old)
    $date_start = date("Y-m-d", strtotime(0-$dow-7 ." day"));
    $date_end = date("Y-m-d", strtotime(6-$dow-7 ." day"));
    foreach($tokens as $token){
      $percentages = $emails = $depts = $emails = array();
      echo "Work for $date_start - $date_end ($token)<br>\r\n";
      $departments = $model->filter("group_token", $token)->all();
      foreach($departments as $dept){
        $depts[$dept->primval] = $dept;
        $worked = 0;
        $available = 0;
        echo "Department: $dept->title<br>\r\n";
        foreach($dept->staff as $staff){
          $emails[$staff->email] = $staff->email;
          $hrs = $staff->hours_worked_by_date_and_department($date_start, $date_end, array($dept->primval));
          $allowed = $staff->weekly_hours();
          //now find the time logged
          $worked += $hrs;
          $available += $allowed;
          echo "&nbsp;&nbsp;Staff: $staff->title - $hrs / $allowed<br>\r\n";
        }
        $percentages[$dept->primval] = (100/$available) * $worked;
        echo "&nbsp;".$percentages[$dept->primval]."<br>\r\n";
      }

    }
  }

}
?>