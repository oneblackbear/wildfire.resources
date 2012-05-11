<?
class CronController extends WaxController{

  public function controller_global(){
    if(ENV == "production" && Request::param("knock_knock") != "who is there") $this->redirect_to("/?naughty=1");
  }

  public function department_weekly_summary(){
    $tokens = $this->get_group_tokens();
    $model = new Department("live");

    //work out start and end date of this current week
    $dow = date("N");
    $diff = 1-$dow;
    //1 is monday, 5 is friday (-7 for a week old)
    $date_start = date("Y-m-d", strtotime(1-$dow-7 ." day"));
    $date_end = date("Y-m-d", strtotime(5-$dow-7 ." day"));
    foreach($tokens as $token){
      $percentages = $emails = $depts = array();
      echo "Work for $date_start - $date_end ($token)<br>\r\n";
      $departments = $model->filter("group_token", $token)->all();
      foreach($departments as $dept){
        $depts[$dept->primval] = $dept;
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
        $percentages[$dept->primval] = (100/$available) * $worked;
        echo "&nbsp;".$percentage[$dept->primval]."<br>\r\n";
      }
      $notify = new ResourceNotify;
      $notify->send_weekly_departmental_hours($depts, $percentages, $date_start, $date_end);
    }

  }

  //find all the top level groups for use elsewhere
  protected function get_group_tokens(){
    $model = new Organisation;
    $tokens = array();
    foreach($model->filter("is_client", 0)->filter("LENGTH(`group_token`) >0")->group("group_token")->all() as $org) $tokens[] = $org->group_token;
    return $tokens;
  }

}
?>