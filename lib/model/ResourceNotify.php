<?
class ResourceNotify extends WaxEmail{

  public function staff_invite($staff){
    $this->to = $staff->email;
    $this->staff = $staff;
    $this->subject = "You have been invited to join ".Config::get("site/name");
    $this->from = Config::get("site/email");
    $this->from_name = Config::get("site/email_name");
  }

  public function job_creation($job, $staff, $emails){
    $this->to = $staff->email;
    $this->staff = $staff;
    $this->job = $job;
    $this->client = $job->client;
    $this->subject = "A new job has been created [".$this->job->title." - ".$this->client->title." @ ".$this->job->deadline()."]";
    $this->from = Config::get("site/email");
    $this->from_name = Config::get("site/email_name");
    $this->emails = $emails;
  }
  public function job_updated($job, $staff, $emails){
    $this->to = $staff->email;
    $this->staff = $staff;
    $this->job = $job;
    $this->client = $job->client;
    $this->subject = "A job has been updated [".$this->job->title." - ".$this->client->title." @ ".$this->job->deadline()."]";
    $this->from = Config::get("site/email");
    $this->from_name = Config::get("site/email_name");
    $this->emails = $emails;
  }


  public function work_scheduled($work, $job, $staff, $emails){
    $this->to = $staff->email;
    $this->staff = $staff;
    $this->job = $job;
    $this->work = $work;
    $this->assigned = $work->staff;
    $this->dept = $work->department;
    $this->subject = "Work item has been created [".$this->work->title." - ".$this->client->title." @ ".$this->work->date_string()."]";;
    $this->from = Config::get("site/email");
    $this->from_name = Config::get("site/email_name");
    $this->emails = $emails;
  }

  public function work_completed($work, $job, $staff, $emails){
    $this->to = $staff->email;
    $this->staff = $staff;
    $this->job = $job;
    $this->work = $work;
    $this->dept = $work->department;
    $this->assigned = $work->staff;
    $this->subject = "Work has been completed [".$this->work->title." - ".$this->client->title." @ ".$this->work->date_string()."]";;;
    $this->from = Config::get("site/email");
    $this->from_name = Config::get("site/email_name");
    $this->emails = $emails;
  }
  public function work_updated($work, $job, $staff, $emails){
    $this->to = $staff->email;
    $this->staff = $staff;
    $this->job = $job;
    $this->work = $work;
    $this->dept = $work->department;
    $this->assigned = $work->staff;
    $this->subject = "Work has been updated [".$this->work->title." - ".$this->client->title." @ ".$this->work->date_string()."]";;;
    $this->from = Config::get("site/email");
    $this->from_name = Config::get("site/email_name");
    $this->emails = $emails;
  }

  public function weekly_departmental_hours($departments, $hours, $start, $end, $emails){
    foreach($emails as $em) $this->add_to_address($em);
    $this->departments = $departments;
    $this->hours = $hours;
    $this->subject = "Last Weeks Logged time [$start - $end]";
    $this->from = Config::get("site/email");
    $this->from_name = Config::get("site/email_name");
  }

}
?>