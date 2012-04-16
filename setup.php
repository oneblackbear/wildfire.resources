<?
CMSApplication::register_module("client", array('plugin_name'=>'wildfire.resources', 'assets_for_cms'=>true, "display_name"=>"Clients", "link"=>"/admin/client/"));
CMSApplication::register_module("jobcomment", array("display_name"=>"Comments on jobs", "link"=>"/admin/jobcomment/"));
CMSApplication::register_module("department", array("display_name"=>"Departments", "link"=>"/admin/department/"));
CMSApplication::register_module("fee", array("display_name"=>"Fees", "link"=>"/admin/fee/"));
?>