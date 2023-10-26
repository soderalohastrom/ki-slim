<?php
/*! \class Notes class.notes.php "class.notes.php"
 *  \brief used to work with record notes.
 */
class Notes {
	/*! \fn obj __constructor($DB)
		\brief notes class constructor.
		\param	$DB db class object
		\return null
	*/
	function __construct($DB, $RECORD) {
		$this->db 		= $DB;
		$this->record	= $RECORD;
	}
	
	/*! \fn obj get_LastNoteAction($personID)
		\brief get the last note/action for the record		
		\param	$personID	ID of the record.
		\return array
	*/
	function get_LastNoteAction($personID) {
		$sql = "SELECT * FROM PersonsNotes WHERE PersonsNotes_personID='".$personID."' ORDER BY PersonsNotes_dateCreated DESC LIMIT 1";
		$snd = $this->db->get_single_result($sql);
		if(isset($snd['empty_result'])) {
			$return['type-tag'] = '&nbsp;';
			$return['note-date'] = '&nbsp;';
			$return['note-body'] = '<div class="text-center">No Note(s) Found</div>';
		} else {
			$return['type-tag'] = $snd['PersonsNotes_type'].' &gt; '.$snd['PersonsNotes_header'];
			$return['note-date'] = date("m/d/Y", $snd['PersonsNotes_dateCreated']);
			$return['note-body'] = $snd['PersonsNotes_body'];
		}
		return $return;
	}
	
	/*! \fn obj get_noteTypesSelect($type)
		\brief get the select options for person types	
		\param	$type	$type to filter on
		\return array
	*/
	function get_noteTypesSelect($type) {
		$sql = "SELECT * FROM PersonsNotesTypes WHERE PersonsNotesTypes_type='".$this->db->mysqli->escape_string($type)."' ORDER BY PersonsNotesTypes_order ASC";
		echo $sql;
		$snd = $this->db->get_multi_result($sql);
		ob_start();
		foreach($snd as $dta):
			?><option vaue="<?php echo $dta['PersonsNotesTypes_header']?>"><?php echo $dta['PersonsNotesTypes_header']?></option><?php
		endforeach;
		return ob_get_clean();
	}
	
	function get_noteTypeArray($output='array') {
		$sql = "SELECT * FROM PersonsNotesTypes WHERE 1 ORDER BY PersonsNotesTypes_order ASC";
		//echo $sql;
		$snd = $this->db->get_multi_result($sql);
		foreach($snd as $dta):
			$types[] = $dta['PersonsNotesTypes_header'];
		endforeach;
		//return ob_get_clean();	
		if($output == 'array') {
			return $types;
		} elseif($output == 'js') {
			return json_encode($types);
		}
	}
		
	/*! \fn obj render_notesModal()
		\brief renders the notes modal	
		\return HTML
	*/
	function render_notesModal() {
		?>
        <div class="modal fade" id="addNotesModal" data-backdrop="static" role="dialog" aria-labelledby="addNotesModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addNotesModalLabel">Add Note/Action/Call/Task/Schedule</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body"> 
                    
<form class="m-form" id="NotesForm">
	<input type="hidden" name="pid" value="" />
    <input type="hidden" name="epoch" value="" />
    <input type="hidden" name="uid" value="<?php echo $_SESSION['system_user_id']?>" />
	<div class="row" style="margin-bottom:10px;">
    	<div class="col-6">Note For: <strong id="displayNoteRecipient">{NOTE RECIPIENT)</strong></div>
        <div class="col-6">Date: <strong id="displayNoteDate">{NOTE_DATE}</strong></div>
	</div>        
	      
    <div class="row">
        <div class="col-4">
            <div class="form-group m-form__group">
    			<label for="example_input_full_name">Type:</label>  
            	<div class="input-group m-input-group">
                	<span class="input-group-addon"><i class="flaticon-folder-1"></i></span>
                	<input type="text" class="form-control m-input" name="PersonsNotes_type" id="PersonsNotes_type" value="" readonly="readonly" />
                    <span class="input-group-addon"><i class="fa fa-arrow-right"></i></span>
            	</div>
        	</div>
		</div>
        <div class="col-8">
        	<div class="form-group m-form__group">
            <label for="example_input_full_name">Subheader:</label> 	
            <div class="m-typeahead">                                   
                <input class="form-control m-input" name="PersonsNotes_header" id="m_typeahead_1_modal" type="text" placeholder="Begin Typing Here" style="width:100%;">
            </div>
            <span class="m-form__help">Please select the header for your note or action</span>
            </div>
        </div>
    </div>
    
    <div class="row">
    	<div class="col-12">
            <div class="form-group m-form__group">
                <label>Body:</label>
                <div class="summernote notes-summernote"></div>
                <input type="hidden" name="PersonsNotes_body" id="PersonsNotes_body" value="" />
            </div>
		</div>
	</div>                    
</form>                      

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary" onclick="saveNote()">Save</button>
                    </div>
                </div>
            </div>
        </div>
        <script>
		var substringMatcher = function(strs) {
			return function findMatches(q, cb) {
				var matches, substringRegex;
		
				// an array that will be populated with substring matches
				matches = [];
		
				// regex used to determine if a string contains the substring `q`
				substrRegex = new RegExp(q, 'i');
		
				// iterate through the pool of strings and for any string that
				// contains the substring `q`, add it to the `matches` array
				$.each(strs, function(i, str) {
					if (substrRegex.test(str)) {
						matches.push(str);
					}
				});
				cb(matches);
			};
		};		
		var states = <?php echo $this->get_noteTypeArray('js')?>;
		$('#m_typeahead_1_modal').typeahead({
			hint: true,
			highlight: true,
			minLength: 1
		},{
			name: 'states',
			source: substringMatcher(states)
		});
        </script>
        <?php		
	}
	
	
	
}
?>