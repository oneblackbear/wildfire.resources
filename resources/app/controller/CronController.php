<?
class CronController extends WaxController{



  public function department_weekly_summary(){
    $tokens = $this->get_group_tokens();
    $model = new Department("live");
    $percentage = array();
    $emails = array();

    //work out start and end date of this current week
    $dow = date("N");
    $diff = 1-$dow;
    $week = date("W");
    //1 is monday, 5 is friday
    $date_start = date("Y-m-d", strtotime(1-$dow ." day"));
    $date_end = date("Y-m-d", strtotime(5-$dow ." day"));
    echo "Work for $date_start - $date_end<br>\r\n";
    $departments = $model->filter("group_token", $tokens)->all();
    foreach($departments as $dept){
      $emails[$dept->primval] = $dept;
      $worked = 0;
      $available = 0;
      echo "Department: $dept->title<br>\r\n";
      foreach($dept->staff as $staff){
        $hrs = $staff->hours_worked_by_date_and_department($date_start, $date_end, array($dept->primval));
        $allowed = $staff->weekly_hours();
        //now find the time logged
        $worked += $hrs;
        $available += $allowed;
        echo "&nbsp;&nbsp;Staff: $staff->title - $hrs / $allowed<br>\r\n";
      }
      $percentage[$dept->primval] = (100/$available) * $worked;
      echo "&nbsp;".$percentage[$dept->primval]."<br>\r\n";
    }
    exit;
  }

  //find all the top level groups for use elsewhere
  protected function get_group_tokens(){
    $model = new Organisation;
    $tokens = array("0");
    foreach($model->filter("is_client", 0)->group("group_token")->all() as $org) $tokens[] = $org->group_token;
    return $tokens;
  }

}
?>