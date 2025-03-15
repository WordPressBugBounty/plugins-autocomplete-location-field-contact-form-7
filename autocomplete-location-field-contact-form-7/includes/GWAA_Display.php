<?php
/**
* This class is loaded on the front-end since its main job is
* to display the WhatsApp box.
*/
class GWAA_Display {
	private $fields = array();
    private $names = array();
	public function __construct () {
		add_action('wpcf7_init', array($this, 'GWAA_cf7_autocomplete_add_tag_generator'));
		add_action( 'admin_init', array($this, 'GWAA_add_products_tag_generator_menu'));
		add_action( 'wpcf7_validate_gmautocomplete', array($this, 'GWAA_products_validation_filter'), 10, 2 );
		add_action( 'wpcf7_validate_gmautocomplete*', array($this, 'GWAA_products_validation_filter'), 10, 2 );
	}
	
	public function GWAA_cf7_autocomplete_add_tag_generator()
	{
		
		wpcf7_add_form_tag( array( 'gmautocomplete', 'gmautocomplete*' ),array($this, 'GWAA_wpcf7_cfpl_products_shortcode_handler'),true);
		
		
		
	}
	public function GWAA_wpcf7_cfpl_products_shortcode_handler( $tag )
	{
		
		if (empty($tag->name)) 
		{
			return '';
		}
		
		$validation_error = wpcf7_get_validation_error( $tag->name );
		$class = wpcf7_form_controls_class( $tag->type, 'gmautocomplete' );
		
		/* $class = wpcf7_form_controls_class( $tag->type ); */

		if ( $validation_error ) 
		{
			$class .= ' wpcf7-not-valid';
		}
		
		$atts = array();
		$atts['size']		= $tag->get_size_option( '40' );
		$atts['maxlength']	= $tag->get_maxlength_option();
		$atts['class']		= $tag->get_class_option( $class );
		$atts['id']			= $tag->get_id_option();
		$atts['tabindex']	= $tag->get_option( 'tabindex', 'int', true );

		if ( $tag->has_option( 'readonly' ) ) 
		{
			$atts['readonly'] = 'readonly';
		}

		if ( $tag->is_required() ) 
		{
			$atts['aria-required'] = 'true';
		}
		$atts['aria-invalid'] = $validation_error ? 'true' : 'false';
		
		
		
		$atts['placeholder'] = get_option('gwaa_tr_enter_loc')!=''?get_option('gwaa_tr_enter_loc'):'Enter a location';		
		$atts['type']	= 'text';
		$atts['name']	= $tag->name;
		$atts = wpcf7_format_atts($atts);
        $this->fields[$tag->name]   = $tag->values;
        $this->names[]  = $tag->name;   
        $gwaa_address_option = get_option('gwaa_address_option',array());
        if(empty($gwaa_address_option)){
        	$gwaa_address_option = array();
        }
        $gwaa_enable_map = get_option('gwaa_enable_map','');
        ob_start();
        ?>
      
        <div class="wpcf7-form-control-wrap-main <?php echo sanitize_html_class( $tag->name )?>">
        	<span class="wpcf7-form-control-wrap" data-name="<?php echo $tag->name;?>">
	        	<input <?php echo $atts;?> />
	        	<?php echo $validation_error;?>
	        </span>
        	<?php
        	if (in_array("street_number", $gwaa_address_option)) {
        	?>
        	 <div class="full-field">
		        <label>
		        	<?php echo get_option('gwaa_tr_apartment')!=''?get_option('gwaa_tr_apartment'):'Apartment, unit, suite, or floor #';?>
		        </label>
		        <input id="<?php echo $tag->name;?>_address2" name="<?php echo $tag->name;?>_address2" />
		     </div>
		    <?php 
		 	}
		    ?>
		    <?php
        	if (in_array("locality", $gwaa_address_option)) {
        	?>
		     <div class="full-field">
		        <label>
		        	<?php echo get_option('gwaa_tr_city')!=''?get_option('gwaa_tr_city'):'City';?>
		        </label>
		        <input id="<?php echo $tag->name;?>_locality" name="<?php echo $tag->name;?>_locality"  />
		     </div>
		    <?php 
		 	}
		    ?>
		    <?php
        	if (in_array("administrative_area_level_1", $gwaa_address_option)) {
        	?>
		     <div class="slim-field-left">
		        <label  class="form-label">
		        	<?php echo get_option('gwaa_tr_state')!=''?get_option('gwaa_tr_state'):'State/Province';?>
		        </label>
		        <input id="<?php echo $tag->name;?>_state" name="<?php echo $tag->name;?>_state"  />
		     </div>
		    <?php 
		 	}
		    ?>
		    <?php
        	if (in_array("postcode", $gwaa_address_option)) {
        	?>
		     <div class="slim-field-right" >
		        <label >
			        <?php echo get_option('gwaa_tr_postalcode')!=''?get_option('gwaa_tr_postalcode'):'Postal code';?>
			    </label>
		        <input id="<?php echo $tag->name;?>_postcode" name="<?php echo $tag->name;?>_postcode"  />
		     </div>
		    <?php 
		 	}
		    ?>
		    <?php
        	if (in_array("country", $gwaa_address_option)) {
        	?>
		     <div class="full-field">
		        <label>
		        <?php echo get_option('gwaa_tr_country')!=''?get_option('gwaa_tr_country'):'Country/Region';?>
		    </label>
		        <input id="<?php echo $tag->name;?>_country" name="<?php echo $tag->name;?>_country"  />
		     </div>
		    <?php 
		 	}
		 	if ($gwaa_enable_map==true) {
		 	?>
		    <div class="full-field">
		    	<div id="<?php echo $tag->name;?>map" class="gwaa_map"></div>
		    </div>
		    <?php
			}
		    ?>
        </div>
        <?php
        $html = ob_get_clean();
		return $html;
	}
	public function GWAA_add_products_tag_generator_menu()
	{
		$tag_generator = WPCF7_TagGenerator::get_instance();
		$tag_generator->add( 'gmautocomplete', __( 'Field Autocomplete', 'gwaa' ),array($this, 'GWAA_wpcf7_tag_products_generator_menu') ,array('version'=>2));
	}
	function GWAA_wpcf7_tag_products_generator_menu( $contact_form, $args = '' ) {
		$args = wp_parse_args( $args, array() );
		$type = 'gmautocomplete';
	 	$gwaa_cf7_geo_api_key = get_option('gwaa_cf7_geo_api_key','');
		?>        
		<header class="description-box">
			<h3>gmautocomplete form tag generator</h3>
			<p>
			<?php
			if($gwaa_cf7_geo_api_key==''){
			?>
			<a href="<?php echo get_admin_url().'admin.php?page=google-place-api';?>" target="_blank" style="font-weight: bold;color: red;">Setup Google Places API Key </a>
			<?php
			}
			?>
			</p>
		</header> 
		<div class="control-box">
			<fieldset>
				<legend><?php echo esc_html( __( 'Field type', 'contact-form-7' ) ); ?></legend>
				<input type="hidden" data-tag-part="basetype" value="gmautocomplete" >
				<label>
				<input type="checkbox" data-tag-part="type-suffix" value="*">This is a required field.
				</label>
			</fieldset>
			<fieldset>
				<legend>Name</legend>
				<input type="text" data-tag-part="name" pattern="[A-Za-z][A-Za-z0-9_\-]*">
			</fieldset>
			<fieldset>
				<legend>Id</legend>
				<input type="text" data-tag-part="option" data-tag-option="id:" pattern="[A-Za-z][A-Za-z0-9_\-]*">
			</fieldset>
			<fieldset>
				<legend>Class</legend>
				<input type="text" data-tag-part="option" data-tag-option="class:" pattern="[A-Za-z0-9_\-\s]*" >
			</fieldset>
		</div>
		<div class="insert-box">
			<div class="flex-container">
				<input type="text" class="code" readonly="readonly" onfocus="this.select();" data-tag-part="tag">
				<div class="submitbox">
					<input type="button" class="button button-primary insert-tag" value="Insert Tag" />
				</div>
	    	</div/>
			<p class="mail-tag-tip">
				<label for="<?php echo esc_attr( $args['content'] . '-mailtag' ); ?>"><?php echo sprintf( esc_html( __( "To use the value input through this field in a mail field, you need to insert the corresponding mail-tag (%s) into the field on the Mail tab.", 'calculation-for-contact-form-7' ) ), '<strong><span class="mail-tag"></span></strong>' ); ?>
			    </label>
			</p>
		</div>
		<?php
	}
	public function GWAA_products_validation_filter($result, $tag)
	{
	    $tag = new WPCF7_Shortcode($tag);
	    $name = $tag->name;
	    $value = isset($_POST[$name]) ? sanitize_text_field($_POST[$name]) : '';

	    if ($tag->is_required() && empty($value)) {
	        $result->invalidate($tag, 'This field is required.');
	    }

	    return $result;
	}
	
	
}