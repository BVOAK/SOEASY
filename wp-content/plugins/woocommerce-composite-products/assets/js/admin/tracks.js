( function( window ) {
	window.WC_CP        = {};
	window.WC_CP.tracks = {};

	window.WC_CP.tracks.recordEvent = function ( event, properties = {} ) {
		if ( ! window.wcTracks ) {
			return;
		}

		properties = { ...wc_composite_admin_tracks_default_params, ...properties };

		window.wcTracks.recordEvent( event, properties );
	}
} )( window );
