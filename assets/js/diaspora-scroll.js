/**
 * Diaspora-Architektur — Scroll-Storytelling Engine
 *
 * Vanilla JS: Intersection Observer reveals, Detail Panels,
 * SVG draw calculations, smooth scroll, keyboard nav.
 *
 * Budget: < 40KB. No dependencies.
 *
 * @package Hasimuener_Journal
 * @since   7.0.0
 */

(function () {
    'use strict';

    /* =========================================
       1. DETAIL DATA
       ========================================= */

    var DA_DETAILS = {
        /* Rat — Säulen */
        legitim: {
            title: 'Demokratische Legitimation',
            body: 'Delegiertenversammlung aus allen Ortsgruppen und Fachbereichen. Paritätische Doppelspitze (m/w). Wahl alle 2 Jahre. Rückkopplung durch Bedarfserhebung und Feedback-Zyklen.'
        },
        operativ: {
            title: 'Operative gGmbH',
            body: 'Medienproduktion (Newsroom), Bildungsprogramme, IT-Infrastruktur (eigene Server, lokale KI), Fördermittelakquise und -verwaltung. Datenhoheit und digitale Souveränität als Kernprinzip.'
        },
        politisch: {
            title: 'Politischer Arm',
            body: 'Lobbying auf Bundes- und Landesebene, Kampagnenkoordination, strategische Allianzen mit anderen Diaspora-Organisationen, Parteien und zivilgesellschaftlichen Akteuren.'
        },

        /* Rat — Gesellschaftsknoten */
        lokal: {
            title: 'Lokale Räte / Stadtgruppen',
            body: 'Autonome Ortsgruppen in Städten wie Berlin, Köln, Hamburg, Hannover u.\u202fv.\u202fm. Eigene Entscheidungsstrukturen, lokale Vernetzung, Delegierte zum Rat.'
        },
        jugend: {
            title: 'Jugend & Studierende',
            body: 'Studierendeninitiativen, junge Berufstätige, Nachwuchsförderung. Eigene Strukturen mit Vertretung im Rat. Brücke zwischen Tradition und Innovation.'
        },
        wirtschaft: {
            title: 'Unternehmer:innen',
            body: 'Wirtschaftsnetzwerke, Selbständige, Gründer:innen. Ökonomische Selbstorganisation, Mentoring, Investitionskreise, Verbindung von Diaspora-Ökonomie und Gemeinwohl.'
        },
        frauen: {
            title: 'Frauenstrukturen',
            body: 'Autonome feministische Gruppen mit eigenständiger Organisationsform. Frauenrat mit Vetorecht bei geschlechterpolitischen Fragen. Jin, Jiyan, Azadî als gelebtes Prinzip.'
        },
        kultur: {
            title: 'Kultur & Bildung',
            body: 'Sprachkurse (Kurmancî, Soranî, Zazakî), Kulturzentren, Archive, Bibliotheken, Filmfestivals. Bewahrung und Weitergabe kultureller Identität.'
        },
        fach: {
            title: 'Fachnetzwerke',
            body: 'Jurist:innen, Ärzt:innen, IT-Professionals, Ingenieur:innen, Lehrkräfte u.\u202fa. Professionelle Expertise für die Community und den Rat. Mentoring-Systeme und Wissenstransfer.'
        },

        /* Freiheit — Pillar-Details */
        rhythmus: {
            title: 'Rhythmus',
            body: 'Regelmäßige, verlässliche Zeitpunkte für Analyse, Entscheidung und Auswertung. Monatliche Beschlussrunden, quartalsweise Strategietreffen, jährliche Konferenzen. Der Rhythmus verhindert Dauerstress und schafft einen erkennbaren Takt — Freiheit braucht Verlässlichkeit.'
        },
        rollen: {
            title: 'Rollen',
            body: 'Benannte Zuständigkeiten und Stellvertretungen machen Verantwortung sichtbar und verteilen Last. Eine klare Rollenarchitektur verhindert, dass Organisation an wenigen \u201Eunsichtbaren\u201C Träger:innen hängt. Rollen sind keine Hierarchie — sie sind Ermöglichung.'
        },
        routinen: {
            title: 'Routinen',
            body: 'Einfache Standards — Protokollformate, Entscheidungsformen, Kommunikationswege — senken die Einstiegshürde, erhöhen Qualität und ermöglichen Lernfähigkeit durch Wiederholung. Routinen sind das Betriebssystem der Freiheit.'
        },

        /* Intelligenz — Blocks */
        system: {
            title: 'Das System',
            body: 'Staat, Kapital und Patriarchat haben analytische Intelligenz historisch monopolisiert und in den Dienst von Herrschaft gestellt: als kalte, instrumentelle Vernunft, die Effizienz, Kontrolle und Profit über Leben und Gemeinschaft stellt. Gleichzeitig wurde emotionale Intelligenz — Fürsorge, Beziehung, Gemeinschaftssinn — als \u201Eweiblich\u201C abgewertet und aus dem Zentrum der Macht verdrängt.'
        },
        luecke: {
            title: 'Die Lücke',
            body: 'Die Bewegung hat Mut, Empathie, Solidarität und Mobilisierungskraft — aber zu wenig systematische Analytik: Mustererkennung, Begriffsarbeit, Hypothesenbildung, strukturierte Auswertung. Diese Lücke ist kein Schönheitsfehler. Sie ist die zentrale Verwundbarkeit. Ohne Analytik bleibt die Bewegung dem System strukturell unterlegen — nicht an Mut, sondern an Methode.'
        },
        waffe: {
            title: 'Integrierte Intelligenz — Die Waffe',
            body: 'Nicht Analytik gegen Emotion — sondern Analytik, geführt von emotionaler Intelligenz. Der Staat hat Analytik vom Leben abgetrennt. Die demokratische Gesellschaft vereinigt sie wieder. So entstehen Entscheidungen, die klar UND menschenwürdig sind. Strategien, die präzise UND verbunden sind. Das ist kein ethisches Ideal — das ist Gegenmacht.'
        },
        lagebilder: {
            title: 'Lagebilder',
            body: 'Monatliche Analyse von Tendenzen, Risiken und Chancen. Nicht reaktiv auf Ereignisse warten, sondern Entwicklungen vorausdenken. Wer Lagebilder hat, wird nicht überrascht.'
        },
        hypothesen: {
            title: 'Hypothesenlisten',
            body: 'Explizit formulieren, was vermutet wird — und systematisch überprüfen. Das verhindert Gruppendenken und macht Irrtümer korrigierbar. Jede Hypothese hat eine Verantwortliche und einen Überprüfungstermin.'
        },
        entscheid: {
            title: 'Entscheidungsnotizen',
            body: 'Jeder Beschluss wird dokumentiert mit Begründung, Termin und Verantwortung. Keine informellen Absprachen. Nachvollziehbarkeit ist die Grundlage für Lernfähigkeit und Vertrauen.'
        },
        reviews: {
            title: 'Strukturierte Reviews',
            body: 'Quartalsweise Auswertung — was hat gewirkt, was nicht, was lernen wir? Ohne Reviews wiederholt die Bewegung dieselben Muster. Mit Reviews wird sie zur lernenden Organisation.'
        },

        /* Rat — Infrastruktur */
        newsroom: {
            title: 'Newsroom',
            body: 'Professionelle Medienproduktion: Recherche, Redaktion, Publikation. Eigene Narrative statt Abhängigkeit von Fremdberichterstattung. Der Newsroom ist das Sprachrohr der demokratischen Gesellschaft.'
        },
        server: {
            title: 'Server & IT',
            body: 'Eigene Server-Infrastruktur für digitale Souveränität. Keine Abhängigkeit von Big-Tech-Plattformen für kritische Kommunikation und Datenhaltung.'
        },
        ki: {
            title: 'Lokale KI',
            body: 'KI-Werkzeuge auf eigener Infrastruktur: Übersetzung, Analyse, Dokumentenverarbeitung. Datenhoheit bleibt gewahrt — keine Zuflüsse an externe Dienste.'
        },
        daten: {
            title: 'Datenhoheit',
            body: 'Alle Daten der Community verbleiben unter eigener Kontrolle. Verschlüsselung, Zugriffsrechte und Löschkonzepte nach eigenen Standards — nicht nach den AGB von Tech-Konzernen.'
        }
    };

    /* =========================================
       2. INTERSECTION OBSERVER — REVEAL
       ========================================= */

    function initReveal() {
        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    if (!entry.target.hasAttribute('data-repeat')) {
                        observer.unobserve(entry.target);
                    }
                }
            });
        }, {
            threshold: 0.15,
            rootMargin: '0px 0px -60px 0px'
        });

        document.querySelectorAll('.da-reveal, .da-draw-svg, .da-myzel, .da-grow-node').forEach(function (el) {
            observer.observe(el);
        });
    }

    /* =========================================
       3. SVG DASH LENGTH CALCULATION
       ========================================= */

    function initSVGDash() {
        document.querySelectorAll('.da-draw-svg').forEach(function (svg) {
            svg.querySelectorAll('path, line, polyline').forEach(function (el) {
                try {
                    var length = el.getTotalLength();
                    el.style.setProperty('--dash-length', Math.ceil(length));
                } catch (e) {
                    // Some elements may not support getTotalLength
                }
            });
        });
    }

    /* =========================================
       4. DETAIL PANEL LOGIC
       ========================================= */

    function initDetailPanels() {
        var overlay = document.getElementById('da-detail-overlay');
        if (!overlay) return;

        var titleEl = overlay.querySelector('.da-detail-title');
        var bodyEl = overlay.querySelector('.da-detail-body');
        var closeBtn = overlay.querySelector('.da-detail-close');

        function openDetail(key) {
            var data = DA_DETAILS[key];
            if (!data) return;
            titleEl.textContent = data.title;
            bodyEl.textContent = data.body;
            overlay.classList.add('is-open');
            overlay.setAttribute('aria-hidden', 'false');
            closeBtn.focus();
        }

        function closeDetail() {
            overlay.classList.remove('is-open');
            overlay.setAttribute('aria-hidden', 'true');
        }

        // Event delegation for all [data-detail] elements
        document.addEventListener('click', function (e) {
            var trigger = e.target.closest('[data-detail]');
            if (trigger) {
                e.preventDefault();
                openDetail(trigger.getAttribute('data-detail'));
            }
        });

        // Keyboard: Enter/Space on [data-detail]
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.key === ' ') {
                var trigger = e.target.closest('[data-detail]');
                if (trigger) {
                    e.preventDefault();
                    openDetail(trigger.getAttribute('data-detail'));
                }
            }
        });

        // Close
        closeBtn.addEventListener('click', closeDetail);

        overlay.addEventListener('click', function (e) {
            if (e.target === overlay) closeDetail();
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && overlay.classList.contains('is-open')) {
                closeDetail();
            }
        });
    }

    /* =========================================
       5. SMOOTH SCROLL
       ========================================= */

    function initSmoothScroll() {
        document.querySelectorAll('a[href^="#"]').forEach(function (link) {
            link.addEventListener('click', function (e) {
                var targetId = this.getAttribute('href');
                if (targetId === '#') return;

                var target = document.querySelector(targetId);
                if (target) {
                    e.preventDefault();
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        });
    }

    /* =========================================
       6. BODY CLASS FOR THEME OVERRIDE
       ========================================= */

    function initPageClass() {
        document.body.classList.add('da-page-active');
    }

    /* =========================================
       7. INIT
       ========================================= */

    function init() {
        initPageClass();
        initSVGDash();
        initReveal();
        initDetailPanels();
        initSmoothScroll();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

})();
