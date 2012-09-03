<?
class CronController extends WaxController{

  public function controller_global(){
    if(ENV == "production" && Request::param("knock_knock") != "who_is_there") $this->redirect_to("/?naughty=1");
  }

  public function department_weekly_summary(){
    $tokens = $this->get_group_tokens();
    $model = new Department("live");
    //work out start and end date of this current week
    $dow = date("N");
    $diff = 1-$dow;
    //1 is monday, 5 is friday (-7 for a week old)
    $date_start = date("Y-m-d", strtotime(0-$dow-7 ." day"));
    $date_end = date("Y-m-d", strtotime(6-$dow-7 ." day"));
    foreach($tokens as $token){
      $all_staff = $percentages = $emails = $depts = $emails = array();
      echo "Work for $date_start - $date_end ($token)<br>\r\n";
      $departments = $model->filter("is_client", 0)->filter("group_token", $token)->all();
      foreach($departments as $dept){
        $depts[$dept->primval] = $dept;
        $worked = 0;
        $available = 0;
        echo "Department: $dept->title<br>\r\n";
        foreach($dept->staff as $staff){
          $emails[$staff->email] = $staff->email;
          $hrs = $staff->hours_worked_by_date_and_department($date_start, $date_end, $staff->department_id());
          $allowed = $staff->weekly_hours();
          $all_staff[$staff->primval]['allowed'] = $allowed;
          $all_staff[$staff->primval]['worked'] += $hrs;
          //now find the time logged
          $worked += $hrs;
          $available += $allowed;
          echo "&nbsp;&nbsp;Staff: $staff->title - $hrs / $allowed<br>\r\n";
        }
        $percentages[$dept->primval] = (100/$available) * $worked;
        echo "&nbsp;".$percentages[$dept->primval]."<br>\r\n";
      }
      $notify = new ResourceNotify;
      echo "sending emails to:";
      print_r($emails);
      if(!Request::param('debug')) $notify->send_weekly_departmental_hours($depts, $percentages, $date_start, $date_end, $emails);
    }
    $this->use_view = $this->use_layout = false;
  }

  public function staff_weekly_summary(){
    $tokens = $this->get_group_tokens();
    $model = new Department("live");
    //work out start and end date of this current week
    $dow = date("N");
    $diff = 1-$dow;
    //1 is monday, 5 is friday (-7 for a week old)
    $date_start = date("Y-m-d", strtotime(0-$dow-7 ." day"));
    $date_end = date("Y-m-d", strtotime(6-$dow-7 ." day"));
    foreach($tokens as $token){
      $lookup = new Staff;
      $all_staff = $percentages = $emails = $depts = $emails = array();
      $organisation = new Organisation("live");
      $org_staff = array();
      $organisations = $organisation->filter("group_token", $token)->filter("is_client", 0)->all();
      foreach($organisations as $org) foreach($org->staff as $s) $org_staff[] = $s;
      echo "Work for $date_start - $date_end ($token)<br>\r\n";
      foreach($org_staff as $staff){
        $hrs = $staff->hours_worked_by_date_and_department($date_start, $date_end, false);
        $allowed = $staff->weekly_hours();
        $all_staff[$staff->primval]['allowed'] = $allowed;
        $all_staff[$staff->primval]['worked'] = $hrs;
        //now find the time logged
        echo "&nbsp;&nbsp;Staff: $staff->title - $hrs / $allowed<br>\r\n";
      }
      if(Request::param('debug') != 1){
        //send personal emails showing how much each staff member worked
        foreach($all_staff as $k=>$s){
          $n = new ResourceNotify;
          $n->send_weekly_hours(new Staff($k), $s, $date_start, $date_end);
        }
      }
    }
    $this->use_view = $this->use_layout = false;
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