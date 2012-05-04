<?
class ResourceNotify extends WaxEmail{

  public function staff_invite($staff){
    $this->to = $staff->email;
    $this->staff = $staff;
    $this->subject = "You have been invited to join ".Config::get("site/name");
    $this->from = Config::get("site/email");
    $this->from_name = Config::get("site/email_name");
  }

  public function job_creation($job, $staff){
    $this->to = $staff->email;
    $this->staff = $staff;
    $this->job = $job;
    $this->client = $job->client;
    $this->subject = "A new job has been created [".$this->job->title." - ".$this->client->title." @ ".$this->job->deadline()."]";
    $this->from = Config::get("site/email");
    $this->from_name = Config::get("site/email_name");
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

}
?>