<#1>
<?php

if( !$ilDB->tableExists('adv_md_values_text') )
{
	$ilDB->renameTable('adv_md_values', 'adv_md_values_text');
}

?>
<#2>
<?php

if( !$ilDB->tableExists('adv_md_values_int') )
{
	$ilDB->createTable('adv_md_values_int', array(
		'obj_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'sub_type' => array(
			'type' => 'text',
			'length' => 10,
			'notnull' => true,
			'default' => "-"
		),
		'sub_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'field_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'value' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => false
		)	
	));
		
	$ilDB->addPrimaryKey('adv_md_values_int', array('obj_id', 'sub_type', 'sub_id'));
}

?>
<#3>
<?php

if( !$ilDB->tableExists('adv_md_values_float') )
{
	$ilDB->createTable('adv_md_values_float', array(
		'obj_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'sub_type' => array(
			'type' => 'text',
			'length' => 10,
			'notnull' => true,
			'default' => "-"
		),
		'sub_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'field_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'value' => array(
			'type' => 'float',			
			'notnull' => false
		)	
	));
		
	$ilDB->addPrimaryKey('adv_md_values_float', array('obj_id', 'sub_type', 'sub_id'));
}

?>
<#4>
<?php

if( !$ilDB->tableExists('adv_md_values_date') )
{
	$ilDB->createTable('adv_md_values_date', array(
		'obj_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'sub_type' => array(
			'type' => 'text',
			'length' => 10,
			'notnull' => true,
			'default' => "-"
		),
		'sub_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'field_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'value' => array(
			'type' => 'date',			
			'notnull' => false
		)	
	));
		
	$ilDB->addPrimaryKey('adv_md_values_date', array('obj_id', 'sub_type', 'sub_id'));
}

?>
<#5>
<?php

if( !$ilDB->tableExists('adv_md_values_datetime') )
{
	$ilDB->createTable('adv_md_values_datetime', array(
		'obj_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'sub_type' => array(
			'type' => 'text',
			'length' => 10,
			'notnull' => true,
			'default' => "-"
		),
		'sub_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'field_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'value' => array(
			'type' => 'timestamp',			
			'notnull' => false
		)	
	));
		
	$ilDB->addPrimaryKey('adv_md_values_datetime', array('obj_id', 'sub_type', 'sub_id'));
}

?>
<#6>
<?php

if( !$ilDB->tableExists('adv_md_values_location') )
{
	$ilDB->createTable('adv_md_values_location', array(
		'obj_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'sub_type' => array(
			'type' => 'text',
			'length' => 10,
			'notnull' => true,
			'default' => "-"
		),
		'sub_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'field_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'loc_lat' => array(
			'type' => 'float',			
			'notnull' => false
		),
		'loc_long' => array(
			'type' => 'float',			
			'notnull' => false
		),
		'loc_zoom' => array(
			'type' => 'integer',			
			'length' => 1,
			'notnull' => false
		)	
	));
		
	$ilDB->addPrimaryKey('adv_md_values_location', array('obj_id', 'sub_type', 'sub_id'));
}

?>
<#7>
<?php

	if (!$ilDB->tableColumnExists('adv_md_values_location', 'disabled'))
	{		
		$ilDB->addTableColumn('adv_md_values_location', 'disabled', array(
			"type" => "integer",
			"length" => 1,
			"notnull" => true,
			"default" => 0
		));
	}
	if (!$ilDB->tableColumnExists('adv_md_values_datetime', 'disabled'))
	{		
		$ilDB->addTableColumn('adv_md_values_datetime', 'disabled', array(
			"type" => "integer",
			"length" => 1,
			"notnull" => true,
			"default" => 0
		));
	}
	if (!$ilDB->tableColumnExists('adv_md_values_date', 'disabled'))
	{		
		$ilDB->addTableColumn('adv_md_values_date', 'disabled', array(
			"type" => "integer",
			"length" => 1,
			"notnull" => true,
			"default" => 0
		));
	}
	if (!$ilDB->tableColumnExists('adv_md_values_float', 'disabled'))
	{		
		$ilDB->addTableColumn('adv_md_values_float', 'disabled', array(
			"type" => "integer",
			"length" => 1,
			"notnull" => true,
			"default" => 0
		));
	}
	if (!$ilDB->tableColumnExists('adv_md_values_int', 'disabled'))
	{		
		$ilDB->addTableColumn('adv_md_values_int', 'disabled', array(
			"type" => "integer",
			"length" => 1,
			"notnull" => true,
			"default" => 0
		));
	}
	
?>
<#8>
<?php

// #6/#7/#8

$ilDB->dropPrimaryKey('adv_md_values_int');
$ilDB->addPrimaryKey('adv_md_values_int', array('obj_id', 'sub_type', 'sub_id', 'field_id'));
$ilDB->dropPrimaryKey('adv_md_values_float');
$ilDB->addPrimaryKey('adv_md_values_float', array('obj_id', 'sub_type', 'sub_id', 'field_id'));
$ilDB->dropPrimaryKey('adv_md_values_date');
$ilDB->addPrimaryKey('adv_md_values_date', array('obj_id', 'sub_type', 'sub_id', 'field_id'));
$ilDB->dropPrimaryKey('adv_md_values_datetime');
$ilDB->addPrimaryKey('adv_md_values_datetime', array('obj_id', 'sub_type', 'sub_id', 'field_id'));
$ilDB->dropPrimaryKey('adv_md_values_location');
$ilDB->addPrimaryKey('adv_md_values_location', array('obj_id', 'sub_type', 'sub_id', 'field_id'));

?>

<#9>
<?php

// Copied from CourseBooking install skript

require_once "Customizing/class.ilCustomInstaller.php";

if(!$ilDB->tableExists('crs_book'))
{
	$ilDB->createTable('crs_book', array(
		'crs_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		)
		,'user_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		)
		,'status' => array(
			'type' => 'integer',
			'length' => 1,
			'notnull' => true,
			'default' => 0
		)
		,'status_changed_by' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		)
		,'status_changed_on' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		)
	));

	$ilDB->addPrimaryKey('crs_book', array('crs_id', 'user_id'));
}

$new_crs_ops = array(
	'view_bookings' => array('View Bookings', 2900)
	,'book_users' => array('Book Users', 3500)
	,'cancel_bookings' => array('Cancel Bookings', 3800)
);
ilCustomInstaller::addRBACOps('crs', $new_crs_ops);

$new_org_ops = array(
	'view_employee_bookings' => array('View Employee Bookings', 2901)
	,'view_employee_bookings_rcrsv' => array('View Employee Bookings (recursive)', 2902)
	,'book_employees' => array('Book Employees', 3501)
	,'book_employees_rcrsv' => array('Book Employees (recursive)', 3502)
	,'cancel_employee_bookings' => array('Cancel Employee Bookings', 3801)
	,'cancel_employee_bookings_rcrsv' => array('Cancel Employee Bookings (recursive)', 3802)
);
ilCustomInstaller::addRBACOps('orgu', $new_org_ops);

$lang_data = array(
	"admin_tab_list_bookings" => array("Buchungen", "Bookings")
	,"admin_tab_list_cancellations" => array("Stornierungen", "Cancellations")
	,"admin_add_group" => array("Gruppe hinzufügen", "Add Group")
	,"admin_add_org_unit" => array("Org-Einheit hinzufügen", "Add Org-Unit")
	,"admin_status_booked" => array("Gebucht", "Booked")
	,"admin_status_waiting" => array("Auf Warteliste", "Waiting")
	,"admin_status_cancelled_with_costs" => array("Mit Kosten storniert", "Cancelled with Costs")
	,"admin_status_cancelled_without_costs" => array("Ohne Kosten storniert", "Cancelled without Costs")
	,"admin_assign_confirm" => array("Bestätigen Sie bitte die Buchung der folgenden Teilnehmer.", "Please confirm the booking of the following members.")
	,"admin_assign_cancelled_user" => array("Wurde bereits storniert", "Was already cancelled")
	,"admin_assign_not_bookable_user" => array("Ist nicht buchbar", "Is not bookable")
	,"admin_assign_already_assigned" => array("Ist bereits gebucht (oder auf der Warteliste)", "Is already booked (or waiting)")
	,"admin_add_participants" => array("Teilnehmer hinzufügen", "Add Participants")
	,"admin_status" => array("Buchungsstatus", "Booking Status")
	,"admin_do_not_add" => array("Nicht hinzufügen", "Do not Add")
	,"admin_group_has_no_members" => array("Die gewählte Gruppe hat keine Mitglieder", "The selected group has no members")
	,"admin_status_change" => array("Änderung des Buchungsstatus", "Booking Status Change")
	,"admin_action_to_waiting_list" => array("Auf die Warteliste", "To Waiting List")
	,"admin_action_book" => array("Als Teilnehmer hinzufügen", "Add to Participants")
	,"admin_action_cancel_without_costs" => array("Ohne Kosten stornieren", "Cancel without Costs")
	,"admin_action_cancel_with_costs" => array("Mit Kosten stornieren", "Cancel with Costs")
	,"admin_user_action_confirm_ToWaitingList" => array("Wollen Sie den Teilnehmer wirklich auf die Warteliste verschieben?", "Do you really want to put the participant on the waiting list?")
	,"admin_user_action_confirm_Book" => array("Wollen Sie die Teilnahme wirklich buchen?", "Do you really want to book the user?")
	,"admin_user_action_confirm_CancelWithoutCosts" => array("Wollen Sie den Teilnahme wirklich ohne Kosten stornieren?", "Do you really want to cancel without costs?")
	,"admin_user_action_confirm_CancelWithCosts" => array("Wollen Sie den Teilnahme wirklich mit Kosten stornieren", "Do you really want to cancel with costs?")
	,"admin_user_action_done" => array("Der Status des Benutzer wurde erfolgreich geändert.", "User status changed succesfully.")
	,"admin_org_add_recursive" => array("Mitglieder von Untereinheiten auswählen", "Include Members of Sub-Org-Units")
	,"admin_org_unit_has_no_members" => array("Die gewählte Org-Einheit hat keine Mitglieder", "The selected org-unit has no members")
	,"admin_assign_overlapping_user" => array("Folgende Buchungen überschneiden sich", "The following bookings are overlapping")
);

ilCustomInstaller::addLangData("crsbook", array("de", "en"), $lang_data, "patch generali - course booking");

?>

<#10>
<?php
if(!$ilDB->tableExists('orgu_types')) {
    $fields = array (
        'id'    => array ('type' => 'integer', 'length'  => 4,'notnull' => true, 'default' => 0),
        'default_lang'   => array ('type' => 'text', 'notnull' => true, 'length' => 4, 'fixed' => false),
        'icon'    => array ('type' => 'text', 'length'  => 256, 'notnull' => false),
        'owner' => array('type' => 'integer', 'notnull' => true, 'length' => 4),
        'create_date'  => array ('type' => 'timestamp'),
        'last_update' => array('type' => 'timestamp'),
    );
    $ilDB->createTable('orgu_types', $fields);
    $ilDB->addPrimaryKey('orgu_types', array('id'));
    $ilDB->createSequence('orgu_types');
}
?>

<#11>
<?php
if(!$ilDB->tableExists('orgu_data')) {
    $fields = array (
        'orgu_id'    => array ('type' => 'integer', 'length'  => 4,'notnull' => true, 'default' => 0),
        'orgu_type_id'   => array ('type' => 'integer', 'notnull' => false, 'length' => 4),
    );
    $ilDB->createTable('orgu_data', $fields);
    $ilDB->addPrimaryKey('orgu_data', array('orgu_id'));
}
?>

<#12>
<?php
if(!$ilDB->tableExists('orgu_types_trans')) {
    $fields = array (
        'orgu_type_id'    => array ('type' => 'integer', 'length'  => 4,'notnull' => true),
        'lang'   => array ('type' => 'text', 'notnull' => true, 'length' => 4),
        'member'    => array ('type' => 'text', 'length'  => 32, 'notnull' => true),
        'value' => array('type' => 'text', 'length' => 4000, 'notnull' => false),
    );
    $ilDB->createTable('orgu_types_trans', $fields);
    $ilDB->addPrimaryKey('orgu_types_trans', array('orgu_type_id', 'lang', 'member'));
}
?>

<#13>
<?php
if(!$ilDB->tableExists('orgu_types_adv_md_rec')) {
    $fields = array (
        'type_id'    => array ('type' => 'integer', 'length'  => 4,'notnull' => true),
        'rec_id'   => array ('type' => 'integer', 'notnull' => true, 'length' => 4),
    );
    $ilDB->createTable('orgu_types_adv_md_rec', $fields);
    $ilDB->addPrimaryKey('orgu_types_adv_md_rec', array('type_id', 'rec_id'));
}
?>

<#14>
<?php

// copied from MailTemplates install

if(!$ilDB->tableExists('cat_mail_templates'))
{
	$fields = array (
		'id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0),
		'category_name' => array(
			'type' => 'text',
			'length' => 255),
		'template_type' => array(
			'type' => 'text',
			'length' => 255),
		'consumer_location' => array(
			'type' => 'text',
			'length' => 255)
	);
	$ilDB->createTable('cat_mail_templates', $fields);
	$ilDB->addPrimaryKey('cat_mail_templates', array('id'));
	$ilDB->createSequence('cat_mail_templates');
}
?>

<#15>
<?php

// copied from MailTemplates install

if(!$ilDB->tableExists('cat_mail_variants'))
{
	$fields = array (
		'id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0),
		'mail_types_fi' => array(
			'type' => 'integer',
			'length' => 4),
		'language' => array(
			'type' => 'text',
			'length' => 255),
		'message_subject' => array(
			'type' => 'text',
			'length' => 255),
		'message_plain' => array(
			'type' => 'clob'),
		'message_html' => array(
			'type' => 'clob'),
		'created_date' => array(
			'type' => 'integer',
			'length' => 4),
		'updated_date' => array(
			'type' => 'integer',
			'length' => 4),
		'updated_usr_fi' => array(
			'type' => 'integer',
			'length' => 4),
		'template_active' => array(
			'type' => 'integer',
			'length' => 4)
	);
	$ilDB->createTable('cat_mail_variants', $fields);
	$ilDB->addPrimaryKey('cat_mail_variants', array('id'));
	$ilDB->createSequence('cat_mail_variants');
}

?>

<#16>
<?php

// copied from installer of ParticipationStatus

// init helper class
require_once "Customizing/class.ilCustomInstaller.php";

//
// create database tables
// 

if(!$ilDB->tableExists('crs_pstatus_crs'))
{
	$ilDB->createTable('crs_pstatus_crs', array(
		'crs_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		)
		,'state' => array(
			'type' => 'integer',
			'length' => 1,
			'notnull' => true,
			'default' => 0
		)
		,'alist' => array(
			'type' => 'text',
			'length' => 1000,
			'notnull' => false,
			'fixed' => false
		)			
	));

	$ilDB->addPrimaryKey('crs_pstatus_crs', array('crs_id'));
	
	
	$ilDB->createTable('crs_pstatus_usr', array(
		'crs_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		)
		,'user_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		)
		,'status' => array(
			'type' => 'integer',
			'length' => 1,
			'notnull' => false,
		)
		,'cpoints' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => false,
		)			
		,'changed_by' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		)
		,'changed_on' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		)
	));

	$ilDB->addPrimaryKey('crs_pstatus_usr', array('crs_id', 'user_id'));
}


//
// create RBAC permissions (incl. org unit?)
//

$new_crs_ops = array(
	'view_participation_status' => array('View Participation Status', 2902)
	,'set_participation_status' => array('Set Participation Status', 3502)
	,'review_participation_status' => array('Review Participation Status', 3503)
);
ilCustomInstaller::addRBACOps('crs', $new_crs_ops);


//
// lng variables
//

$lang_data = array(
	"status_not_set" => array("Nicht gesetzt", "Not set")
	,"status_successful" => array("Erfolgreich", "Successful")
	,"status_absent_excused" => array("Abwesend (entschuldigt)", "Absent (excused)")
	,"status_absent_not_excused" => array("Abwesend (unentschuldigt)", "Absent (not excused)")	
	,"admin_tab_list_status" => array("Teilnahmestatus", "Participation Status")	
	,"admin_start_date_not_reached" => array("Der Teilnahmestatus kann erst ab %s gesetzt werden.", "The participation status can be set after %s.")	
	,"admin_status" => array("Teilnahmestatus", "Participation Status")	
	,"admin_credit_points" => array("Bildungspunkte", "Credit Points")	
	,"admin_finalize" => array("Finalisieren", "Finalize")	
	,"admin_changed_by" => array("Letzte Änderung", "Last Change")	
	,"admin_finalize_need_attendance_list" => array("Eine Anwesenheitsliste wird noch benötigt.", "An attendance list is required.")	
	,"admin_finalize_need_not_status_set" => array("Der Teilnahmestatus muss für jeden Teilnehmer gesetzt sein.", "The participation status has to be set for all members.")	
	,"admin_confirm_finalize" => array("Wollen Sie wirklich den Teilnahmestatus finalisieren?", "Are you sure you really want to finalize the participation status?")
	,"admin_attendance_list" => array("Anwesenheitsliste", "Attendance List")
	,"admin_view_attendance_list" => array("Anwesenheitsliste anzeigen", "View Attendance List")
	,"admin_no_attendance_list" => array("Es wurde keine Anwesenheitsliste hochgeladen.", "No attendance list uploaded.")
	
);

ilCustomInstaller::addLangData("ptst", array("de", "en"), $lang_data, "patch generali - participation status");

?>

<#17>
<?php

// Automail-Info for the Generali

$fields = array (
     'crs_id' => array(
         'type' => 'integer',
         'length' => 4,
         'notnull' => true
         ),
     'mail_id' => array(
         "type" => "text",
         "length" => "50",
         "notnull" => true
         ),
     'last_send' => array(
         'type' => 'integer',
         'length' => 4,
         'notnull' => true,
         )
);

$ilDB->createTable('gev_automail_info', $fields);
$ilDB->addPrimaryKey('gev_automail_info', array('crs_id', 'mail_id'));

?>

<#18>
<?php

$fields = array (
     'id' => array(
         'type' => 'integer',
         'length' => 4,
         'notnull' => true
         ),
     'obj_id' => array(
         'type' => 'integer',
         'length' => 4,
         'notnull' => true
         ),
     'moment' => array(
         'type' => 'integer',
         'length' => 4,
         'notnull' => true
         ),
     'occasion' => array(
         "type" => "text",
         "length" => "100",
         "notnull" => true
         ),
     'mail_to' => array(
         "type" => "text",
         "length" => "200",
         "notnull" => true
         ),
     'mail_from' => array(
         "type" => "text",
         "length" => "200",
         "notnull" => true
         ),
     'cc' => array(
         "type" => "clob"
         ),
     'bcc' => array(
         "type" => "clob"
         ),
     'subject' => array(
         "type" => "text",
         "length" => "200",
         "notnull" => true
         ),
     'message' => array(
         "type" => "clob"
         ),
     'attachments' => array(
         "type" => "clob"
         )
);

$ilDB->createTable('mail_log', $fields);
$ilDB->addPrimaryKey('mail_log', array('id'));
$ilDB->createSequence("mail_log");

?>

<#19>
<?php

$fields = array (
     'obj_id' => array(
         'type' => 'integer',
         'length' => 4,
         'notnull' => true
         ),
     'filename' => array(
         "type" => "text",
         "length" => "255",
         "notnull" => true
         ),
     'lock_count' => array(
         'type' => 'integer',
         'length' => 4,
         'notnull' => true
     )
);


$ilDB->createTable('mail_attachment_locks', $fields);

# set collation since mysql complains about the length of primary 
# key for some collations.
$ilDB->manipulate("ALTER TABLE `mail_attachment_locks` CHANGE `filename` `filename` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ");

$ilDB->addPrimaryKey('mail_attachment_locks', array('obj_id', 'filename'));

?>

<#20>
<?php

$fields = array (
     'crs_id' => array(
         'type' => 'integer',
         'length' => 4,
         'notnull' => true
         ),
     'send_list_to_accom' => array(
         'type' => 'integer',
         'length' => 1,
         'notnull' => true
         ),
     'send_list_to_venue' => array(
         'type' => 'integer',
         'length' => 1,
         'notnull' => true
         )
);

$ilDB->createTable('gev_crs_addset', $fields);
$ilDB->addPrimaryKey('gev_crs_addset', array('crs_id'));

?>

<#21>
<?php

// INVITATION_MAIL_SETTINGS for the VoFue

$fields = array (
     'crs_id' => array(
         'type' => 'integer',
         'length' => 4,
         'notnull' => true
         ),
     'function_name' => array(
         "type" => "text",
         "length" => "50",
         "notnull" => true
         ),
     'template_id' => array(
         'type' => 'integer',
         'length' => 4,
         'notnull' => true,
         ),
     'attachments' => array(
         "type" => "clob"
         )
);

$ilDB->createTable('gev_crs_invset', $fields);
$ilDB->addPrimaryKey('gev_crs_invset', array('crs_id', 'function_name'));

?>

<#22>
<?php

$ilDB->addTableColumn( "gev_crs_addset"
					 , "inv_mailing_date"
					 , array( "type" 	=> "integer"
					 		, "length"	=> 4
					 		, "notnull"	=> true
					 		)
					 );

?>

<#23>
<?php

// GEV_USER_REGISTRATION_TOKENS for GEV

$fields = array (
     'token' => array(
         'type' => 'text',
         'length' => 32,
         'notnull' => true
         ),
     'stelle' => array(
         "type" => "text",
         "length" => 6,
         "notnull" => true
         ),
     'username' => array(
         'type' => 'text',
         'length' => 100,
         'notnull' => true
         ),
     'email' => array(
         'type' => 'text',
         'length' => 100,
         'notnull' => true
         ),
     'email_sent' => array(
         'type' => 'timestamp',
         'notnull' => false
         ),
     'token_used' => array(
         'type' => 'timestamp',
         'notnull' => false
         ),
     'password_changed' => array(
         'type' => 'timestamp',
         'notnull' => false
         ),
     "firstname" => array(
     	"type" 	=> "text",
		"length"	=> 32,
		"notnull"	=> true
		),
     "lastname" => array(
     	"type" 	=> "text",
		"length"	=> 32,
		"notnull"	=> true
		),
	"gender" => array(
		"type" 	=> "text",
		"length"	=> 1,
		"notnull"	=> true
		)
);

$ilDB->createTable('gev_user_reg_tokens', $fields);
$ilDB->addPrimaryKey('gev_user_reg_tokens', array('token'));

?>


<#24>
<?php

// copied from install of Accomodations

require_once "Customizing/class.ilCustomInstaller.php";

//
// create database tables
// 

if(!$ilDB->tableExists('crs_acco'))
{
	$ilDB->createTable('crs_acco', array(
		'crs_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		)
		,'user_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		)
		,'night' => array(
			'type' => 'date',			
			'notnull' => true
		)			
	));

	$ilDB->addPrimaryKey('crs_acco', array('crs_id', 'user_id', 'night'));
}


//
// create RBAC permissions 
//

$new_crs_ops = array(
	'view_own_accomodations' => array('View Own Accomodations', 2906)
	,'set_own_accomodations' => array('Set Own Accomodations', 2907)
	,'view_others_accomodations' => array('View Others Accomodations', 3507)
	,'set_others_accomodations' => array('Set Others Accomodations', 3508)		
);
ilCustomInstaller::addRBACOps('crs', $new_crs_ops);

//
// lng variables
//

$lang_data = array(
	"tab_list_accomodations" => array("Übernachtungen", "Accomodations")	
	,"accomodations" => array("Übernachtungen", "Accomodations")	
	,"edit_user_accomodations" => array("Teilnehmer bearbeiten", "Edit participant")	
	,"period_input_from" => array("von", "from")	
	,"period_input_to" => array("bis", "to")	
	,"period_input_from_first" => array("Vorabend", "Previous Evening")	
	,"period_input_to_last" => array("nächster Morgen", "Next Morning")	
);

ilCustomInstaller::addLangData("acco", array("de", "en"), $lang_data, "patch generali - accomodations");

?>

<#25>
<?php

// copied from install script of TEP

// init helper class
require_once "Customizing/class.ilCustomInstaller.php";

//
// create database tables
// 

if(!$ilDB->tableExists('cal_derived_entry'))
{
	// global TEP calendar
	$crs_set = new ilSetting("TEP");
	$crs_cal_id = $crs_set->get("crs_calendar");
	if(!$crs_cal_id)
	{
		$calcat_id = $ilDB->nextId('cal_categories');

		$query = "INSERT INTO cal_categories (cat_id,obj_id,color,type,title) ".
			"VALUES ( ".
			$ilDB->quote($calcat_id, 'integer').", ".
			$ilDB->quote(0, 'integer').", ".
			$ilDB->quote('#000000', 'text').", ".
			$ilDB->quote(3, 'integer').", ".  // global
			$ilDB->quote('TEP', 'text')." ".
			")";
		$ilDB->manipulate($query);

		$crs_set->set("crs_calendar", $calcat_id);
	}
}
	
// derived calender entries
if (!$ilDB->tableExists('cal_derived_entry'))
{
	$fields = array (
		'id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0),		    
		'master_cal_entry' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0),		
		'cat_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0)
	);
	$ilDB->createTable('cal_derived_entry', $fields);
	$ilDB->addPrimaryKey('cal_derived_entry', array('id'));
	$ilDB->createSequence('cal_derived_entry');		
}
	
// calendar entry type
if(!$ilDB->tableColumnExists('cal_entries', 'entry_type'))
{
	$ilDB->addTableColumn('cal_entries', 'entry_type', 
		array(
			'type' => 'text', 
			'length' => 100, 
			'notnull' => false, 
			'default' => ''
	));			
}

// calendar entry types
if (!$ilDB->tableExists('tep_type'))
{
	$fields = array (
		'id' => array(
			'type' => 'text',
			'length' => 100,
			'notnull' => true,
			'default' => ''),		    
		'title' => array(
			'type' => 'text',
			'length' => 1000,
			'notnull' => true),		
		'bg_color' => array(
			'type' => 'text',
			'length' => 6,
			'fixed' => true,
			'notnull' => false),
		'font_color' => array(
			'type' => 'text',
			'length' => 6,
			'fixed' => true,
			'notnull' => false),
		'tep_active' => array(
			'type' => 'integer',
			'length' => 1,
			'notnull' => false,
			'default' => 1)
	);
	$ilDB->createTable('tep_type', $fields);
	$ilDB->addPrimaryKey('tep_type', array('id'));	
}

if(!$ilDB->tableExists('tep_op_days'))
{
	$fields = array (
		'obj_type' => array(
			'type' => 'text',
			'length' => 200,
			'notnull' => true,
			'default' => ''),		    				
		'obj_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0),
		'user_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0),
		'miss_day' => array(
			'type' => 'date',
			'notnull' => true)					
	);
	$ilDB->createTable('tep_op_days', $fields);
}	


//
// create RBAC permissions (incl. org unit?)
//

$new_org_ops = array(
	'tep_is_tutor' => array('TEP Tutor', 2904)
	,'tep_view_other' => array('TEP View Other', 2905)
	,'tep_view_other_rcrsv' => array('TEP View Other (recursive)', 2906)
	,'tep_edit_other' => array('TEP Edit Other', 3505)
	,'tep_edit_other_rcrsv' => array('TEP Edit Other (recursive)', 3506)
);

ilCustomInstaller::addRBACOps('orgu', $new_org_ops);

//
// lng variables
//

$lang_data = array(
	"personal_calendar_title" => array("TEP", "TEP")
	,"page_title" => array("TEP", "TEP")
	,"tab_list" => array("Terminliste", "List Of Dates")
	,"tab_month" => array("Monatsansicht", "Monthly")
	,"tab_halfyear" => array("Halbjahresansicht", "Half-Year")
	,"add_new_entry" => array("Neuen Termin anlegen", "Add new entry")
	,"export" => array("XLS-Export", "XLS-Export")
	,"print" => array("Drucken", "Print")
	,"create_entry" => array("Eintrag anlegen", "Create Entry")
	,"entry_owner" => array("Termin für", "Entry of")
	,"entry_derived" => array("Weitere Teilnehmer", "Other Participants")
	,"entry_title" => array("Termintitel", "Title of Entry")
	,"entry_type" => array("Typ", "Type")
	,"entry_period" => array("Zeitraum", "Period")
	,"entry_location" => array("Ort", "Location")
	,"entry_created" => array("Der Eintrag wurde erstellt.", "The entry has been created.")
	,"entry_updated" => array("Der Eintrag wurde aktualisiert.", "The entry has been updated.")
	,"legend" => array("Legende", "Legend")
	,"search_all" => array("--Alle--", "--All--")
	,"filter_submit" => array("Filtern", "Filter")
	,"filter_no_tutor" => array("Seminare ohne Trainer anzeigen?", "Show Seminars without Trainers?")
	,"column_no_tutor" => array("Seminare ohne Trainer", "Seminars without Trainers")
	,"update_entry" => array("Termin bearbeiten", "Edit Entry")
	,"list_view_title" => array("Terminliste", "List Of Dates")
	,"list_view_info" => array("Es werden Termine der nächsten 4 Wochen angezeigt.", "Only entries in the next 4 weeks are shown.")
	,"entry_delete_sure" => array("Wollen Sie wirklich den Termin löschen?", "Are you sure you want to delete the entry?")
	,"delete_entry" => array("Termin löschen", "Delete Entry")
	,"entry_deleted" => array("Termin wurde gelöscht.", "Entry has been deleted.")
	,"op_tab_list_operation_days" => array("Einsatztage", "Operation Days")
	,"edit_user_operation_days" => array("Einsatztage bearbeiten", "Edit Operation Days")
	,"edit_operation_days" => array("Einsatztage", "Operation Days")
	,"filter_orgu_all" => array("--Alle Tutoren--", "--All Trainers--")
	,"filter_orgu_rcrsv" => array("rekursiv?", "recursive?")
	,"filter_tutor" => array("Tutor", "Trainer")
	,"filter_tutor_empty" => array("Die Auswahl der Organisationseinheiten enthält keine Tutoren.", "The selected organisational units did not yield any trainers.")
);

ilCustomInstaller::addLangData("tep", array("de", "en"), $lang_data, "patch generali - TEP");

?>

<#26>
<?php

$stmt = $ilDB->prepareManip("INSERT INTO tep_type (id, title, bg_color, font_color, tep_active) VALUES (?, ?, ?, ?, ?) "
						   , array("integer", "text", "text", "text", "integer"));
$data = array( array(1, "Training", "f6000d", "000000", "0")
			 , array(2, "Veranstaltung (LD)", "f76809", "000000", "1")
			 , array(3, "Projekt/Sondermaßnahme", "b90007", "000000", "1")
			 , array(4, "Trainingsvorbereitung", "a2ff2c", "000000", "1")
			 , array(5, "Büro", "49ff00", "000000", "1")
			 , array(6, "Bereichs-/Abteilungsmeeting", "1900ff", "ffffff", "1")
			 , array(7, "Besprechung", "4effff", "000000", "1")
			 , array(8, "Besuch Infoveranstaltung", "feff98", "000000", "1")
			 , array(9, "Weiterbildung", "feff00", "000000", "1")
			 , array(10, "Urlaub/Gleittag", "e0e0e0", "000000", "1")
			 , array(11, "Feiertag im Bundesland", "c0c0c0", "000000", "1")
			 );
$ilDB->executeMultiple($stmt, $data);
$ilDB->free($stmt);
?>

<#27>
<?php

if(!$ilDB->tableExists('bill'))
{
	$fields = array(
		'bill_pk'               => array(
			'type'    => 'integer',
			'length'  => 4,
			'notnull' => true,
			'default' => 0
		),
		'bill_number'           => array(
			'type'    => 'text',
			'length'  => 255,
			'notnull' => false,
			'default' => null
		),
		'bill_recipient_name'   => array(
			'type'    => 'text',
			'length'  => 255,
			'notnull' => false,
			'default' => null
		),
		'bill_recipient_street' => array(
			'type'    => 'text',
			'length'  => 255,
			'notnull' => false,
			'default' => null
		),
		'bill_recipient_hnr'    => array(
			'type'    => 'text',
			'length'  => 255,
			'notnull' => false,
			'default' => null
		),
		'bill_recipient_zip'    => array(
			'type'    => 'text',
			'length'  => 255,
			'notnull' => false,
			'default' => null
		),
		'bill_recipient_city'   => array(
			'type'    => 'text',
			'length'  => 255,
			'notnull' => false,
			'default' => null
		),
		'bill_recipient_cntry'  => array(
			'type'    => 'text',
			'length'  => 255,
			'notnull' => false,
			'default' => null
		),
		'bill_date'             => array(
			'type'    => 'integer',
			'length'  => 4,
			'notnull' => true,
			'default' => 0
		),
		'bill_title'            => array(
			'type'    => 'text',
			'length'  => 255,
			'notnull' => true
		),
		'bill_description'      => array(
			'type'    => 'text',
			'length'  => 4000,
			'notnull' => false,
			'default' => null
		),
		'bill_vat'              => array(
			'type'    => 'float',
			'notnull' => true,
			'default' => 0
		),
		'bill_cost_center'      => array(
			'type'    => 'text',
			'length'  => 255,
			'notnull' => false,
			'default' => null
		),
		'bill_currency'         => array(
			'type'    => 'text',
			'length'  => 255,
			'notnull' => true
		),
		'bill_usr_id'           => array(
			'type'    => 'integer',
			'length'  => 4,
			'notnull' => true,
			'default' => 0
		),
		'bill_year'             => array(
			'type'    => 'integer',
			'length'  => 2,
			'notnull' => true,
			'default' => 0
		),
		'bill_final'            => array(
			'type'    => 'integer',
			'length'  => 1,
			'notnull' => true,
			'default' => 0
		),
		'bill_context_id'       => array(
			'type'    => 'integer',
			'length'  => 4,
			'notnull' => false,
			'default' => null
		),
		"bill_recipient_email" => array(
			"type" 	=> "text",
			"length"	=> 255,
			"notnull"	=> false,
			"default" => null
		)
	);

	$ilDB->createTable('bill', $fields);
	$ilDB->addPrimaryKey('bill', array('bill_pk'));
	$ilDB->createSequence('bill');
	
	$ilDB->query('ALTER TABLE bill ENGINE=INNODB');
	$ilDB->query('ALTER TABLE bill_seq ENGINE=INNODB');
}

?>

<#28>
<?php

// Tracking Infos about cron jobs for mail

$fields = array (
    'crs_id' => array(
        'type' => 'integer',
        'length' => 4,
        'notnull' => true
        ),
    'title' => array(
        "type" => "text",
        "length" => 64,
        "notnull" => true
        ),
    'send_at' => array(
        'type' => 'timestamp',
        'notnull' => false
        )
);

$ilDB->createTable('gev_crs_dl_mail_cron', $fields);
$ilDB->addPrimaryKey('gev_crs_dl_mail_cron', array('crs_id', 'title'));

?>


<#29>
<?php

// Tracking Infos about cron jobs for mail

$fields = array (
    'crs_id' => array(
        'type' => 'integer',
        'length' => 4,
        'notnull' => true
        ),
    'mail_id' => array(
        "type" => "text",
        "length" => 64,
        "notnull" => true
        ),
    'recipient' => array(
        'type' => 'text',
        "length" => 128,
        'notnull' => false
        )
    , "occasion" => array(
        'type' => 'text',
        "length" => 256,
        'notnull' => true
    )
);

$ilDB->createTable('gev_crs_deferred_mails', $fields);
$ilDB->addPrimaryKey('gev_crs_deferred_mails', array('crs_id', 'mail_id', 'recipient'));

?>

<#30>
<?php

// Tracking relation between coupons and bills they were created for.

$fields = array (
	'bill_pk' => array(
		'type'    => 'integer',
		'length'  => 4,
		'notnull' => true,
		'default' => 0
	),
	'coupon_code' => array(
		'type'    => 'text',
		'length'  => 255,
		'notnull' => true
	)
);

$ilDB->createTable('gev_bill_coupon', $fields);
$ilDB->addPrimaryKey('gev_bill_coupon', array('bill_pk'));

?>

<#31>
<?php

$ilDB->manipulate("UPDATE settings SET value = 1800 WHERE keyword = 'reg_hash_life_time'");

?>

<#32>
<?php
if(!$ilDB->tableExists('billitem'))
{
	$fields = array(
		'billitem_pk'          => array(
			'type'    => 'integer',
			'length'  => 4,
			'notnull' => true,
			'default' => 0
		),
		'bill_fk'          => array(
			'type'    => 'integer',
			'length'  => 4,
			'notnull' => true,
			'default' => 0
		),
		'billitem_title'       => array(
			'type'    => 'text',
			'length'  => 255,
			'notnull' => true
		),
		'billitem_description' => array(
			'type'    => 'text',
			'length'  => 4000,
			'notnull' => false,
			'default' => null
		),
		'billitem_pta'         => array(
			'type'    => 'float',
			'notnull' => true,
			'default' => 0
		),
		'billitem_vat'         => array(
			'type'    => 'float',
			'notnull' => true,
			'default' => 0
		),
		'billitem_currency'    => array(
			'type'    => 'text',
			'length'  => 255,
			'notnull' => true
		),
		'billitem_context_id'  => array(
			'type'    => 'integer',
			'length'  => 4,
			'notnull' => false,
			'default' => null
		),
		'billitem_final'       => array(
			'type'    => 'integer',
			'length'  => 1,
			'notnull' => true,
			'default' => 0
		)
	);

	$ilDB->createTable('billitem', $fields);
	$ilDB->addPrimaryKey('billitem', array('billitem_pk'));
	$ilDB->createSequence('billitem');
	
	$ilDB->query('ALTER TABLE bill_seq ENGINE=INNODB');
	$ilDB->query('ALTER TABLE billitem ENGINE=INNODB');
	$ilDB->query('ALTER TABLE billitem_seq ENGINE=INNODB');
}
?>

<#33>
<?php
if(!$ilDB->tableExists('coupon'))
{
	$fields = array(
		'coupon_pk'          => array(
			'type'    => 'integer',
			'length'  => 4,
			'notnull' => true,
			'default' => 0
		),
		'coupon_code'        => array(
			'type'    => 'text',
			'length'  => 255,
			'notnull' => true
		),
		'coupon_value'       => array(
			'type'    => 'float',
			'notnull' => true,
			'default' => 0
		),
		'coupon_last_change' => array(
			'type'    => 'integer',
			'length'  => 4,
			'notnull' => true,
			'default' => 0
		),
		'coupon_expires'     => array(
			'type'    => 'integer',
			'length'  => 4,
			'notnull' => true,
			'default' => 0
		),
		'coupon_usr_id'      => array(
			'type'    => 'integer',
			'length'  => 4,
			'notnull' => true,
			'default' => 0
		),
		'coupon_active'      => array(
			'type'    => 'integer',
			'length'  => 1,
			'notnull' => true,
			'default' => 0
		),
		'coupon_created'     => array(
			'type'    => 'integer',
			'length'  => 4,
			'notnull' => true,
			'default' => 0
		)
	);

	$ilDB->createTable('coupon', $fields);
	$ilDB->addPrimaryKey('coupon', array('coupon_pk'));
	$ilDB->createSequence('coupon');
	
	$ilDB->query('ALTER TABLE coupon ENGINE=INNODB');
	$ilDB->query('ALTER TABLE coupon_seq ENGINE=INNODB');
}
?>

<#34>
<?php

// init helper class
require_once "Customizing/class.ilCustomInstaller.php";

$lang_data = array(
	"numberlabel" => array("Rechnungsnummer", "Bill Number"),
	"vat"         => array("Umsatzsteuer", "Sales Tax"),
	"net"         => array("(netto)", "(after tax)"),
	"bru"         => array("(brutto)", "(pre-tax)"),
	"val"         => array("Rechnungsbetrag", "Amount")
);

ilCustomInstaller::addLangData("billing", array("de", "en"), $lang_data, "patch generali - Billing");
?>

<#35>
<?php
if(!$ilDB->tableExists('hist_usercoursestatus'))
{
	$fields = array(
		'row_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true),
		'hist_version' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 1),
		'hist_historic' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0),
		'creator_user_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true),
		'created_ts' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0),
		'usr_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true),
		'crs_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true),
		'credit_points' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true),
		'bill_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true),
		'booking_status' => array(
			'type' => 'text',
			'length' => 255,
			'notnull' => true),
		'participation_status' => array(
			'type' => 'text',
			'length' => 255,
			'notnull' => true),
		'okz' => array(
			'type' => 'text',
			'length' => 255,
			'notnull' => false),
		'org_unit' => array(
			'type' => 'text',
			'length' => 255,
			'notnull' => false),
		'certificate' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => false),
		'begin_date' => array(
			'type' => 'date'),
		'end_date' => array(
			'type' => 'date'),
		'overnights' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => false),
		'function' => array(
			'type' => 'text',
			'length' => 255,
			'notnull' => false)
	);
	$ilDB->createTable('hist_usercoursestatus', $fields);
	$ilDB->addPrimaryKey('hist_usercoursestatus', array('row_id'));
	$ilDB->createSequence('hist_usercoursestatus');
}
?>

<#36>
<?php

if(!$ilDB->tableExists('hist_certfile'))
{
	$fields = array (
		'row_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true),
		'certfile' => array(
			'type' => 'clob',
			'notnull' => true)
	);
	$ilDB->createTable('hist_certfile', $fields);
	$ilDB->addPrimaryKey('hist_certfile', array('row_id'));
	$ilDB->createSequence('hist_certfile');
}
?>

<#37>
<?php
if(!$ilDB->tableExists('hist_course'))
{
	$fields = array (
		'row_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true),
		'hist_version' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 1),
		'hist_historic' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0),
		'creator_user_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true),
		'created_ts' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0),
		'crs_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true),
		'custom_id' => array(
			'type' => 'text',
			'length' => 255,
			'notnull' => true),
		'title' => array(
			'type' => 'text',
			'length' => 255,
			'notnull' => true),
		'template_title' => array(
			'type' => 'text',
			'length' => 255,
			'notnull' => true),
		'type' => array(
			'type' => 'text',
			'length' => 255,
			'notnull' => true),
		'topic_set' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true),
		'begin_date' => array(
			'type' => 'date'),
		'end_date' => array(
			'type' => 'date'),
		'hours' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => false,
			'default' => 0),
		'is_expert_course' => array(
			'type' => 'integer',
			'length' => 1,
			'notnull' => true,
			'default' => 0),
		'venue' => array(
			'type' => 'text',
			'length' => 255,
			'notnull' => false),
		'provider' => array(
			'type' => 'text',
			'length' => 255,
			'notnull' => false),
		'tutor' => array(
			'type' => 'text',
			'length' => 255,
			'notnull' => false),
		'max_credit_points' => array(
			'type' => 'text',
			'length' => 255,
			'notnull' => false),
		'fee' => array(
			'type' => 'float',
			'notnull' => false),
		"is_template" =>  array(
			"type" 	=> "text", 
			"length" 	=> 8,
			"notnull" => false)
	);
	$ilDB->createTable('hist_course', $fields);
	$ilDB->addPrimaryKey('hist_course', array('row_id'));
	$ilDB->createSequence('hist_course');
}
?>

<#38>
<?php
if(!$ilDB->tableExists('hist_topicset2topic'))
{
	$fields = array (
		'row_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true),
		'topic_set_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,),
		'topic_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,),
	);
	$ilDB->createTable('hist_topicset2topic', $fields);
	$ilDB->addPrimaryKey('hist_topicset2topic', array('row_id'));
	$ilDB->createSequence('hist_topicset2topic');
}
?>

<#39>
<?php
if(!$ilDB->tableExists('hist_topics'))
{
	$fields = array (
		'row_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true),
		'topic_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,),
		'topic_title' => array(
			'type' => 'text',
			'length' => 255,
			'notnull' => true),
	);
	$ilDB->createTable('hist_topics', $fields);
	$ilDB->addPrimaryKey('hist_topics', array('row_id'));
	$ilDB->createSequence('hist_topics');
}
?>

<#40>
<?php
if(!$ilDB->tableExists('hist_user'))
{
	$fields = array (
		'row_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true),
		'hist_version' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 1),
		'hist_historic' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0),
		'creator_user_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true),
		'created_ts' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0),
		'user_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true),
		'firstname' => array(
			'type' => 'text',
			'length' => 255,
			'notnull' => true),
		'lastname' => array(
			'type' => 'text',
			'length' => 255,
			'notnull' => true),
		'gender' => array(
			'type' => 'text',
			'length' => 255,
			'notnull' => true),
		'birthday' => array(
			'type' => 'date'),
		'org_unit' => array(
			'type' => 'text',
			'length' => 255,
			'notnull' => true),
		'position_key' => array(
			'type' => 'text',
			'length' => 255,
			'notnull' => true),
		'entry_date' => array(
			'type' => 'date'),
		'exit_date' => array(
			'type' => 'date'),
		'bwv_id' => array(
			'type' => 'text',
			'length' => 255,
			'notnull' => true),
		'okz' => array(
			'type' => 'text',
			'length' => 255,
			'notnull' => true),
		'begin_of_certification' => array(
			'type' => 'date'),
		'deleted' => array(
			'type' => 'integer',
			'length' => 1,
			'notnull' => true)
	);
	$ilDB->createTable('hist_user', $fields);
	$ilDB->addPrimaryKey('hist_user', array('row_id'));
	$ilDB->createSequence('hist_user');
}
?>

<#41>
<?php
	$ilCtrlStructureReader->getStructure();
?>

<#42>
<?php
// init helper class
require_once "Customizing/class.ilCustomInstaller.php";

ilCustomInstaller::initPluginEnv();
ilCustomInstaller::activatePlugin(IL_COMP_MODULE, "OrgUnit", "orgutypehk", "GEVOrgTypes");

?>

<#43>
<?php
// init helper class
require_once "Customizing/class.ilCustomInstaller.php";

ilCustomInstaller::initPluginEnv();
ilCustomInstaller::activatePlugin(IL_COMP_SERVICE, "AdvancedMetaData", "amdc", "CourseAMD");

?>

<#44>
<?php
// init helper class
require_once "Customizing/class.ilCustomInstaller.php";

ilCustomInstaller::initPluginEnv();
ilCustomInstaller::activatePlugin(IL_COMP_SERVICE, "AdvancedMetaData", "amdc", "OrgUnitAMD");

?>

<#45>
<?php
// init helper class
// require_once "Customizing/class.ilCustomInstaller.php";

// ilCustomInstaller::initPluginEnv();
// ilCustomInstaller::activatePlugin(IL_COMP_SERVICE, "EventHandling", "evhk", "GEVBilling");

?>

<#46>
<?php
// init helper class
require_once "Customizing/class.ilCustomInstaller.php";

ilCustomInstaller::initPluginEnv();
ilCustomInstaller::activatePlugin(IL_COMP_SERVICE, "EventHandling", "evhk", "GEVCourseCreation");

?>

<#47>
<?php
// init helper class
require_once "Customizing/class.ilCustomInstaller.php";

ilCustomInstaller::initPluginEnv();
ilCustomInstaller::activatePlugin(IL_COMP_SERVICE, "EventHandling", "evhk", "GEVCourseTemplateCreation");

?>

<#48>
<?php
// init helper class
require_once "Customizing/class.ilCustomInstaller.php";

ilCustomInstaller::initPluginEnv();
ilCustomInstaller::activatePlugin(IL_COMP_SERVICE, "EventHandling", "evhk", "GEVCourseUpdate");

?>

<#49>
<?php
// init helper class
require_once "Customizing/class.ilCustomInstaller.php";

ilCustomInstaller::initPluginEnv();
ilCustomInstaller::activatePlugin(IL_COMP_SERVICE, "EventHandling", "evhk", "GEVMailing");

?>

<#50>
<?php
// init helper class
require_once "Customizing/class.ilCustomInstaller.php";

ilCustomInstaller::initPluginEnv();
ilCustomInstaller::activatePlugin(IL_COMP_SERVICE, "EventHandling", "evhk", "GEVOrgUnitUpdate");

?>

<#51>
<?php
// init helper class
require_once "Customizing/class.ilCustomInstaller.php";

ilCustomInstaller::initPluginEnv();
ilCustomInstaller::activatePlugin(IL_COMP_SERVICE, "EventHandling", "evhk", "GEVWaitingList");

?>

<#52>
<?php
//
?>


<#53>
<?php
// update on #40, missing fields for wbd
$txt_fields_hist_user = array(
	'street',
	'zipcode',
	'city',
	'phone_nr',
	'mobile_phone_nr',
	'email',
);
foreach ($txt_fields_hist_user as $field) {
	if(!$ilDB->tableColumnExists('hist_user', $field)){
		$ilDB->addTableColumn('hist_user', $field, array(
			'type' => 'text',
			'length' => 255,
			'notnull' => false
			)
		);	
	}
}
?>

<#54>
<?php
// marker for wbd-reports
if(!$ilDB->tableColumnExists('hist_user', 'last_wbd_report')){
	$ilDB->manipulate("ALTER TABLE `hist_user` ADD `last_wbd_report` DATE NULL DEFAULT NULL AFTER `created_ts`");
}
if(!$ilDB->tableColumnExists('hist_usercoursestatus', 'last_wbd_report')){
	$ilDB->manipulate("ALTER TABLE `hist_usercoursestatus` ADD `last_wbd_report` DATE NULL DEFAULT NULL AFTER `created_ts`");
}

	

?>


<#55>
<?php
// update on #40,#53; missing fields for wbd
$txt_fields_hist_user = array(
	'agent_status', 
	'wbd_type', //USR_TP_TYPE

);
foreach ($txt_fields_hist_user as $field) {
	if(!$ilDB->tableColumnExists('hist_user', $field)){
		$ilDB->addTableColumn('hist_user', $field, array(
			'type' => 'text',
			'length' => 255,
			'notnull' => false
			)
		);	
	}
}
?>

<#56>
<?php
// update on #40,#53, #55; missing fields for wbd
$txt_fields_hist_user = array(
	'wbd_email', //USR_UDF_PRIV_EMAIL
);
foreach ($txt_fields_hist_user as $field) {
	if(!$ilDB->tableColumnExists('hist_user', $field)){
		$ilDB->addTableColumn('hist_user', $field, array(
			'type' => 'text',
			'length' => 255,
			'notnull' => false
			)
		);	
	}
}
?>

<#57>
<?php
// missing fields for wbd, hist_course
$txt_fields_hist_course = array(
	'wbd_topic'
);
foreach ($txt_fields_hist_course as $field) {
	if(!$ilDB->tableColumnExists('hist_course', $field)){
		$ilDB->addTableColumn('hist_course', $field, array(
			'type' => 'text',
			'length' => 255,
			'notnull' => false
			)
		);	
	}
}
?>
<#58>
<?php
// missing fields for wbd, hist_usercoursestatus
$txt_fields_hist_course = array(
	'wbd_booking_id' 
);
foreach ($txt_fields_hist_course as $field) {
	if(!$ilDB->tableColumnExists('hist_usercoursestatus', $field)){
		$ilDB->addTableColumn('hist_usercoursestatus', $field, array(
			'type' => 'text',
			'length' => 255,
			'notnull' => false
			)
		);	
	}
}
?>
<#59>
<?php

if(!$ilDB->tableColumnExists("bill", "bill_finalized_date")) {
	$ilDB->addTableColumn("bill", "bill_finalized_date", array(
		  "type" => "integer"
		, "length" => 4
		, "notnull" => false
		));
}

$ilDB->manipulate("UPDATE bill SET bill_finalized_date = UNIX_TIMESTAMP() WHERE bill_final = 1");

?>

<#60>
<?php
// TEP categories

$query = "DELETE FROM tep_type WHERE 1";
$ilDB->manipulate($query);

$stmt = $ilDB->prepareManip("INSERT INTO tep_type (id, title, bg_color, font_color, tep_active) VALUES (?, ?, ?, ?, ?) "
						   , array("integer", "text", "text", "text", "integer"));

$data = array(
	  array(1,  "Projekt", 					"f09273", "000000", "1")
	, array(2,  "Ausgleichstag",			"86b37d", "000000", "1")
	, array(3,  "Krankheit",				"86b37d", "000000", "1")
	, array(4,  "Urlaub genehmigt",			"86b37d", "000000", "1")
	, array(5,  "FD Gespräch",				"e6da9d", "000000", "1")
	, array(6,  "FD-MA Teammeeting",		"e6da9d", "000000", "1")
	, array(7,  "RD-Gespräch",				"e6da9d", "000000", "1")
	, array(8,  "OD-FD Meeting",			"e6da9d", "000000", "1")
	, array(9,  "AKL-Gespräch",				"e6da9d", "000000", "1")
	, array(10, "bAV-Arbeitskreis",			"e6da9d", "000000", "1")
	, array(11, "Gewerbe-Arbeitskreis",		"e6da9d", "000000", "1")
	, array(12, "FDL-Arbeitskreis",			"e6da9d", "000000", "1")
	, array(13, "Firmenkunden",				"cccccc", "000000", "1")
	, array(14, "Aquise Pilotprojekt", 		"cccccc", "000000", "1")
	, array(15, "Individuelle Unterstützung SpV/FD",	"cccccc", "000000", "1")
	, array(16, "AD Begleitung",			"cccccc", "000000", "1")
	, array(17, "Urlaub beantragt",			"b8ce8d", "000000", "1")
	, array(18, "Trainer- / DBV Klausur (Zentral)", "bf6364", "000000", "1")
	, array(19, "Trainer Teammeeting",		"bf6364", "000000", "1")
	, array(20, "Arbeitsgespräch", 			"bf6364", "000000", "1")
	, array(21, "Veranstaltung / Tagung (Zentral)",	"f09273", "000000", "1")
	, array(22, "Büro", 					"b8ace6",  "000000", "1")
	, array(23, "Dezentraler Feiertag", 	"b8ce8d", "000000", "1")
	, array(24, "Training", 				"f0e960" , "000000", "1")
	//, array(24, "Gibt es noch nicht", 		"bf6364" , "000000", "1")
);

$ilDB->executeMultiple($stmt, $data);
$ilDB->free($stmt);
?>


<#61>
<?php
// update on #719, new fields 
$txt_fields_hist_user = array(
	'job_number',
	'adp_number',
	'position_key',
	'org_unit_above1',
	'org_unit_above2'
);
foreach ($txt_fields_hist_user as $field) {
	if(!$ilDB->tableColumnExists('hist_user', $field)){
		$ilDB->addTableColumn('hist_user', $field, array(
			'type' => 'text',
			'length' => 255,
			'notnull' => false
			)
		);	
	}
}
?>


<#62>
<?php
// change fieldname

$query = "ALTER TABLE hist_user 
	CHANGE agent_status wbd_agent_status 
	VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL 
	DEFAULT NULL;
";

$ilDB->manipulate($query);
?>

<#63>
<?php
// change fieldname

$query = "ALTER TABLE hist_usercoursestatus 
	CHANGE bill_id bill_id 
	VARCHAR(16) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL 
	DEFAULT NULL;
";

$ilDB->manipulate($query);

/**
* migrate hist_usercoursestatus.bill_id to hold 
*/

$query = "SELECT 
			hist_usercoursestatus.row_id, 
			hist_usercoursestatus.bill_id,
			bill.bill_number 
		FROM hist_usercoursestatus
		INNER JOIN bill ON 
			hist_usercoursestatus.bill_id = bill.bill_pk
		WHERE 
			hist_usercoursestatus.bill_id > 0
			AND 
			hist_usercoursestatus.hist_historic = 0
			AND 
			bill.bill_number IS NOT NULL;
		";


$res = $ilDB->query($query);
while($rec = $ilDB->fetchAssoc($res)) {
	$bill_id = $rec['bill_number'];
	$row_id = $rec['row_id'];
	$sql = "UPDATE hist_usercoursestatus SET bill_id = '$bill_id' WHERE row_id=$row_id";
	$ilDB->manipulate($sql);
}
?>

<#64>
<?php
// missing fields, hist_course
$txt_fields_hist_course = array(
	'edu_program' //CRS_AMD_EDU_PROGRAM
);
foreach ($txt_fields_hist_course as $field) {
	if(!$ilDB->tableColumnExists('hist_course', $field)){
		$ilDB->addTableColumn('hist_course', $field, array(
			'type' => 'text',
			'length' => 255,
			'notnull' => false
			)
		);	
	}
}
?>

<#65>
<?php
if(!$ilDB->tableExists('org_unit_personal'))
{
	$fields = array (
    'orgunit_id'    => array(
    		'type' => 'integer',
    		'length'  => 4,
    		'notnull' => true,
    		'default' => 0),

  'usr_id'    => array(
    		'type' => 'integer',
    		'length'  => 4,
    		'notnull' => true,
    		'default' => 0),

  
  );
  $ilDB->createTable('org_unit_personal', $fields);
  $ilDB->addPrimaryKey('org_unit_personal', array('orgunit_id'));
}
?>

<#66>
<?php
	$ilCtrlStructureReader->getStructure();
?>

<#67>
<?php
	if(!$ilDB->tableColumnExists('gev_crs_addset', "suppress_mails")){
		$ilDB->addTableColumn('gev_crs_addset', "suppress_mails", array(
			'type' => 'integer',
			'length' => 1,
			'notnull' => true,
			'default' => 0
			)
		);	
	}
?>

<#68>
<?php
	if(!$ilDB->tableColumnExists('hist_user', "is_vfs")){
		$ilDB->addTableColumn('hist_user', "is_vfs", array(
			'type' => 'integer',
			'length' => 1,
			'notnull' => true,
			'default' => 0
			)
		);	
	}

?>

<#69>
<?php

if(!$ilDB->tableExists('copy_mappings'))
{
	$fields = array (
	'target_ref_id'    => array(
			'type' => 'integer',
			'length'  => 4,
			'notnull' => true,
			'default' => 0),
	
	'source_ref_id'    => array(
			'type' => 'integer',
			'length'  => 4,
			'notnull' => true,
			'default' => 0),
	);

	$ilDB->createTable('copy_mappings', $fields);
	$ilDB->addPrimaryKey('copy_mappings', array('target_ref_id'));
}

?>

<#70>
<?php

if(!$ilDB->tableExists('hist_tep'))
{
	$fields = array (
		'row_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true),
		'hist_version' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 1),
		'hist_historic' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0),
		'creator_user_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true),
		'created_ts' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0),
		'user_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true),
		'cal_entry_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true),
		'cal_derived_entry_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => false),
		'context_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true),
		'title' => array(
			'type' => 'text',
			'length' => 255,
			'notnull' => true),
		'subtitle' => array(
			'type' => 'text',
			'length' => 255),
		'description' => array(
			'type' => 'text',
			'length' => 255),
		'location' => array(
			'type' => 'text',
			'length' => 255),
		'fullday' => array(
			'type' => 'integer',
			'length' => 1,
			'notnull' => true),
		'begin_date' => array(
			'type' => 'date'),
		'end_date' => array(
			'type' => 'date'),
		'individual_days' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true),
		'category' => array(
			'type' => 'text',
			'length' => 255,
			'notnull' => true),
		'deleted' => array(
			'type' => 'integer',
			'length' => 1,
			'notnull' => true)
	);
	$ilDB->createTable('hist_tep', $fields);
	$ilDB->addPrimaryKey('hist_tep', array('row_id'));
	$ilDB->createSequence('hist_tep');
}

?>

<#71>
<?php
	$ilCtrlStructureReader->getStructure();
?>

<#72>
<?php
	require_once "Customizing/class.ilCustomInstaller.php";

	$new_org_ops = array(
		 'add_dec_training_self' => array('Create decentral Trainings for self', 2907)
		,'add_dec_training_others' => array('Create decentral Trainings for others', 2908)
		,'add_dec_training_others_rec' => array('Create decentral Trainings for others (recursiv)', 2909)
	);
	ilCustomInstaller::addRBACOps('orgu', $new_org_ops);
?>

<#73>
<?php
	$query = "UPDATE  tep_type SET title = 'Akquise Pilotprojekt' WHERE id=14";
	$ilDB->manipulate($query);
?>

<#74>
<?php
	require_once "Customizing/class.ilCustomInstaller.php";

	$new_crs_ops = array(
		 'write_reduced_settings' => array('Edit reduced settings of training.', 6001)
	);
	ilCustomInstaller::addRBACOps('crs', $new_crs_ops);
?>

<#75>
<?php
	// create instance
	include_once("Services/AccessControl/classes/class.ilObjRoleTemplate.php");
	require_once "Customizing/class.ilCustomInstaller.php";
	
	ilCustomInstaller::maybeInitPluginAdmin();
	ilCustomInstaller::maybeInitObjDefinition();
	ilCustomInstaller::maybeInitAppEventHandler();
	ilCustomInstaller::maybeInitTree();
	ilCustomInstaller::maybeInitRBAC();
	ilCustomInstaller::maybeInitUserToRoot();
	
	$newObj = new ilObjRoleTemplate();
	$newObj->setType("rolt");
	$newObj->setTitle("Trainingsersteller");
	$newObj->setDescription("Rolle für die Ersteller von dezentralen Trainings");
	$newObj->create();
	$newObj->createReference();
	$newObj->putInTree(ROLE_FOLDER_ID);
	$newObj->setPermissions(ROLE_FOLDER_ID);

	$ilDB->manipulate("INSERT INTO rbac_fa (rol_id, parent, assign, protected)"
					 ." VALUES ( ".$ilDB->quote($newObj->getId(), "integer")
					 ."        , ".$ilDB->quote(ROLE_FOLDER_ID, "integer")
					 ."        , 'n', 'y')"
					 );
?>

<#76>
<?php
	$ilCtrlStructureReader->getStructure();
?>

<#77>
<?php
	if(!$ilDB->tableExists('gev_na_tokens'))
	{
		$fields = array (
			'user_id' => array(
				'type' => 'integer',
				'length' => 4,
				'notnull' => true),
			'adviser_id' => array(
				'type' => 'integer',
				'length' => 4,
				'notnull' => true),
			'token' => array(
				'type' => 'text',
				'length' => 32,
				'notnull' => true)
		);
		$ilDB->createTable('gev_na_tokens', $fields);
		$ilDB->addPrimaryKey('gev_na_tokens', array('user_id'));
	}
?>


<#78>
<?php

$ilDB->manipulate("UPDATE tep_type SET title = 'AD-Begleitung' WHERE title = 'AD Begleitung'");
$ilDB->manipulate("UPDATE tep_type SET title = 'FD-Gespräch' WHERE title = 'FD Gespräch'");

?>

<#79>
<?php
	//set indizes for the history table - wow, such performance!
	$queries =  array(
		 "ALTER TABLE hist_course ADD INDEX hist_historic (hist_historic);"
		,"ALTER TABLE hist_course ADD INDEX crs_id (crs_id);"

		,"ALTER TABLE hist_user ADD INDEX hist_historic (hist_historic);"
		,"ALTER TABLE hist_user ADD INDEX user_id (user_id);"
		
		,"ALTER TABLE hist_usercoursestatus ADD INDEX hist_historic (hist_historic);"
		,"ALTER TABLE hist_usercoursestatus ADD INDEX crs_id (crs_id);"
		,"ALTER TABLE hist_usercoursestatus ADD INDEX usr_id (usr_id);"

	);
	foreach ($queries as $query) {
		try{
			$ilDB->manipulate($query);
		} catch(Exception $e){
			//pass
		}
	}


?>

<#80>
<?php
	require_once "Customizing/class.ilCustomInstaller.php";

	if(!$ilDB->tableExists('crs_matlist'))
	{
		$fields = array (
			'id' => array(
				'type' => 'integer',
				'length' => 4,
				'notnull' => true,
				'default' => 0),
			'obj_id' => array(
				'type' => 'integer',
				'length' => 4,
				'notnull' => true,
				'default' => 0),
			'quant_per_part' => array(
				'type' => 'integer',
				'length' => 4,
				'notnull' => true,
				'default' => 0),
			'quant_per_crs' => array(
				'type' => 'integer',
				'length' => 4,
				'notnull' => true,
				'default' => 0),
			'mat_number' => array(
				'type' => 'text',
				'length' => 80,
				'notnull' => false),
			'description' => array(
				'type' => 'text',
				'length' => 200,
				'notnull' => false),
			'changed_by' => array(
				'type' => 'integer',
				'length' => 4,
				'notnull' => true,
				'default' => 0),
			'changed_on' => array(
				'type' => 'integer',
				'length' => 4,
				'notnull' => true,
				'default' => 0),
		);
		$ilDB->createTable('crs_matlist', $fields);
		$ilDB->addPrimaryKey('crs_matlist', array('id'));
		$ilDB->createSequence('crs_matlist');
	}


	$new_crs_ops = array(
		'view_material' => array('View Material', 2910)
		,'edit_material' => array('Edit Material', 3510)
	);
	ilCustomInstaller::addRBACOps('crs', $new_crs_ops);


	$lang_data = array(
		"tab" => array("Material", "Material")
		,"add" => array("Material hinzufügen", "Add Material")
		,"download" => array("Materialliste herunterladen", "Download Material List")
		,"participants_count" => array("Anzahl/Teilnehmer", "Number/Participants")
		,"course_count" => array("Anzahl/Seminar", "Number/Course")
		,"product_id" => array("Artikelnummer", "Product Id")
		,"title" => array("Bezeichnung", "Title")
		,"updated" => array("Materialliste gespeichert.", "The material list has been updated.")
		,"delete_sure" => array("Wollen Sie wirklich die folgenden Materialien löschen?", "Are you sure you want to delete the following materials?")
		,"deleted" => array("Materialien wurden gelöscht.", "Materials have been deleted.")
		// xls
		,"xls_title" => array("Titel", "Title")
		,"xls_subtitle" => array("Untertitel", "Subtitle")
		,"xls_custom_id" => array("Nummer der Maßnahme", "Id of Training")
		,"xls_amount_participants" => array("Anzahl Teilnehmer", "Number of Participants")
		,"xls_date_info" => array("Datum", "Period")
		,"xls_trainer" => array("Trainer", "Trainer")
		,"xls_venue_info" => array("Veranstaltungsort", "Venue")
		,"xls_contact" => array("Bei Rückfragen", "Contact")
		,"xls_creation_date" => array("Datum der Erstellung", "Date of Creation")	
		,"xls_list_header" => array("Materialliste", "Material List")	
		,"xls_list_general_header" => array("Generelle Angaben zum Training", "General Info about Training")	
		,"xls_list_item_header" => array("Materialien", "Materials")	
	);
	ilCustomInstaller::addLangData("matlist", array("de", "en"), $lang_data, "patch generali - material list");

	ilCustomInstaller::reloadStructure();
?>

<#81>
<?php
	//more fields in history
	if(!$ilDB->tableColumnExists('hist_user', "is_active")){
		$ilDB->addTableColumn('hist_user', "is_active", array(
			'type' => 'integer',
			'length' => 1,
			'notnull' => false,
			'default' => 0
			)
		);	
	}
	if(!$ilDB->tableColumnExists('hist_course', "is_online")){
		$ilDB->addTableColumn('hist_course', "is_online", array(
			'type' => 'integer',
			'length' => 1,
			'notnull' => false,
			'default' => 0
			)
		);	
	}
?>

<#82>
<?php
	//deadline fields in course history
	$deadlines = array(
		'dl_invitation',
		'dl_storno',
		'dl_booking',
		'dl_waitinglist'
	);

	foreach ($deadlines as $deadline) {
		if(!$ilDB->tableColumnExists('hist_course', $deadline)){
			$ilDB->addTableColumn('hist_course', $deadline, array(
				'type' => 'integer',
				'length' => 3,
				'notnull' => false,
				)
			);	
		}
	}
?>

<#83>
<?php

	// calendar entry weight
	if(!$ilDB->tableColumnExists('cal_entries', 'entry_weight'))
	{
		$ilDB->addTableColumn('cal_entries', 'entry_weight', 
			array(
				'type' => 'integer', 
				'length' => 1, 
				'notnull' => false, 
				'default' => ''
		));			
	}
	
	// operation day weight
	if(!$ilDB->tableColumnExists('tep_op_days', 'weight'))
	{
		$ilDB->addTableColumn('tep_op_days', 'weight', 
			array(
				'type' => 'integer', 
				'length' => 1, 
				'notnull' => false, 
				'default' => ''
		));			
	}

?>

<#84>
<?php
	if(!$ilDB->tableExists('hist_tep_individ_days'))
	{
		$fields = array (
			'id' => array(
				'type' => 'integer',
				'length' => 4,
				'notnull' => true,
				'default' => 0),
			'day' => array(
				'type' => 'date',
				'notnull' => true
				),
			'start_time' => array(
				'type' => 'text',
				'length' => 5,
				'notnull' => false
				),
			'end_time' => array(
				'type' => 'text',
				'length' => 5,
				'notnull' => false
				),
			'weight' => array(
				'type' => 'integer',
				'length' => 1,
				'notnull' => true
				)
		);
		$ilDB->createTable('hist_tep_individ_days', $fields);
		$ilDB->addPrimaryKey('hist_tep_individ_days', array('id', 'day'));
		$ilDB->createSequence('hist_tep_individ_days');
	}

?>

<#85>
<?php

	// calendar entry weight
	if(!$ilDB->tableColumnExists('cal_entries', 'orgu_id'))
	{
		$ilDB->addTableColumn('cal_entries', 'orgu_id', 
			array(
				'type' => 'integer', 
				'length' => 4, 
				'notnull' => false, 
				'default' => null
		));			
	}

?>

<#86>
<?php

	if (!$ilDB->tableColumnExists("hist_tep", "orgu_title")) {
		$ilDB->addTableColumn('hist_tep', 'orgu_title', 
			array(
				'type' => 'text', 
				'length' => 255, 
				'notnull' => true, 
				'default' => "-empty-"
		));
	}

?>

<#87>
<?php
	$ilCtrlStructureReader->getStructure();
?>

<#88>
<?php
	require_once "Customizing/class.ilCustomInstaller.php";
	ilCustomInstaller::maybeInitClientIni();
	ilCustomInstaller::maybeInitPluginAdmin();
	ilCustomInstaller::maybeInitObjDefinition();
	ilCustomInstaller::maybeInitAppEventHandler();
	ilCustomInstaller::maybeInitTree();
	ilCustomInstaller::maybeInitRBAC();
	ilCustomInstaller::maybeInitObjDataCache();
	ilCustomInstaller::maybeInitUserToRoot();

	require_once "Services/GEV/Utils/classes/class.gevOrgUnitUtils.php";
	require_once("Modules/OrgUnit/classes/class.ilObjOrgUnit.php");

	$ref_id = ilObjOrgUnit::getRootOrgRefId();

	gevOrgUnitUtils::grantPermissionsRecursivelyFor($ref_id, "Campus-Manager",
			array( "visible"
				 , "read"
				 , "copy"
				 , "write"
				 , "delete"
				 , "edit_permission"
				 , "create_orgu"
				 ));
?>

<#89>
<?php
	require_once "Customizing/class.ilCustomInstaller.php";
	ilCustomInstaller::maybeInitClientIni();
	ilCustomInstaller::maybeInitPluginAdmin();
	ilCustomInstaller::maybeInitObjDefinition();
	ilCustomInstaller::maybeInitAppEventHandler();
	ilCustomInstaller::maybeInitTree();
	ilCustomInstaller::maybeInitRBAC();
	ilCustomInstaller::maybeInitObjDataCache();
	ilCustomInstaller::maybeInitUserToRoot();

	require_once "Services/GEV/Utils/classes/class.gevOrgUnitUtils.php";
	$ref_id = 3412;
	
	gevOrgUnitUtils::grantPermissionsRecursivelyFor($ref_id, "superior",
			array( 'view_employee_bookings'
				 , 'view_employee_bookings_rcrsv'
				 , 'book_employees'
				 , 'book_employees_rcrsv'
				 , 'cancel_employee_bookings'
				 , 'cancel_employee_bookings_rcrsv'
				 ));
?>


<#90>
<?php
// init helper class
require_once "Customizing/class.ilCustomInstaller.php";

ilCustomInstaller::initPluginEnv();
ilCustomInstaller::activatePlugin(IL_COMP_SERVICE, "User", "udfc", "GEVUserData");
?>

<#91>
<?php
	$res = $ilDB->query("SELECT DISTINCT oref.ref_id "
						."  FROM object_data od "
						."  JOIN object_reference oref ON oref.obj_id = od.obj_id "
						." WHERE import_id = 'Exit'"
						."   AND oref.deleted IS NULL"
						."   AND od.type = 'orgu'"
						);
	
	require_once("Services/GEV/Utils/classes/class.gevSettings.php");
	
	if ($rec = $ilDB->fetchAssoc($res)) {
		gevSettings::getInstance()->setOrgUnitExited($rec["ref_id"]);
	}
	else {
		die("Could not find orgu with import id 'Exit'.");
	}
?>

<#92>
<?php
	$ilCtrlStructureReader->getStructure();
?>

<#93>
<?php

require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");

// Make label for booking and participation status internationalizable
$ilDB->manipulate("UPDATE hist_usercoursestatus "
				 ."   SET booking_status = '".gevCourseUtils::LABEL_BOOKED."'"
				 ." WHERE booking_status = 'gebucht'");
$ilDB->manipulate("UPDATE hist_usercoursestatus "
				 ."   SET booking_status = '".gevCourseUtils::LABEL_WAITING."'"
				 ." WHERE booking_status = 'auf Warteliste'");
$ilDB->manipulate("UPDATE hist_usercoursestatus "
				 ."   SET booking_status = '".gevCourseUtils::LABEL_CANCELLED_WITH_COSTS."'"
				 ." WHERE booking_status = 'kostenpflichtig storniert'");
$ilDB->manipulate("UPDATE hist_usercoursestatus "
				 ."   SET booking_status = '".gevCourseUtils::LABEL_CANCELLED_WITHOUT_COSTS."'"
				 ." WHERE booking_status = 'kostenfrei storniert'");
$ilDB->manipulate("UPDATE hist_usercoursestatus "
				 ."   SET participation_status = '".gevCourseUtils::LABEL_NOT_SET."'"
				 ." WHERE participation_status = 'nicht gesetzt'");
$ilDB->manipulate("UPDATE hist_usercoursestatus "
				 ."   SET participation_status = '".gevCourseUtils::LABEL_SUCCESSFUL."'"
				 ." WHERE participation_status = 'teilgenommen'");
$ilDB->manipulate("UPDATE hist_usercoursestatus "
				 ."   SET participation_status = '".gevCourseUtils::LABEL_ABSENT_EXCUSED."'"
				 ." WHERE participation_status = 'fehlt entschuldigt'");
$ilDB->manipulate("UPDATE hist_usercoursestatus "
				 ."   SET participation_status = '".gevCourseUtils::LABEL_ABSENT_NOT_EXCUSED."'"
				 ." WHERE participation_status = 'fehlt ohne Absage'");

?>

<#94>
<?php

require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");

// Make labels for function at course internationalizable
$ilDB->manipulate("UPDATE hist_usercoursestatus "
				 ."   SET function = '".gevCourseUtils::LABEL_MEMBER."'"
				 ." WHERE function IN ('Member', 'Mitglied', '??')");
$ilDB->manipulate("UPDATE hist_usercoursestatus "
				 ."   SET function = '".gevCourseUtils::LABEL_TUTOR."'"
				 ." WHERE function IN ('Tutor', 'Trainer')");
$ilDB->manipulate("UPDATE hist_usercoursestatus "
				 ."   SET function = '".gevCourseUtils::LABEL_ADMIN."'"
				 ." WHERE function IN ('Trainingsbetreuer', 'Administrator', 'Training administrator')");

?>

<#95>
<?php

require_once "Customizing/class.ilCustomInstaller.php";
ilCustomInstaller::maybeInitClientIni();
ilCustomInstaller::maybeInitPluginAdmin();
ilCustomInstaller::maybeInitObjDefinition();
ilCustomInstaller::maybeInitAppEventHandler();
ilCustomInstaller::maybeInitTree();
ilCustomInstaller::maybeInitRBAC();
ilCustomInstaller::maybeInitObjDataCache();
ilCustomInstaller::maybeInitUserToRoot();
ilCustomInstaller::maybeInitSettings();

require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");

$role = "CAMPUS-Manager";
$permissions = array( "visible"
					, "read"
					, "view_bookings"
					, "book_users"
					, "cancel_bookings"
					, "view_participation_status"
					, "set_participation_status"
					, "review_participation_status"
					, "view_own_accomodations"
					, "set_own_accomodations"
					, "view_others_accomodations"
					, "set_others_accomodations"
					, "edit_learning_progress"
					, "copy"
					, "write"
					, "delete"
					, "edit_permission"
					, "create_bibl"
					, "create_blog"
					, "create_book"
					, "create_cat"
					, "create_catr"
					, "create_chtr"
					, "create_cld"
					, "create_crs"
					, "create_crsr"
					, "create_dbk"
					, "create_dcl"
					, "create_exc"
					, "create_feed"
					, "create_file"
					, "create_fold"
					, "create_frm"
					, "create_glo"
					, "create_grp"
					, "create_htlm"
					, "create_icla"
					, "create_icrs"
					, "create_itgr"
					, "create_lm"
					, "create_mcst"
					, "create_mep"
					, "create_poll"
					, "create_prtt"
					, "create_qpl"
					, "create_rcrs"
					, "create_role"
					, "create_rolt"
					, "create_sahs"
					, "create_sess"
					, "create_spl"
					, "create_svy"
					, "create_tst"
					, "create_webr"
					, "create_wiki"
					);

gevCourseUtils::grantPermissionsForAllCoursesBelow(3763, $role, $permissions);
gevCourseUtils::grantPermissionsForAllCoursesBelow(3759, $role, $permissions);
gevCourseUtils::grantPermissionsForAllCoursesBelow(3880, $role, $permissions);
gevCourseUtils::grantPermissionsForAllCoursesBelow(3986, $role, $permissions);

?>

<#96>
<?php

require_once "Customizing/class.ilCustomInstaller.php";
ilCustomInstaller::maybeInitClientIni();
ilCustomInstaller::maybeInitPluginAdmin();
ilCustomInstaller::maybeInitObjDefinition();
ilCustomInstaller::maybeInitAppEventHandler();
ilCustomInstaller::maybeInitTree();
ilCustomInstaller::maybeInitRBAC();
ilCustomInstaller::maybeInitObjDataCache();
ilCustomInstaller::maybeInitUserToRoot();
ilCustomInstaller::maybeInitSettings();

require_once("Services/GEV/Utils/classes/class.gevRoleUtils.php");
$role_utils = gevRoleUtils::getInstance();

$role = "CAMPUS-Manager";

$obj_type = "file";
$perms =   array( "visible"
				, "read"
				, "copy"
				, "write"
				, "delete"
				, "edit_permission"
				);

$role_utils->grantPermissionsForAllObjectsBelow($obj_type, 3763, $role, $perms);
$role_utils->grantPermissionsForAllObjectsBelow($obj_type, 3759, $role, $perms);
$role_utils->grantPermissionsForAllObjectsBelow($obj_type, 3880, $role, $perms);
$role_utils->grantPermissionsForAllObjectsBelow($obj_type, 3986, $role, $perms);


$obj_type = "qpl";
$perms =   array( "read"
				, "copy"
				, "write"
				, "delete"
				, "edit_permission"
				);

$role_utils->grantPermissionsForAllObjectsBelow($obj_type, 3763, $role, $perms);
$role_utils->grantPermissionsForAllObjectsBelow($obj_type, 3759, $role, $perms);
$role_utils->grantPermissionsForAllObjectsBelow($obj_type, 3880, $role, $perms);
$role_utils->grantPermissionsForAllObjectsBelow($obj_type, 3986, $role, $perms);


$obj_type = "spl";
$perms =   array( "visible"
				, "read"
				, "copy"
				, "write"
				, "delete"
				, "edit_permission"
				);

$role_utils->grantPermissionsForAllObjectsBelow($obj_type, 3763, $role, $perms);
$role_utils->grantPermissionsForAllObjectsBelow($obj_type, 3759, $role, $perms);
$role_utils->grantPermissionsForAllObjectsBelow($obj_type, 3880, $role, $perms);
$role_utils->grantPermissionsForAllObjectsBelow($obj_type, 3986, $role, $perms);


$obj_type = "grp";
$perms =   array( "visible"
				, "join"
				, "leave"
				, "read"
				, "edit_learning_progress"
				, "copy"
				, "write"
				, "delete"
				, "edit_permission"
				, "create_catr"
				, "create_crsr"
				, "create_fold"
				, "create_itgr"
				, "create_grp"
				, "create_file"
				, "create_webr"
				, "create_sahs"
				, "create_exc"
				, "create_tst"
				, "create_svy"
				, "create_qpl"
				, "create_spl"
				);

$role_utils->grantPermissionsForAllObjectsBelow($obj_type, 3763, $role, $perms);
$role_utils->grantPermissionsForAllObjectsBelow($obj_type, 3759, $role, $perms);
$role_utils->grantPermissionsForAllObjectsBelow($obj_type, 3880, $role, $perms);
$role_utils->grantPermissionsForAllObjectsBelow($obj_type, 3986, $role, $perms);

$obj_type = "catr";
$perms =   array( "visible"
				, "write"
				, "delete"
				, "edit_permission"
				);

$role_utils->grantPermissionsForAllObjectsBelow($obj_type, 3763, $role, $perms);
$role_utils->grantPermissionsForAllObjectsBelow($obj_type, 3759, $role, $perms);
$role_utils->grantPermissionsForAllObjectsBelow($obj_type, 3880, $role, $perms);
$role_utils->grantPermissionsForAllObjectsBelow($obj_type, 3986, $role, $perms);

$obj_type = "cat";
$perms =   array( "visible"
				, "read"
				, "copy"
				, "write"
				, "delete"
				, "edit_permission"
				, "create_cat"
				, "create_catr"
				, "create_crs"
				, "create_crsr"
				, "create_itgr"
				, "create_grp"
				, "create_file"
				, "create_webr"
				, "create_sahs"
				, "create_exc"
				, "create_tst"
				, "create_svy"
				, "create_qpl"
				, "create_spl"
				);

$role_utils->grantPermissionsForAllObjectsBelow($obj_type, 3763, $role, $perms);
$role_utils->grantPermissionsForAllObjectsBelow($obj_type, 3759, $role, $perms);
$role_utils->grantPermissionsForAllObjectsBelow($obj_type, 3880, $role, $perms);
$role_utils->grantPermissionsForAllObjectsBelow($obj_type, 3986, $role, $perms);

$obj_type = "itgr";
$perms =   array( "visible"
				, "read"
				, "copy"
				, "write"
				, "delete"
				, "edit_permission"
				);

$role_utils->grantPermissionsForAllObjectsBelow($obj_type, 3763, $role, $perms);
$role_utils->grantPermissionsForAllObjectsBelow($obj_type, 3759, $role, $perms);
$role_utils->grantPermissionsForAllObjectsBelow($obj_type, 3880, $role, $perms);
$role_utils->grantPermissionsForAllObjectsBelow($obj_type, 3986, $role, $perms);


$obj_type = "fold";
$perms =   array( "visible"
				, "read"
				, "edit_learning_progress"
				, "copy"
				, "write"
				, "delete"
				, "edit_permission"
				, "create_fold"
				, "create_itgr"
				, "create_grp"
				, "create_file"
				, "create_webr"
				, "create_sahs"
				, "create_exc"
				, "create_tst"
				, "create_svy"
				, "create_qpl"
				, "create_spl"
				);

$role_utils->grantPermissionsForAllObjectsBelow($obj_type, 3763, $role, $perms);
$role_utils->grantPermissionsForAllObjectsBelow($obj_type, 3759, $role, $perms);
$role_utils->grantPermissionsForAllObjectsBelow($obj_type, 3880, $role, $perms);
$role_utils->grantPermissionsForAllObjectsBelow($obj_type, 3986, $role, $perms);


$obj_type = "sahs";
$perms =   array( "visible"
				, "read"
				, "edit_learning_progress"
				, "copy"
				, "write"
				, "delete"
				, "edit_permission"
				);

$role_utils->grantPermissionsForAllObjectsBelow($obj_type, 3763, $role, $perms);
$role_utils->grantPermissionsForAllObjectsBelow($obj_type, 3759, $role, $perms);
$role_utils->grantPermissionsForAllObjectsBelow($obj_type, 3880, $role, $perms);
$role_utils->grantPermissionsForAllObjectsBelow($obj_type, 3986, $role, $perms);


$obj_type = "tst";
$perms =   array( "visible"
				, "read"
				, "edit_learning_progress"
				, "copy"
				, "write"
				, "tst_statistics"
				, "delete"
				, "edit_permission"
				);

$role_utils->grantPermissionsForAllObjectsBelow($obj_type, 3763, $role, $perms);
$role_utils->grantPermissionsForAllObjectsBelow($obj_type, 3759, $role, $perms);
$role_utils->grantPermissionsForAllObjectsBelow($obj_type, 3880, $role, $perms);
$role_utils->grantPermissionsForAllObjectsBelow($obj_type, 3986, $role, $perms);


$obj_type = "crsr";
$perms =   array( "visible"
				, "copy"
				, "write"
				, "delete"
				, "edit_permission"
				);

$role_utils->grantPermissionsForAllObjectsBelow($obj_type, 3763, $role, $perms);
$role_utils->grantPermissionsForAllObjectsBelow($obj_type, 3759, $role, $perms);
$role_utils->grantPermissionsForAllObjectsBelow($obj_type, 3880, $role, $perms);
$role_utils->grantPermissionsForAllObjectsBelow($obj_type, 3986, $role, $perms);


$obj_type = "svy";
$perms =   array( "visible"
				, "read"
				, "invite"
				, "copy"
				, "write"
				, "delete"
				, "edit_permission"
				);

$role_utils->grantPermissionsForAllObjectsBelow($obj_type, 3763, $role, $perms);
$role_utils->grantPermissionsForAllObjectsBelow($obj_type, 3759, $role, $perms);
$role_utils->grantPermissionsForAllObjectsBelow($obj_type, 3880, $role, $perms);
$role_utils->grantPermissionsForAllObjectsBelow($obj_type, 3986, $role, $perms);


$obj_type = "webr";
$perms =   array( "visible"
				, "read"
				, "copy"
				, "write"
				, "delete"
				, "edit_permission"
				);

$role_utils->grantPermissionsForAllObjectsBelow($obj_type, 3763, $role, $perms);
$role_utils->grantPermissionsForAllObjectsBelow($obj_type, 3759, $role, $perms);
$role_utils->grantPermissionsForAllObjectsBelow($obj_type, 3880, $role, $perms);
$role_utils->grantPermissionsForAllObjectsBelow($obj_type, 3986, $role, $perms);


$obj_type = "exc";
$perms =   array( "visible"
				, "read"
				, "edit_learning_progress"
				, "copy"
				, "write"
				, "delete"
				, "edit_permission"
				);

$role_utils->grantPermissionsForAllObjectsBelow($obj_type, 3763, $role, $perms);
$role_utils->grantPermissionsForAllObjectsBelow($obj_type, 3759, $role, $perms);
$role_utils->grantPermissionsForAllObjectsBelow($obj_type, 3880, $role, $perms);
$role_utils->grantPermissionsForAllObjectsBelow($obj_type, 3986, $role, $perms);


?>

<#97>
<?php

require_once "Customizing/class.ilCustomInstaller.php";
ilCustomInstaller::initPluginEnv();
ilCustomInstaller::activatePlugin(IL_COMP_SERVICE, "AdvancedMetaData", "amdc", "CourseAMD");

?>

<#98>
<?php
	require_once("Services/Administration/classes/class.ilSetting.php");
	$set = new ilSetting();
	$set->set("enable_trash",0);
?>

<#99>
<?php
	if(!$ilDB->tableExists('hist_userorgu')) {
		$fields = array(
			'row_id' => array(
				'type' => 'integer',
				'length' => 4,
				'notnull' => true),
			'hist_version' => array(
				'type' => 'integer',
				'length' => 4,
				'notnull' => true,
				'default' => 1),
			'hist_historic' => array(
				'type' => 'integer',
				'length' => 4,
				'notnull' => true,
				'default' => 0),
			'creator_user_id' => array(
				'type' => 'integer',
				'length' => 4,
				'notnull' => true),
			'created_ts' => array(
				'type' => 'integer',
				'length' => 4,
				'notnull' => true,
				'default' => 0),
			'usr_id' => array(
				'type' => 'integer',
				'length' => 4,
				'notnull' => true),
			'orgu_id' => array(
				'type' => 'integer',
				'length' => 4,
				'notnull' => true),
			'rol_id' => array(
				'type' => 'integer' ,
				'length' => 4 ,
				'notnull' => true),
			'orgu_title' => array(
				'type' => 'text',
				'length' => 40 ,
				'notnull' => false),
			'org_unit_above1' => array(
				'type' => 'text',
				'length' => 40 ,
				'notnull' => false),
			'org_unit_above2' => array(
				'type' => 'text',
				'length' => 40 ,
				'notnull' => false),
			'rol_title' => array(
				'type' => 'text',
				'length' => 40 ,
				'notnull' => false),
			'action' => array(
				'type' => 'integer',
				'length' => 1 ,
				'notnull' => true)			
		);
		$ilDB->createTable('hist_userorgu', $fields);
		$ilDB->addPrimaryKey('hist_userorgu', array('row_id'));
		$ilDB->createSequence('hist_userorgu');

		$ilCtrlStructureReader->getStructure();
	}
?>

<#100>
<?php
	$queries =  array(
		 "ALTER TABLE hist_userorgu ADD INDEX hist_historic (hist_historic);"
		,"ALTER TABLE hist_userorgu ADD INDEX orgu_id (orgu_id);"
		,"ALTER TABLE hist_userorgu ADD INDEX usr_id (usr_id);"

	);
	foreach ($queries as $query) {
		try{
			$ilDB->manipulate($query);
		} catch(Exception $e){
			//pass
		}
	}


?>

<#101>
<?php
$ilCtrlStructureReader->getStructure();
?>

<#102>
<?php
require_once "Customizing/class.ilCustomInstaller.php";
ilCustomInstaller::initPluginEnv();
ilCustomInstaller::activatePlugin(IL_COMP_SERVICE, "AdvancedMetaData", "amdc", "CourseAMD");
?>

<#103>
<?php
if(!$ilDB->tableColumnExists('hist_course', 'reason_for_training')){
	$ilDB->addTableColumn('hist_course', 'reason_for_training', array(
		'type' => 'text',
		'length' => 255,
		'notnull' => false
		)
	);
}
?>

<#104>
<?php
$ilCtrlStructureReader->getStructure();
?>

<#105>
<?php
require_once("Services/GEV/Desktop/classes/EffectivenessAnalysis/class.gevEffectivenessAnalysisDB.php");
$eff_analysis_db = new gevEffectivenessAnalysisDB($ilDB);
$eff_analysis_db->createTable();
?>

<#106>
<?php
	$fields = array("target_groups"=>array("type"=>"clob", "notnull"=>false)
		, "training_number"=>array("type"=>"text", "length"=>64, "notnull"=>false)
		, "objectives_benefits"=>array("type"=>"clob", "notnull"=>false)
		, "training_topics"=>array("type"=>"clob", "notnull"=>false)
		, "language"=>array("type"=>"text", "length"=>64, "notnull"=>false)
	);

	foreach ($fields as $name => $values) {
		if(!$ilDB->tableColumnExists('hist_course', $name)){
			$ilDB->addTableColumn('hist_course', $name, $values);
	}
}
?>

<#107>
<?php
$ilCtrlStructureReader->getStructure();
?>

<#108>
<?php
	if(!$ilDB->tableExists('eff_analysis_due_date')) {
		$fields = array(
			'crs_id' => array(
				'type' => 'integer',
				'length' => 4,
				'notnull' => true),
			'user_id' => array(
				'type' => 'integer',
				'length' => 4,
				'notnull' => true),
			'due_date' => array(
				'type' => 'date',
				'notnull' => true)
		);
		$ilDB->createTable('eff_analysis_due_date', $fields);
		$ilDB->addPrimaryKey('eff_analysis_due_date', array('crs_id', 'user_id'));
	}
?>