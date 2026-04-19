<?php

/**
 * This class is loaded on the front-end since its main job is 
 * to display the WhatsApp box.
 */

class ACGWAA_Frontend {
	
	public function __construct () {
		add_action( 'wp_enqueue_scripts',  array( $this, 'gwaa_insta_scritps' ) );

		add_action( 'wp_footer', array($this,'GWAA_cf7_gpa_plugin_script'), 21, 1 );
	}
	public function gwaa_insta_scritps () {
		wp_enqueue_style('gwaa-stylee', GWAA_PLUGIN_URL . '/assents/css/style.css', array(), '1.0.0', 'all');

	}
	public function GWAA_cf7_gpa_plugin_script() 
	{
		$gpa_page = get_option( 'gwaa_cf7_geo_gpa_page' );
		$gwaa_country_code = get_option( 'gwaa_country_code','' );
		$gwaa_place_types = get_option( 'gwaa_place_types','' );
	    $api_key = get_option( 'gwaa_cf7_geo_api_key' );
		if(is_ssl())
	  {
			$securee = 'https';
	  }
	  else
	  {
			$securee = 'http';
	  }
	  $api_script = $securee.'://maps.googleapis.com/maps/api/js?key=' . rawurlencode($api_key) . '&v=beta&libraries=places&loading=async';
	?>
	<script async defer src="<?php echo esc_url( $api_script );?>"></script>
<script>
(function() {
    function initializePlaceAutocomplete(retries = 10) {
        if ( ! window.google || ! google.maps || ! google.maps.places || ! google.maps.places.PlaceAutocompleteElement ) {
            if ( retries > 0 ) {
                console.warn('Google Maps Places API not ready. Retrying...');
                setTimeout(() => initializePlaceAutocomplete(retries - 1), 500);
            } else {
                console.error('Google Maps Places API failed to load.');
            }
            return;
        }
        console.log('Google Maps PlaceAutocompleteElement ready.');

        var hiddenInputs = document.querySelectorAll('input.wpcf7-gmautocomplete');
        hiddenInputs.forEach(function(hiddenInput) {
            applyPlaceAutocomplete(hiddenInput);
        });
    }

    function applyPlaceAutocomplete(hiddenInput) {
        var name    = hiddenInput.name;
        var wrapper = document.getElementById(name + '_autocomplete_wrapper');
        if ( ! wrapper ) return;
        if ( wrapper.dataset.initialized ) return;
        wrapper.dataset.initialized = 'true';

        var elementOptions = {};
        <?php if ( $gwaa_country_code !== '' ) : ?>
        elementOptions.includedRegionCodes = <?php echo wp_json_encode( explode( ',', $gwaa_country_code ) ); ?>;
        <?php endif; ?>
        <?php if ( $gwaa_place_types !== '' ) : ?>
        elementOptions.includedPrimaryTypes = <?php echo wp_json_encode( explode( ',', $gwaa_place_types ) ); ?>;
        <?php endif; ?>

        var placeElement = new google.maps.places.PlaceAutocompleteElement(elementOptions);
        placeElement.style.width   = '100%';
        placeElement.style.display = 'block';

        wrapper.appendChild(placeElement);

        // Clear hidden input when user clears the field
        placeElement.addEventListener('gmp-placeclear', function() {
            hiddenInput.value = '';
        });

        placeElement.addEventListener('gmp-select', async function(event) {
            var placePrediction = event.placePrediction;
            var place = placePrediction.toPlace();

            // ✅ 'geometry' removed — use 'location' instead
            await place.fetchFields({
                fields: ['addressComponents', 'formattedAddress', 'location', 'displayName']
            });

            console.log('gmp-select fired');
            console.log('formattedAddress:', place.formattedAddress);

            var address1 = '';
            var postcode = '';
            var locality = '';
            var state    = '';
            var country  = '';

            if ( place.addressComponents ) {
                for ( var component of place.addressComponents ) {
                    var type = component.types[0];
                    switch ( type ) {
                        case 'street_number':
                            address1 = component.longText + ' ' + address1;
                            break;
                        case 'route':
                            address1 += component.shortText;
                            break;
                        case 'postal_code':
                            postcode = component.longText + postcode;
                            break;
                        case 'postal_code_suffix':
                            postcode = postcode + '-' + component.longText;
                            break;
                        case 'locality':
                            locality = component.longText;
                            var localityEl = document.getElementById(name + '_locality');
                            if ( localityEl ) localityEl.value = component.longText;
                            break;
                        case 'administrative_area_level_1':
                            state = component.shortText;
                            var stateEl = document.getElementById(name + '_state');
                            if ( stateEl ) stateEl.value = component.shortText;
                            break;
                        case 'country':
                            country = component.longText;
                            var countryEl = document.getElementById(name + '_country');
                            if ( countryEl ) countryEl.value = component.longText;
                            break;
                    }
                }
            }

            // Populate sub-fields
            var address2El = document.getElementById(name + '_address2');
            if ( address2El ) address2El.value = address1;

            var postcodeEl = document.getElementById(name + '_postcode');
            if ( postcodeEl ) postcodeEl.value = postcode;

            // Build full address from parts as fallback
            var addressParts = [];
            if ( address1 ) addressParts.push(address1);
            if ( locality )  addressParts.push(locality);
            if ( state )     addressParts.push(state);
            if ( postcode )  addressParts.push(postcode);
            if ( country )   addressParts.push(country);

            var fullAddress = ( place.formattedAddress && place.formattedAddress.trim() !== '' )
                ? place.formattedAddress
                : addressParts.join(', ');

            // Set value and force CF7 to detect it
            hiddenInput.value = fullAddress;
            hiddenInput.setAttribute('value', fullAddress);
            hiddenInput.dispatchEvent(new Event('input',  { bubbles: true }));
            hiddenInput.dispatchEvent(new Event('change', { bubbles: true }));

            console.log('Hidden input [' + name + '] set to:', hiddenInput.value);

            // ✅ Lat/Lng — use place.location (not place.geometry.location)
            var latField = document.getElementById(name + '_latitude');
            var lngField = document.getElementById(name + '_longitude');
            if ( place.location ) {
                if ( latField ) latField.value = place.location.lat();
                if ( lngField ) lngField.value = place.location.lng();
            }

            // Map
            var mapEl = document.getElementById(name + 'map');
            if ( mapEl && place.location ) {
                mapEl.style.display = 'block';
                var map = new google.maps.Map(mapEl, {
                    zoom: 17,
                    center: place.location,
                    mapTypeControl: false,
                });
                new google.maps.Marker({ position: place.location, map: map });
            }
        });
    }

    setTimeout(() => initializePlaceAutocomplete(), 1000);

    jQuery(window).on('elementor/popup/show', function() {
        setTimeout(() => initializePlaceAutocomplete(), 1000);
    });
})();
</script>
	<?php 
			
	}
}