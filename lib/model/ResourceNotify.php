<?
class ResourceNotify extends WaxEmail{

  public function staff_invite($staff){
    $this->add_to_address($staff->email);
    $this->staff = $staff;
    $this->subject = "You have been invited to join ".Config::get("site/name");
    $this->from = Config::get("site/email");
    $this->from_name = Config::get("site/email_name");
  }

  public function job_creation($job, $staff){
    $this->add_to_address($staff->email);
    $this->staff = $staff;
    $this->job = $job;
    $this->subject = "A new job has been created";
    $this->from = Config::get("site/email");
    $this->from_name = Config::get("site/email_name");
  }

  public function work_scheduled(){

  }

  public function work_completed(){

  }

}
?>