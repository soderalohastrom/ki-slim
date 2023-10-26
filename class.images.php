<?php
/*! \class Images class.imagtes.php "class.search.php"
 *  \brief used to work with images.
 */
class Images {
	/*! \fn obj __constructor($DB)
		\brief images class constructor.
		\param	$DB db class object
		\return null
	*/
	function __construct($DB, $RECORD) {
		$this->db 		= $DB;
		$this->record	= $RECORD;
	}
	
	function render_ImagesUploadModal($personID) {
		?>
        <div class="modal fade" id="imgUploadModal" data-backdrop="static" role="dialog" aria-labelledby="imgUploadModalLabel" aria-hidden="true">
            <div class="modal-dialog " role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="imgUploadModalLabel">Upload Image(s)</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">   
<form class="m-form" id="NewImages" action="">
    <input type="hidden" name="pid" value="<?php echo $personID?>" />      
<div class="m-dropzone m-dropzone--success" action="/ajax/upload.php?pid=<?php echo $personID?>" id="m-dropzone-three">
    <div class="m-dropzone__msg dz-message needsclick">
        <h3 class="m-dropzone__msg-title">
            Drop files here or click to upload.
        </h3>
        <span class="m-dropzone__msg-desc">
            Only image files are allowed for upload
        </span>
    </div>
</div>
	</form>
        
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-secondary" id="clear-dropzone">Clear Images</button>
                    </div>
                </div>
            </div>
        </div>
        <?php		
	}
	
	function render_ImagesPreviewModal($personID) {
		?>
        <div class="modal fade" id="imgPreviewModal" data-backdrop="static" role="dialog" aria-labelledby="imgPreviewModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="imgPreviewModalLabel">Preview Image</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                    	<form id="imageUpdateForm">
                        <input type="hidden" id="imageUpdateImageID" name="PersonsImages_id" value="" />
                        <input type="hidden" name="pid" value="<?php echo $personID?>" />
                        <div class="row">
                        	<div class="col-8">   
								<div id="img-preview-wrapper" align="center"></div>
							</div>
                            <div class="col-4">
                            	<div class="m-form__group form-group">
									<div class="m-radio-list">
										<label class="m-checkbox">
                                        	<input type="radio" name="PersonsImages_status" value="2"> Primary
                                            <span></span>
										</label>
                                        <label class="m-checkbox">
                                        	<input type="radio" name="PersonsImages_status" value="1"> Approved
                                            <span></span>
										</label>
                                        <label class="m-checkbox">
                                        	<input type="radio" name="PersonsImages_status" value="0"> Private
                                            <span></span>
										</label>
									</div>
								</div>
                                <div><button type="button" class="btn btn-secondary btn-sm btn-block" onclick="editImageNow()">Edit Image <i class="fa fa-eye"></i></button></div>
                                <div>&nbsp;</div>
                                <div><button type="button" class="btn btn-danger btn-sm btn-block" onclick="removeImage()">Delete Image <i class="fa fa-ban"></i></button></div>
                            </div>                        
                        </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-secondary" onclick="saveImages()">Save</button>
                    </div>
                </div>
            </div>
        </div>
        <script>
		function editImageNow() {
			var url = '/editor-image/index.php?ImageID='+$('#imageUpdateImageID').val();
			window.open(url, 'photoEditor', 'width=600,height=800');				
		}
		</script>
        <?php		
	}
	
	
	function resize_image_crop($image,$width,$height) {
		$w = @imagesx($image); //current width
		$h = @imagesy($image); //current height
		if ((!$w) || (!$h)) { $GLOBALS['errors'][] = 'Image couldn\'t be resized because it wasn\'t a valid image.'; return false; }
		if (($w == $width) && ($h == $height)) { return $image; } //no resizing needed
		
		//try max width first...
		$ratio = $width / $w;
		$new_w = $width;
		$new_h = $h * $ratio;
		
		//if that created an image smaller than what we wanted, try the other way
		if ($new_h < $height) {
			$ratio = $height / $h;
			$new_h = $height;
			$new_w = $w * $ratio;
		}
		
		$image2 = imagecreatetruecolor ($new_w, $new_h);
		imagecopyresampled($image2,$image, 0, 0, 0, 0, $new_w, $new_h, $w, $h);
	
		//check to see if cropping needs to happen
		if (($new_h != $height) || ($new_w != $width)) {
			$image3 = imagecreatetruecolor ($width, $height);
			if ($new_h > $height) { //crop vertically
				$extra = $new_h - $height;
				$x = 0; //source x
				$y = round($extra / 2); //source y
				imagecopyresampled($image3,$image2, 0, 0, $x, $y, $width, $height, $width, $height);
			} else {
				$extra = $new_w - $width;
				$x = round($extra / 2); //source x
				$y = 0; //source y
				imagecopyresampled($image3,$image2, 0, 0, $x, $y, $width, $height, $width, $height);
			}
			imagedestroy($image2);
			return $image3;
		} else {
			return $image2;
		}
	}
	
	function resize_image_max($image,$max_width,$max_height) {
		$w = imagesx($image); //current width
		$h = imagesy($image); //current height
		if ((!$w) || (!$h)) { $GLOBALS['errors'][] = 'Image couldn\'t be resized because it wasn\'t a valid image.'; return false; }
	
		if (($w <= $max_width) && ($h <= $max_height)) { return $image; } //no resizing needed
		
		//try max width first...
		$ratio = $max_width / $w;
		$new_w = $max_width;
		$new_h = $h * $ratio;
		
		//if that didn't work
		if ($new_h > $max_height) {
			$ratio = $max_height / $h;
			$new_h = $max_height;
			$new_w = $w * $ratio;
		}
		
		$new_image = imagecreatetruecolor ($new_w, $new_h);
		imagecopyresampled($new_image,$image, 0, 0, 0, 0, $new_w, $new_h, $w, $h);
		return $new_image;
	}
	
	function resize_image_force($image,$width,$height) {
		$w = @imagesx($image); //current width
		$h = @imagesy($image); //current height
		if ((!$w) || (!$h)) { $GLOBALS['errors'][] = 'Image couldn\'t be resized because it wasn\'t a valid image.'; return false; }
		if (($w == $width) && ($h == $height)) { return $image; } //no resizing needed
	
		$image2 = imagecreatetruecolor ($width, $height);
		imagecopyresampled($image2,$image, 0, 0, 0, 0, $width, $height, $w, $h);
	
		return $image2;
	}
	
	function resize_image($method,$image_loc,$new_loc,$width,$height) {
		if (!is_array(@$GLOBALS['errors'])) { $GLOBALS['errors'] = array(); }		
		if (!in_array($method,array('force','max','crop'))) { $GLOBALS['errors'][] = 'Invalid method selected.'; }		
		if (!$image_loc) { $GLOBALS['errors'][] = 'No source image location specified.'; }
		else {
			if ((substr(strtolower($image_loc),0,7) == 'http://') || (substr(strtolower($image_loc),0,7) == 'https://')) { /*don't check to see if file exists since it's not local*/ }
			elseif (!file_exists($image_loc)) { $GLOBALS['errors'][] = 'Image source file does not exist.'; }
			$extension = strtolower(substr($image_loc,strrpos($image_loc,'.')));
			if (!in_array($extension,array('.jpg','.jpeg','.png','.gif','.bmp'))) { $GLOBALS['errors'][] = 'Invalid source file extension!'; }
		}
		
		if (!$new_loc) { $GLOBALS['errors'][] = 'No destination image location specified.'; }
		else {
			$new_extension = strtolower(substr($new_loc,strrpos($new_loc,'.')));
			if (!in_array($new_extension,array('.jpg','.jpeg','.png','.gif','.bmp'))) { $GLOBALS['errors'][] = 'Invalid destination file extension!'; }
		}
	
		$width = abs(intval($width));
		if (!$width) { $GLOBALS['errors'][] = 'No width specified!'; }
		
		$height = abs(intval($height));
		if (!$height) { $GLOBALS['errors'][] = 'No height specified!'; }
		
		if (count($GLOBALS['errors']) > 0) { $this->echo_errors(); return false; }
		
		if (in_array($extension,array('.jpg','.jpeg'))) { $image = @imagecreatefromjpeg($image_loc); }
		elseif ($extension == '.png') { $image = @imagecreatefrompng($image_loc); }
		elseif ($extension == '.gif') { $image = @imagecreatefromgif($image_loc); }
		elseif ($extension == '.bmp') { $image = @imagecreatefromwbmp($image_loc); }
		
		if (!$image) { $GLOBALS['errors'][] = 'Image could not be generated!'; }
		else {
			$current_width = imagesx($image);
			$current_height = imagesy($image);
			if ((!$current_width) || (!$current_height)) { $GLOBALS['errors'][] = 'Generated image has invalid dimensions!'; }
		}
		if (count($GLOBALS['errors']) > 0) { @imagedestroy($image); $this->echo_errors(); return false; }
	
		if ($method == 'force') { $new_image = $this->resize_image_force($image,$width,$height); }
		elseif ($method == 'max') { $new_image = $this->resize_image_max($image,$width,$height); }
		elseif ($method == 'crop') { $new_image = $this->resize_image_crop($image,$width,$height); }
		
		if ((!$new_image) && (count($GLOBALS['errors'] == 0))) { $GLOBALS['errors'][] = 'New image could not be generated!'; }
		if (count($GLOBALS['errors']) > 0) { @imagedestroy($image); $this->echo_errors(); return false; }
		
		$save_error = false;
		if (in_array($extension,array('.jpg','.jpeg'))) { imagejpeg($new_image,$new_loc) or ($save_error = true); }
		elseif ($extension == '.png') { imagepng($new_image,$new_loc) or ($save_error = true); }
		elseif ($extension == '.gif') { imagegif($new_image,$new_loc) or ($save_error = true); }
		elseif ($extension == '.bmp') { imagewbmp($new_image,$new_loc) or ($save_error = true); }
		if ($save_error) { $GLOBALS['errors'][] = 'New image could not be saved!'; }
		if (count($GLOBALS['errors']) > 0) { @imagedestroy($image); @imagedestroy($new_image); $this->echo_errors(); return false; }
	
		@imagedestroy($image);
		@imagedestroy($new_image);
		
		return true;
	}
	
	function echo_errors() {
		if (!is_array(@$GLOBALS['errors'])) { $GLOBALS['errors'] = array('Unknown error!'); }
		foreach ($GLOBALS['errors'] as $error) { echo '<p style="color:red;font-weight:bold;">Error: '.$error.'</p>'; }
	}
	
	
	
}
?>