<?php
/*! \class Record class.backgroundcheck.php "class.backgroundcheck.php"
 *  \brief This class is for the background check functionality.
 * Ref 8232022
 */
class BackgroundCheck
{
	/* Parameters */
	public $check_results;
	public $comprehensive_results;
	public $criminal_results;
	public $legacy_results;
	public $person_id;
	public $tahoe_id;

	/*! \fn obj __constructor($DB)
		\brief BackgroundCheck class constructor.
		\param	$DB db class object
		\return null
	*/
	function __construct($DB)
	{
		$DB->connect();
		$this->db 	= 	$DB;
	}

	/*! \fn obj get_checkStatus($personID) 
		\brief get the status of a background check
		\param	$personID ID of the person being checked
		\return array
	*/
	function get_checkStatus($personID)
	{
		$sql = "SELECT * FROM BackgroundCheck_pfpro_Persons WHERE PersonID='" . $personID . "'";
		//echo $sql;
		$snd = $this->db->get_single_result($sql);
		if (isset($snd['empty_result'])) {
			$return['check'] = false;
			$return['result'] = '';
			
		} else {
			$this->tahoe_id = $snd['TahoeId'];
			$this->person_id = $personID;
			$return['check'] = true;
			if ($snd['CheckResults'] == 1) {
				$return['result'] = true;
			} else {
				$return['result'] = false;
			}
		}
		return $return;
	}

	public function check_pfpro_background_check($personID, $tahoeID)
	{
		$sql = "";
		$return = false;
		$this->db->connect();
		
		$stmt = $this->db->mysqli->prepare('SELECT b.* FROM BackgroundCheck_Persons a
		JOIN BackgroundCheck_PersonsResults b ON a.CheckId=b.CheckId
		 WHERE PersonId=?');
		 $stmt->bind_param('s', $personID);
		 $stmt->execute();
		 $result = $stmt->get_result();
		
		while($r = mysqli_fetch_assoc($result)) {
			$this->legacy_results[] = $r;
		}
		
		$stmt = $this->db->mysqli->prepare('SELECT CheckResults,CriminalResults,ComprehensiveResults
			FROM BackgroundCheck_pfpro_Persons 
			WHERE PersonId=? and TahoeId=?');

		$stmt->bind_param('ss', $personID, $tahoeID);
		$stmt->execute();
		$result = $stmt->get_result();
		$search_result =  $result->fetch_assoc();
		
		if (is_array($search_result)) {
			$this->check_results = $search_result['CheckResults'];
			$this->criminal_results = $search_result['CriminalResults'];
			$this->comprehensive_results = $search_result['ComprehensiveResults'];
			$return = true;
		}

		$stmt->close();
		$this->db->mysqli->close();

		return $return;
	}

	public function reset_pfpro_background_check($personID)
	{
		$sql = "";
		$return = false;
		$stmt = $this->db->mysqli->prepare('DELETE FROM BackgroundCheck_pfpro_Persons 
			WHERE PersonId=?');

		$stmt->bind_param('s', $personID);
		$stmt->execute();
		$result = $stmt->get_result();


		$sql = "";
		$return = false;
		$stmt = $this->db->mysqli->prepare('DELETE a,b 
			from BackgroundCheck_Persons a
			left join BackgroundCheck_PersonsResults b on a.CheckID=b.CheckID
			where  a.PersonId=?');

		$stmt->bind_param('s', $personID);
		$stmt->execute();
		$result = $stmt->get_result();

		return $result;
	}

	/*! \fn obj render_BG_check($personID) 
		\brief render the bgackground check on hte page
		\param	$personID ID of the person being checked
		\return HTML
	*/
	function render_BG_check($personID)
	{
		ob_start();
		$status = $this->get_checkStatus($personID);
		if (!$status['check']) :
?>
			<a href="#" data-toggle="modal" data-target="#backgroundCheckModal" class="btn btn-info m-btn m-btn--custom m-btn--icon m-btn--pill m-btn--air">
				<span>
					<i class="flaticon-alert-2"></i>
					<span>NOT RUN</span>
				</span>
			</a>
			<?php
		else :
			if ($status['result']) :
			?>
				<a href="/check/bgprofile.php?personId=<?php echo $this->person_id ?>&tahoeId=<?php echo $this->tahoe_id ?>" class="btn btn-success m-btn m-btn--custom m-btn--icon m-btn--pill m-btn--air">
					<span>
						<i class="flaticon-user-ok"></i>
						<span>Success</span>
					</span>
				</a>
				<script>
					$(document).ready(function(e) {
						loadBackgroundResults();
					});
				</script>
			<?php
			else :
			?>
				<a href="/check/bgprofile.php?personId=<?php echo $this->person_id ?>&tahoeId=<?php echo $this->tahoe_id ?>" class="btn btn-danger m-btn m-btn--custom m-btn--icon m-btn--pill m-btn--air">
					<span>
						<i class="flaticon-warning-2"></i>
						<span>FAILED</span>
					</span>
				</a>
		<?php
			endif;
		endif;


		$this->render_BG_modal($personID);
		$this->render_BG_script($personID);
		return ob_get_clean();
	}

	/*! \fn obj render_BG_modal($personID) 
		\brief render the bgackground check modal
		\param	$personID ID of the person being checked
		\return HTML
	*/
	function render_BG_modal($personID)
	{
		?>
		<div class="modal fade" id="backgroundCheckModal" tabindex="-1" data-backdrop="static" role="dialog" aria-labelledby="backgroundCheckModalLabel" aria-hidden="true">
			<div class="modal-dialog modal-lg" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="backgroundCheckModalLabel">Background Check</h5>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="modal-body">

						<span id="bg_check_disclaimer">
							<p>The information in this report is derived from records that may or may not be in accordance with the Fair Credit Report Act (FCRA, Public Law 91-508, Title VI). This information may not be used for insurance, or credit evaluation and if used for employment purposes or in connection with other legitimate needs it is agreed that the information is for legitimate informational purposes only. The depth of information available varies. Final verification of an individual's identity and proper use of report contents are the user's responsibility.</p>
							<p>The information in this report such as the public records and criminal records, is compiled from and processed by various third-party sources. IntegraScan does not guarantee, warrant or assume any responsibility for the accuracy of the information obtained from other sources and shall not be liable for any losses or injuries now or in the future resulting from or relating to the information provided herein.</p>
							<p class="text-center">
								<button style="background-color:red" type="button" id="btnRunBGCheck" class="btn btn-primary btn-lg" onclick='location.href="/check/bgcheck.php?personid=<?php echo ($personID); ?>"'>Run Background Check</button>
							</p>

						</span>

						
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
					</div>
			</div>
		</div>
		</div>
	<?php

	}

	/*! \fn obj render_BG_script($personID) 
		\brief render the bgackground check scripts for checking background
		\param	$personID ID of the person being checked
		\return HTML
	*/
	function render_BG_script($personID)
	{
	?>
		<script>
			function runBackgroundCheck() {
				$('#btnRunBGCheck').prop('disabled', true);

				
			}

			function loadBackgroundResults() {
				$('#bg_check_disclaimer').fadeOut(250, function() {
					$('#bg_check_result').fadeIn(250);
					$.post('/ajax/ajax.NCS.results.php', {
						PersonID: '<?php echo $personID ?>'
					}, function(data) {
						$('#bg_check_result').html(data);
					});
				});
			}

			function loadDisclaimer() {
				$('#bg_check_result').fadeOut(250, function() {
					$('#bg_check_disclaimer').fadeIn(250);
				});
			}

			function ResetErrorCheck(checkID) {
				var choice = confirm('are you sure you want to reset this check?');
				if (choice) {
					$.post('/ajax/ajax.background_check.php?action=reset', {
						'check_id': checkID
					}, function(data) {
						loadBackgroundResults();
					});
				}
			}

			function cancelForcPass() {
				$('#bg_check_review').fadeOut(250, function() {
					$('#bg_check_result').fadeIn(250);
				});
			}

			function submitForcePassForm() {
				var error = 0;
				var errorTxt = '';

				if ($('#forcePass').is(':checked')) {
					// do nothing //
				} else {
					error = 1;
					errorTxt += 'must check to force pass this offense\n';
				}

				var forcepass_reason = $('#forcePass_reason').val();
				if (forcepass_reason == '') {
					error = 1;
					errorTxt += 'must give a reason for the force pass\n';
				}

				var formData = $('#ForcePassForm').serializeArray();
				if (error == 1) {
					alert(errorTxt);
				} else {
					//alert('submit form');
					$.post('/ajax/ajax.background_check.php?action=submit', formData,
						function(data) {
							cancelForcPass();
							loadBackgroundResults();
							$('#forcePass').prop('checked', false);
							$('#forcePass_reason').val('');
						});
				}
			}

			function forcePassView(ResultID) {
				$('#CheckResultID').val(ResultID);
				$('#SystemUserID').val('<?php echo $_SESSION['system_user_id'] ?>');
				//$('#BackgroundCheck_Dialog').dialog('open');
				$('#bg_check_result').fadeOut(250, function() {
					$('#bg_check_review').fadeIn(250);

					$.get('/ajax/ajax.background_check.php', {
						'ResultCheckID': ResultID,
						'action': 'load'
					}, function(data) {
						$('#CheckID').html(data.CheckID);
						$('#Offense_Name').html(data.Offense_Name);
						$('#Offense_Code').html(data.Offense_Code);
						$('#Offense_Type').html(data.Offense_Type);
						$('#Offense_CaseNumber').html(data.Offense_CaseNumber);
						$('#Offense_State').html(data.Offense_State);
						$('#Offense_Source').html(data.Offense_Source);
						$('#Offense_Court').html(data.Offense_Court);
						$('#Offense_CourtCode').html(data.Offense_CourtCode);
						$('#Offense_Year').html(data.Offense_Year);
						$('#Offense_Disposition').html(data.Offense_Disposition);
						$('#display_date').html(data.display_date);
						$('#Offense_Sentence').html(data.Offense_Sentence);
						$('#Offense_FullName').html(data.Offense_FullName);
						$('#Offense_Race').html(data.Offense_Race);
						$('#Offense_Gender').html(data.Offense_Gender);
						$('#ForcePass').html(data.ForcePass);
						$('#ForcePass_ByUser').html(data.ForcePass_ByUser);
						$('#CheckForm').hide();
						$('#PostForcePassArea').show();

						$('#display_date').html(data.display_date);
						$('#display_user').html(data.display_user);
						$('#display_reason').html(data.display_reason);
					}, "json");
				});
			}

			function forcePassForm(ResultID) {
				$('#CheckResultID').val(ResultID);
				$('#SystemUserID').val('<?php echo $_SESSION['system_user_id'] ?>');
				//$('#BackgroundCheck_Dialog').dialog('open');
				$('#bg_check_result').fadeOut(250, function() {
					$('#bg_check_review').fadeIn(250);

					$.get('/ajax/ajax.background_check.php', {
						'ResultCheckID': ResultID,
						'action': 'load'
					}, function(data) {
						$('#CheckID').html(data.CheckID);
						$('#Offense_Name').html(data.Offense_Name);
						$('#Offense_Code').html(data.Offense_Code);
						$('#Offense_Type').html(data.Offense_Type);
						$('#Offense_CaseNumber').html(data.Offense_CaseNumber);
						$('#Offense_State').html(data.Offense_State);
						$('#Offense_Source').html(data.Offense_Source);
						$('#Offense_Court').html(data.Offense_Court);
						$('#Offense_CourtCode').html(data.Offense_CourtCode);
						$('#Offense_Year').html(data.Offense_Year);
						$('#Offense_Disposition').html(data.Offense_Disposition);
						$('#display_date').html(data.display_date);
						$('#Offense_Sentence').html(data.Offense_Sentence);
						$('#Offense_FullName').html(data.Offense_FullName);
						$('#Offense_Race').html(data.Offense_Race);
						$('#Offense_Gender').html(data.Offense_Gender);
						$('#ForcePass').html(data.ForcePass);
						$('#ForcePass_ByUser').html(data.ForcePass_ByUser);
						$('#CheckForm').show();
						$('#PostForcePassArea').hide();
					}, "json");

				});
			}
		</script>
<?php
	}

	/*! \fn obj get_search_parameters($personID) 
		\brief get search parameters for a background check
		\param	$personID ID of the person being checked
		\return HTML
	*/
	function get_search_parameters($personID)
	{
		$sql = "SELECT FirstName, LastName, DateOfBirth FROM Persons WHERE Person_id = '" . $personID . "'";
		$snd = $this->db->get_single_result($sql);
		if (isset($snd['empty_result'])) {
			$data = array('FirstName' => '', 'LastName' => '', 'DateOfBirth' => '');
		} else {
			$data = $snd;
		}
		return $data;
	}

	


	function update_pfpro_background_check($UserId, $PersonId, $TahoeID, $PassFail, $ErrorMsg, $ComprehensiveResults, $CriminalResults)
	{
		$CheckIsComplete = '1';
		$ErrorMsg = 'TahoeId: ' . $TahoeID;
		$CheckDate = time();
		$ComprehensiveResults = json_encode($ComprehensiveResults);
		$CriminalResults = json_encode($CriminalResults);
		$this->check_results = $PassFail;
		$this->criminal_results = $CriminalResults;
		$this->comprehensive_results = $ComprehensiveResults;
		
		$this->db->connect();

		$PassFail = ($PassFail == 'true') ? 1 : 0;
	
		$pfpro_stmt = $this->db->mysqli->prepare('REPLACE INTO BackgroundCheck_pfpro_Persons 
			(PersonId,CheckDate,CheckSubmittedBy,TahoeId,ComprehensiveResults,CriminalResults,CheckResults)
			VALUES (?, ?, ?, ?, ?, ?, ?)');
		$pfpro_stmt->bind_param('iissssi', $PersonId, $CheckDate, $UserId, $TahoeID, $ComprehensiveResults, $CriminalResults, $PassFail);
		$pfpro_stmt->execute();

		$this->db->mysqli->close();
	}

	/*! \fn obj cleanupXML($xml) 
		\brief cleans up the XML response form SkipMax
		\param	$xml xml response
		\return XML
	*/
	function cleanupXML($xml)
	{
		$xmlOut = '';
		$inTag = false;
		$xmlLen = strlen($xml);
		for ($i = 0; $i < $xmlLen; ++$i) {
			$char = $xml[$i];
			// $nextChar = $xml[$i+1];
			switch ($char) {
				case '<':
					if (!$inTag) {
						// Seek forward for the next tag boundry
						for ($j = $i + 1; $j < $xmlLen; ++$j) {
							$nextChar = $xml[$j];
							switch ($nextChar) {
								case '<':  // Means a < in text
									$char = htmlentities($char);
									break 2;

								case '>':  // Means we are in a tag
									$inTag = true;
									break 2;
							}
						}
					} else {
						$char = htmlentities($char);
					}
					break;

				case '>':
					if (!$inTag) {  // No need to seek ahead here
						$char = htmlentities($char);
					} else {
						$inTag = false;
					}
					break;

				default:
					if (!$inTag) {
						$char = htmlentities($char);
					}
					break;
			}
			$xmlOut .= $char;
		}
		return $xmlOut;
	}
}
?>