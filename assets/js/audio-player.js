( function() {
    function formatTime( seconds ) {
        if ( ! Number.isFinite( seconds ) || seconds < 0 ) {
            return '0:00';
        }

        var minutes = Math.floor( seconds / 60 );
        var rest = Math.floor( seconds % 60 );

        return minutes + ':' + String( rest ).padStart( 2, '0' );
    }

    function updateProgress( root, audio ) {
        var progress = root.querySelector( '[data-hp-audio-progress]' );
        var progressBar = root.querySelector( '[data-hp-audio-progress-bar]' );
        var time = root.querySelector( '[data-hp-audio-time]' );
        var duration = Number.isFinite( audio.duration ) && audio.duration > 0 ? audio.duration : 0;
        var percent = duration ? Math.min( 100, Math.max( 0, ( audio.currentTime / duration ) * 100 ) ) : 0;

        if ( progress ) {
            progress.setAttribute( 'aria-valuenow', String( Math.round( percent ) ) );
        }

        if ( progressBar ) {
            progressBar.style.width = percent + '%';
        }

        if ( time ) {
            time.textContent = formatTime( audio.currentTime ) + ' / ' + formatTime( duration );
        }
    }

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
            status.textContent = isPlaying ? 'Wird abgespielt' : 'Bereit';
        }
    }

    function initAudioPlayer( root ) {
        var button = root.querySelector( '[data-hp-audio-toggle]' );
        var audio = root.querySelector( '[data-hp-audio-media]' );
        var status = root.querySelector( '[data-hp-audio-status]' );

        if ( ! button || ! audio ) {
            return;
        }

        audio.volume = 1;
        audio.muted = false;

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

        audio.addEventListener( 'loadedmetadata', function() {
            updateProgress( root, audio );
        } );

        audio.addEventListener( 'timeupdate', function() {
            updateProgress( root, audio );
        } );

        audio.addEventListener( 'play', function() {
            audio.volume = 1;
            audio.muted = false;
            setState( root, true );
        } );

        audio.addEventListener( 'ended', function() {
            setState( root, false );
            updateProgress( root, audio );
        } );

        audio.addEventListener( 'pause', function() {
            setState( root, false );
            updateProgress( root, audio );
        } );
    }

    document.querySelectorAll( '[data-hp-audio]' ).forEach( initAudioPlayer );
}() );
