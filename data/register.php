<?php
// load register template
$theTemplate = new Template ( "register.tpl" );

if ($_POST['register_send'] == 1) {
	if ( !checkFormData($_POST['new_real_name'], "text", TRUE, "{3,255}")
		 || !checkFormData($_POST['new_mail'], "email", TRUE)
		 || !checkFormData($_POST['new_pass'], "text", TRUE, "{6,100}")
		 || !checkFormData($_POST['wdh_pass'], "text", TRUE, "{6,100}")) {
		$theTemplate->load ("ERROR_DATA");
		$error = (string) $theTemplate;
	} elseif (/*user*/FALSE) {
		$theTemplate->load ("ERROR_USER");
		$error = (string) $theTemplate;		
	} elseif ($_POST['new_pass'] !== $_POST['wdh_pass']) {
		$theTemplate->load ("ERROR_PASS");
		$error = (string) $theTemplate;		
	} else {
		$error = "";
		
	}
	

} else {
	$error = "";
}

$theTemplate->load ("NEW_USER");
$theTemplate->tag ("error", $error);
	
// Template ausgeben
echo $theTemplate;

function user_exists ($USERNAME) {
	
}

?>
