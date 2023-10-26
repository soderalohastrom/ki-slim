<?php
if($_POST['submitted'] == 1)
{
	include_once("assets/vendors/modules/htmlpurifier-4.10.0/library/HTMLPurifier.auto.php");
	$config = HTMLPurifier_Config::createDefault();
	$purifier = new HTMLPurifier($config);
    $success = true;
	
    //validate form submission
    if(strlen($_POST['MarketingDeployments_name']) < 1)
    {
        $form_errors['MarketingDeployments_name'] = 'Please enter a Deployment Name.';
    }
    
    if(strlen($_POST['MarketingDeployments_subject']) < 1)
    {
        $form_errors['MarketingDeployments_subject'] = 'Please enter an Email Subject.';
    }
    
	$email_from = $_POST['MarketingDeployments_fromEmail'].'@'.$_POST['MarketingDeployments_fromEmailDomain'];
    if(strlen($_POST['MarketingDeployments_fromEmail']) < 1 || !$MKG->valid_email($email_from))
    {
        $form_errors['MarketingDeployments_fromEmail'] = 'Please enter a valid From Address.';
    }
    
    if(strlen($_POST['MarketingDeployments_fromName']) < 1)
    {
        $form_errors['MarketingDeployments_fromName'] = 'Please enter a From Name.';
    }
    
    if((strlen($_POST['MarketingDeployments_replyTo']) < 1 || !$MKG->valid_email($_POST['MarketingDeployments_replyTo'])) && $MKG->esp !== 'local')
    {
        $form_errors['EmailReplyTo'] = 'Please enter a valid Reply To Address.';
    }
    
    if(strlen($_POST['deploy_lists']) < 1)
    {
        $form_errors['lists'] = 'You must select at least one Marketing List.';
    }
    
    if(strlen($_POST['msg_body_html']) < 1)
    {
        $form_errors['msg_body_html'] = 'The HTML Message Body contains no content.';
    }
    
    if(strlen($_POST['msg_body_plain']) < 1)
    {
        $form_errors['msg_body_plain'] = 'The Plain Text Message Body contains no content.';
    }
	
    if(empty($form_errors))
    {
        //make sure the deployment is actually still Pending
        $sql   = "SELECT MarketingDeployments_status FROM MarketingDeployments WHERE MarketingDeployments_id = '".$DB->mysqli->escape_string($_POST['deploy_id'])."' LIMIT 1";
        $d = $DB->get_single_result($sql);
        
        if($d['MarketingDeployments_status'] == 'Pending')
        {
            //update deployment
            if($_POST['deploy_id'] > 0)
            {
                $sql = "
                    UPDATE 
                        MarketingDeployments
                    SET
                        MarketingDeployments_name     	= '".$DB->mysqli->escape_string($purifier->purify($_POST['MarketingDeployments_name']))."',
                        MarketingDeployments_subject  	= '".$DB->mysqli->escape_string($purifier->purify($_POST['MarketingDeployments_subject']))."',
                        MarketingDeployments_fromEmail  = '".$DB->mysqli->escape_string($purifier->purify($email_from))."',
                        MarketingDeployments_fromName   = '".$DB->mysqli->escape_string($purifier->purify($_POST['MarketingDeployments_fromName']))."',
                        MarketingDeployments_replyTo    = '".$DB->mysqli->escape_string($purifier->purify($_POST['MarketingDeployments_replyTo']))."',
                        MarketingDeployments_bodyHTML   = '".$DB->mysqli->escape_string($purifier->purify($_POST['msg_body_html']))."',
                        MarketingDeployments_bodyText   = '".$DB->mysqli->escape_string($purifier->purify($_POST['msg_body_plain']))."'
                    WHERE
                        MarketingDeployments_id = '".$DB->mysqli->escape_string($_POST['deploy_id'])."'
                    LIMIT 1";
                    
                $query = $DB->mysqli->query($sql) or die('Line: '.__LINE__ .' - '.$DB->mysqli->error);				
            }
            else
            {
                $success = false;
            }
            
            $deploy_id = $_POST['deploy_id'];
        }
        //new deployment
        else
        {
            $sql = "
                INSERT INTO
                    MarketingDeployments
                (
                    MarketingDeployments_name,
                    MarketingDeployments_subject,
                    MarketingDeployments_fromEmail,
                    MarketingDeployments_fromName,
                    MarketingDeployments_replyTo,
                    MarketingDeployments_bodyHTML,
                    MarketingDeployments_bodyText,
                    MarketingDeployments_status,
                    MarketingDeployments_dateCreated,
                    MarketingDeployments_createdBy,
					MarketingDeployments_ESP
                )
                VALUES
                (
                    '".$DB->mysqli->escape_string($purifier->purify($_POST['MarketingDeployments_name']))."',
                    '".$DB->mysqli->escape_string($purifier->purify($_POST['MarketingDeployments_subject']))."',
                    '".$DB->mysqli->escape_string($purifier->purify($email_from))."',
                    '".$DB->mysqli->escape_string($purifier->purify($_POST['MarketingDeployments_fromName']))."',
                    '".$DB->mysqli->escape_string($purifier->purify($_POST['MarketingDeployments_replyTo']))."',
                    '".$DB->mysqli->escape_string($purifier->purify($_POST['msg_body_html']))."',
                    '".$DB->mysqli->escape_string($purifier->purify($_POST['msg_body_plain']))."',
                    'Pending',
                    '".time()."',
					'".$_SESSION['system_user_id']."',
                    '".$DB->mysqli->escape_string($MKG->esp)."'
                )"; 
                
            $query = $DB->mysqli->query($sql) or die('Line: '.__LINE__ .' - '.$DB->mysqli->error);
            
            $deploy_id = $DB->mysqli->insert_id;		
        }
		
        //reset marketing list associations
        $new_lists = explode('|', $_POST['deploy_lists']); 
        
        $delete = $DB->mysqli->query("DELETE FROM MarketingDeploymentLists WHERE Deployment_id = '".$DB->mysqli->escape_string($deploy_id)."'") or die('Line: '.__LINE__ .' - '.$DB->mysqli->error);
        
        foreach($new_lists as $list_id) {
            if(strlen($list_id) < 0 && !is_numeric($list_id)) {
                continue;
            }
            
            $query = $DB->mysqli->query("INSERT INTO MarketingDeploymentLists (Deployment_id, MarketingList_id) VALUES ('".$DB->mysqli->escape_string($deploy_id)."', '".$DB->mysqli->escape_string($list_id)."')") or die('Line: '.__LINE__ .' - '.$DB->mysqli->error);
        }
		
        if($success == false) {
            $success = '';
        } else {
            $success = ($_POST['deploy_id'] > 0) ? 'Deployment has been updated.' : 'Deployment has been created.';
        }
    } else {
        $success = '';
    }
}
?>