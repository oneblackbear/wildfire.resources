<?
CMSApplication::register_module("organisation", array('plugin_name'=>'wildfire.resources', 'assets_for_cms'=>true, "display_name"=>"Organisations", "link"=>"/admin/organisation/"));
CMSApplication::register_module("department", array("display_name"=>"Departments", "link"=>"/admin/department/"));
CMSApplication::register_module("staff", array("display_name"=>"Staff", "link"=>"/admin/staff/", 'split'=>true));

CMSApplication::register_module("client", array("display_name"=>"Clients", "link"=>"/admin/client/"));
CMSApplication::register_module("fee", array("display_name"=>"Fees", "link"=>"/admin/fee/",'split'=>true));

CMSApplication::register_module("job", array("display_name"=>"Jobs", "link"=>"/admin/job/"));
CMSApplication::register_module("work", array("display_name"=>"Schedule", "link"=>"/admin/work/"));
CMSApplication::register_module("jobcomment", array("display_name"=>"Comments on jobs", "link"=>"/admin/jobcomment/", 'split'=>true));
?>