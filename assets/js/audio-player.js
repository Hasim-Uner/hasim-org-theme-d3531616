( function() {
    function setState( root, isPlaying ) {
        var button = root.querySelector( '[data-hp-audio-toggle]' );
        var label = root.querySelector( '[data-hp-audio-label]' );
        var status = root.querySelector( '[data-hp-audio-status]' );

        if ( button ) {
            button.setAttribute( 'aria-pressed', isPlaying ? 'true' : 'false' );
        }

        if ( label ) {
            label.textContent = isPlaying ? 'Pause' : 'Mission anhören';
        }

        if ( status ) {
            status.textContent = isPlaying ? 'Wird abgespielt' : 'Audio bereit';
        }
    }

    function initAudioPlayer( root ) {
        var button = root.querySelector( '[data-hp-audio-toggle]' );
        var audio = root.querySelector( '[data-hp-audio-media]' );
        var status = root.querySelector( '[data-hp-audio-status]' );

        if ( ! button || ! audio ) {
            return;
        }

        button.addEventListener( 'click', function() {
            if ( audio.paused ) {
                audio.play().then( function() {
                    setState( root, true );
                } ).catch( function() {
                    if ( status ) {
                        status.textContent = 'Audio konnte nicht gestartet werden';
                    }
                } );
                return;
            }

            audio.pause();
            setState( root, false );
        } );

        audio.addEventListener( 'ended', function() {
            setState( root, false );
        } );

        audio.addEventListener( 'pause', function() {
            setState( root, false );
        } );
    }

    document.querySelectorAll( '[data-hp-audio]' ).forEach( initAudioPlayer );
}() );
