<?php
if($_POST['submitted'] == 1)
{
	include_once("assets/vendors/modules/htmlpurifier-4.10.0/library/HTMLPurifier.auto.php");
	$config = HTMLPurifier_Config::createDefault();
	$purifier = new HTMLPurifier($config);
	
    //validate form submission
    if(trim($_POST['MarketingLists_name']) == '')
    {
        $form_errors['MarketingLists_name'] = 'Please enter a List Name.';
    }
    
    if(!isset($_POST['MarketingLists_active']))
    {
        $form_errors['MarketingLists_active'] = 'Please select a Status.';
    }
    
    if($_POST['list_groups'] == '')
    {
        $form_errors['groups'] = 'You must select at least one Group.';
    }
    
    if(empty($form_errors))
    {
        //update list
        if($_POST['list_id'] > 0)
        {
			//check to make sure a list with the same name doesn't exist
			$ck_query = "SELECT * FROM MarketingLists WHERE MarketingLists_name='".$DB->mysqli->escape_string($purifier->purify($_POST['MarketingLists_name']))."' AND MarketingLists_id != '".$DB->mysqli->escape_string($_POST['list_id'])."'";
			$ck_send = $DB->mysqli->query($ck_query);
			$ck_found = $ck_send->num_rows;
			if ($ck_found == 0) {
				$sql = "
					UPDATE
						MarketingLists 
					SET
						MarketingLists_groups = '".$DB->mysqli->escape_string($purifier->purify($_POST['list_groups']))."',
						MarketingLists_name = '".$DB->mysqli->escape_string($purifier->purify($_POST['MarketingLists_name']))."',
						MarketingLists_active = '".$DB->mysqli->escape_string($_POST['MarketingLists_active'])."',
						MarketingLists_category = '".$DB->mysqli->escape_string($_POST['MarketingLists_category'])."' 						
					WHERE
						MarketingLists_id = '".$DB->mysqli->escape_string($_POST['list_id'])."'
					LIMIT 1";
					
				$query = $DB->mysqli->query($sql) or die('Line: '.__LINE__ .' - '.$DB->mysqli->error);
				$success = 'The marketing list has been updated.';
			} else {
				$failure = 'A marketing list with the name you chose already exists.';				
			}
        }
        //new list
        else
        {
           //check to make sure a list with the same name doesn't exist
			$ck_query = "SELECT * FROM MarketingLists WHERE MarketingLists_name='".$DB->mysqli->escape_string($purifier->purify($_POST['MarketingLists_name']))."'";
			$ck_send = $DB->mysqli->query($ck_query, $db_link);
			$ck_found = $ck_send->num_rows;
			if ($ck_found == 0) {
				$sql = "
					INSERT INTO
						MarketingLists
					(
						MarketingLists_groups,
						MarketingLists_name,
						MarketingLists_active,
						MarketingLists_category,
						MarketingLists_dateCreated,
						MarketingLists_createdBy
					)
					VALUES
					(
						'".$DB->mysqli->escape_string($purifier->purify($_POST['list_groups']))."',
						'".$DB->mysqli->escape_string($purifier->purify($_POST['MarketingLists_name']))."',
						'".$DB->mysqli->escape_string($_POST['MarketingLists_active'])."',
						'".$DB->mysqli->escape_string($_POST['MarketingLists_category'])."',
						'".$DB->mysqli->escape_string(time())."',
						".$_SESSION['system_user_id']."
					)";
				$query = $DB->mysqli->query($sql) or die('Line: '.__LINE__ .' - '.$DB->mysqli->error);				
				$list_id = $DB->mysqli->insert_id;
				$success = 'The marketing list has been created.';
			} else {
				$failure = 'A marketing list with the name you chose already exists.';				
			}
        }
    }
}
?>