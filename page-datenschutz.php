<?php
/**
 * Template Name: Datenschutzerklärung
 *
 * DSGVO-konforme Datenschutzerklärung für hasimuener.org.
 * Hosting: Hostpress (Deutschland).
 * Keine Analytics, keine Werbe-Cookies, kein Tracking.
 *
 * @package Hasimuener_Journal
 * @version 1.0.0
 */

get_header(); ?>

<article class="hp-legal" aria-label="Datenschutzerklärung">
    <div class="hp-legal__inner">

        <header class="hp-legal__header">
            <span class="hp-kicker">Rechtliches</span>
            <h1 class="hp-legal__title">Datenschutzerklärung</h1>
        </header>

        <div class="hp-legal__body prose">

            <h2>1. Überblick</h2>

            <p>
                Diese Website verzichtet vollständig auf Tracking, Werbung, externe Analysedienste
                und nicht notwendige Cookies. Die folgende Erklärung legt offen, welche Daten
                beim Besuch dieser Seite technisch verarbeitet werden und welche Rechte dir zustehen.
                Für den serverseitigen Versand von Kontakt-, Bestätigungs- und Newsletter-E-Mails
                wird zusätzlich der E-Mail-Dienst <strong>Brevo</strong> als technischer
                Auftragsverarbeiter eingesetzt. Auf der Website selbst werden dafür keine
                Brevo-Skripte, Tracker oder Marketing-Cookies geladen.
            </p>

            <p>
                Verantwortlicher im Sinne der DSGVO (Art. 4 Nr. 7):<br>
                Hasim Üner, Warschauer Str. 5, 30982 Pattensen<br>
                E-Mail: <a href="mailto:hallo@hasimuener.org">hallo@hasimuener.org</a>
            </p>

            <h2>2. Hosting und Serverprotokolle</h2>

            <p>
                Diese Website wird auf Servern von <strong>Hostpress</strong> betrieben.
                Hostpress ist ein in Deutschland ansässiger Hoster; die Server stehen in Deutschland.
                Damit unterliegt die Datenverarbeitung dem deutschen und europäischen Datenschutzrecht.
            </p>

            <p>
                Bei jedem Seitenaufruf speichert der Webserver automatisch folgende Daten im sogenannten
                Server-Logfile:
            </p>

            <ul>
                <li>IP-Adresse des anfragenden Geräts (anonymisiert oder vollständig, je nach Hostkonfiguration)</li>
                <li>Datum und Uhrzeit des Zugriffs</li>
                <li>Aufgerufene URL</li>
                <li>HTTP-Statuscode und übertragene Datenmenge</li>
                <li>Referrer-URL (sofern übermittelt)</li>
                <li>Browser-Kennung (User-Agent)</li>
            </ul>

            <p>
                Diese Daten werden ausschließlich zur Sicherstellung eines störungsfreien Betriebs und
                zur Abwehr von Angriffen verarbeitet (Art. 6 Abs. 1 lit. f DSGVO — berechtigtes Interesse).
                Eine Zusammenführung mit anderen Datenquellen erfolgt nicht. Die Logs werden nach
                spätestens 30 Tagen gelöscht.
            </p>

            <h2>3. Kontakt per E-Mail und Formular</h2>

            <p>
                Wenn du per E-Mail oder über das Kontaktformular Kontakt aufnimmst, verarbeite ich die
                von dir übermittelten Angaben ausschließlich zur Bearbeitung deiner Anfrage. Dazu können
                Name, E-Mail-Adresse, ein optionaler Betreff und deine Nachricht gehören.
            </p>

            <p>
                Beim Kontaktformular werden zusätzlich technische Prüfdaten verarbeitet, um Missbrauch
                und automatisierte Anfragen abzuwehren. Dazu gehören insbesondere der Zeitpunkt des
                Formularaufrufs, eine serverseitige Prüfsumme und eine kurze Zugriffsdrosselung.
                Rechtsgrundlage ist Art. 6 Abs. 1 lit. b DSGVO, sofern es um die Anbahnung oder
                Beantwortung einer konkreten Anfrage geht, sowie Art. 6 Abs. 1 lit. f DSGVO für den
                Schutz dieser Website vor Missbrauch.
            </p>

            <p>
                Die über das Formular übermittelten Inhalte werden nicht auf dieser Website veröffentlicht,
                sondern direkt per E-Mail weitergeleitet und zusätzlich in der internen
                WordPress-Verwaltung dokumentiert. Sie werden nur so lange gespeichert, wie es für
                die Bearbeitung, eine mögliche Anschlusskorrespondenz und die nachvollziehbare
                Dokumentation der Anfrage erforderlich ist. Die in der Website-Verwaltung gespeicherten
                Kontaktanfragen werden spätestens nach <?php echo esc_html( (string) hp_get_contact_submission_retention_days() ); ?> Tagen gelöscht.
            </p>

            <p>
                Für die Zustellung dieser E-Mails wird der Dienst <strong>Brevo</strong> als
                technischer Versanddienstleister genutzt. Die Übermittlung erfolgt ausschließlich
                serverseitig; beim Besuch der Website werden dadurch keine zusätzlichen Cookies
                oder Tracking-Skripte gesetzt.
            </p>

            <h2>4. Newsletter / Hinweise auf neue Texte</h2>

            <p>
                Wenn du dich für <strong>„Neue Texte per E-Mail“</strong> einträgst, verarbeite ich
                deine E-Mail-Adresse, die gewählte Quelle der Anmeldung, Zeitpunkte von Anmeldung,
                Bestätigung und Abmeldung sowie technische Prüfdaten in gekürzter bzw. gehashter Form,
                um die Einwilligung nachweisen und Missbrauch abwehren zu können.
            </p>

            <p>
                Die Anmeldung erfolgt im <strong>Double-Opt-in-Verfahren</strong>: Erst nach dem Klick
                auf den Bestätigungslink ist die Eintragung aktiv. Rechtsgrundlage ist
                Art. 6 Abs. 1 lit. a DSGVO. Technische Schutzmaßnahmen gegen Missbrauch stütze ich
                zusätzlich auf Art. 6 Abs. 1 lit. f DSGVO.
            </p>

            <p>
                Die Versandzustellung der Bestätigungs- und späteren Hinweismails erfolgt über
                <strong>Brevo</strong> als technischen E-Mail-Dienstleister. Auf der Website selbst
                wird dafür kein Brevo-Tracking eingebunden. Du kannst deine Einwilligung jederzeit über
                den Abmeldelink in jeder entsprechenden E-Mail widerrufen. Nicht bestätigte Anmeldungen
                werden spätestens nach <?php echo esc_html( (string) hp_get_newsletter_pending_retention_days() ); ?> Tagen gelöscht. Nach einer Abmeldung
                wird die Adresse nicht weiter für den Versand genutzt; statt des vollständigen Datensatzes
                bleibt nur eine minimierte Sperrnotiz in gehashter Form für maximal
                <?php echo esc_html( (string) hp_get_newsletter_suppression_retention_days() ); ?> Tage gespeichert, um erneute ungewollte Zusendungen
                zu vermeiden.
            </p>

            <h2>5. Cookies</h2>

            <p>
                Diese Website setzt keine Tracking- oder Marketing-Cookies ein.
                WordPress kann technisch notwendige Session-Cookies setzen (z. B. für das
                Kommentarformular, sofern aktiviert). Diese Cookies werden nicht für Werbung
                oder Profilbildung genutzt und nach dem Schließen des Browsers gelöscht.
            </p>

            <h2>6. Keine Analytics, kein Tracking</h2>

            <p>
                Es werden keine Analyse- oder Trackingdienste verwendet (kein Google Analytics,
                kein Meta Pixel, kein Matomo, kein vergleichbares Tool). Es wird kein nutzerbezogenes
                Profil erstellt.
            </p>

            <h2>7. Eingebettete externe Inhalte</h2>

            <p>
                Artikel können Links zu externen Websites enthalten. Für deren Datenschutzpraktiken
                bin ich nicht verantwortlich und habe keinen Einfluss darauf. Beim Aufruf externer Links
                verlässt du den Geltungsbereich dieser Erklärung.
            </p>

            <h2>8. Schriftarten</h2>

            <p>
                Die auf dieser Seite verwendete Schriftart (Merriweather) wird
                <strong>von eigenen Servern ausgeliefert</strong>.
                Es findet keine Verbindung zu Google Fonts oder anderen externen Schriftanbietern statt.
            </p>

            <h2>9. Deine Rechte (Art. 15–21 DSGVO)</h2>

            <p>Du hast das Recht auf:</p>

            <ul>
                <li><strong>Auskunft</strong> (Art. 15 DSGVO): Welche Daten von dir verarbeitet werden</li>
                <li><strong>Berichtigung</strong> (Art. 16 DSGVO): Korrektur unrichtiger Daten</li>
                <li><strong>Löschung</strong> (Art. 17 DSGVO): Entfernung gespeicherter Daten</li>
                <li><strong>Einschränkung</strong> (Art. 18 DSGVO): Beschränkung der Verarbeitung</li>
                <li><strong>Datenübertragbarkeit</strong> (Art. 20 DSGVO): Daten in maschinenlesbarem Format</li>
                <li><strong>Widerspruch</strong> (Art. 21 DSGVO): Gegen Verarbeitung auf Grundlage berechtigter Interessen</li>
            </ul>

            <p>
                Anfragen zu diesen Rechten kannst du per E-Mail an
                <a href="mailto:hallo@hasimuener.org">hallo@hasimuener.org</a> richten.
                Du hast außerdem das Recht, dich bei der zuständigen Aufsichtsbehörde zu beschweren.
                Zuständig ist die Landesbeauftragte für Datenschutz Niedersachsen:
                <a href="https://lfd.niedersachsen.de" target="_blank" rel="noopener noreferrer">
                    lfd.niedersachsen.de
                </a>.
            </p>

            <h2>10. Änderungen dieser Erklärung</h2>

            <p>
                Bei wesentlichen Änderungen am Betrieb dieser Website — etwa neuen Publikations-
                oder Interaktionsformen — wird diese Erklärung aktualisiert.
                Maßgeblich ist jeweils die zum Zeitpunkt des Besuchs gültige Version.
            </p>

            <p class="hp-legal__meta">
                <time datetime="<?php echo esc_attr( date( 'Y-m-d' ) ); ?>">
                    Stand: <?php echo esc_html( date_i18n( 'j. F Y' ) ); ?>
                </time>
            </p>

        </div>

    </div>
</article>

<?php get_footer(); ?>
