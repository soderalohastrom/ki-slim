<?php
class Templates {
	
	function get_template_data($TemplateID) {
		global $DB;
		$sql = "SELECT * FROM EmailTemplates WHERE EmailTemplates_id='$TemplateID'";
		$data = $DB->get_single_result($sql);
		return $data;
	}

	function get_template_categories() {
		global $DB;
		$sql = "SELECT * FROM EmailTemplateCategories ORDER BY TemplateCategories_name";
		$data = $DB->get_multi_result($sql);
		return $data;
	}

	function get_templates($CategoryID, $UserID=-1) {
		global $DB;
		$sql = "SELECT 
			EmailTemplates.*,
			ifNull(TemplateCategories_id, '0') AS TemplateCategories_id,
			ifNull(TemplateCategories_name, 'Uncategorized') AS TemplateCategories_name
			FROM EmailTemplates
			LEFT JOIN EmailTemplateCategories ON EmailTemplates_category = TemplateCategories_id
			AND TemplateCategories_id ='$CategoryID'";
		if($UserID != -1) {
			$sql .= " WHERE EmailTemplates_user='$UserID'";
		}
		$sql .= " ORDER BY EmailTemplates_title";
		$data = $DB->get_multi_result($sql);
		return $data;
	}

}
?>