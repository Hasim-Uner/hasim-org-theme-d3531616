/**
 * Diaspora-Architektur — Scroll-Storytelling Engine
 *
 * Vanilla JS: Intersection Observer reveals, Detail Panels,
 * SVG draw calculations, smooth scroll, keyboard nav.
 *
 * Budget: < 60KB (inkl. D3-force Subset).
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
            title: 'Der andere Pol — Staat als institutionelle Realität',
            body: 'Der Staat ist kein Feind, den es zu besiegen gilt — er ist der andere Pol, der das politische Spiel bereits professionell spielt. Er hat Lagebilder, Strategiepapiere, institutionelle Kontinuität, analytische Infrastruktur und mediale Reichweite. Die Frage ist nicht, ob wir ihn abschaffen — das ist unrealistisch. Die Frage ist: Können wir ihm als eigenständige, ebenbürtige institutionelle Kraft gegenübertreten? Das geht nur, wenn wir das Spiel mindestens genauso professionell spielen — mit derselben methodischen Strenge, aber verbunden mit dem, was der Staat strukturell nicht kann: Empathie, Gemeinschaftssinn, demokratische Legitimation von unten.'
        },
        luecke: {
            title: 'Die Lücke — Warum wir noch nicht ebenbürtig sind',
            body: 'Der Staat hat Lagebilder — wir nicht. Der Staat hat systematische Auswertung — wir nicht. Der Staat hat institutionelle Kontinuität — wir haben Überlastung Einzelner. Der Staat hat mediale Infrastruktur — wir nutzen Plattformen Dritter. Diese Lücke ist keine moralische Schwäche — es ist ein Professionalisierungsdefizit. Und es ist die zentrale Frage: Spielen wir das Spiel auf dem gleichen Niveau, oder bleiben wir auf der Ebene von Appellen, Reaktionen und spontaner Mobilisierung?'
        },
        waffe: {
            title: 'Augenhöhe — Integrierte Intelligenz',
            body: 'Der Staat hat analytische Methoden, aber keine Empathie. Wir haben Empathie, aber zu wenig analytische Methoden. Integrierte Intelligenz schließt beides: dieselbe Schärfe in der Analyse, dieselbe Professionalität in der Methode — verbunden mit emotionaler Intelligenz, Würde und Gemeinschaftssinn. Das Ergebnis ist nicht Gegnerschaft, sondern Augenhöhe plus Menschlichkeit. Erst wenn wir das Spiel genauso gut spielen, können wir den zweiten Pol bilden, der die Reichweite des Staates begrenzt.'
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

        /* Rose */
        rose: {
            title: 'Die Rose — Warum innere Struktur zählt',
            body: 'Die Rose weiß, dass sie schön ist. Und genau deshalb richtet sie ihre innere Struktur danach aus — Duft, Form, Farbe, Stabilität sind bewusste Arbeit, kein Zufall. Übertragen auf uns: Wir wissen, was wir sein wollen — frei, dezentral, demokratisch, selbstorganisiert. Die Frage ist nicht, ob wir das glauben. Die Frage ist, ob unsere innere Struktur dem entspricht. Organisation ist der Akt, die Differenz zwischen Anspruch und Wirklichkeit zu schließen. So konsequent, wie die Rose es tut.'
        },
        prueffrage: {
            title: 'Die Prüffrage',
            body: 'Sind wir das, was wir zu sein glauben? Wir sagen: Wir sind dezentral, frei, demokratisch. Aber haben wir die Strukturen, die das tragen? Klare Rollen, verlässliche Rhythmen, einfache Routinen? Oder läuft vieles informell, an Einzelnen, reaktiv? Die Rose schließt diese Lücke durch innere Ordnung. Das ist unser Auftrag: Nicht nur wissen, was wir sein wollen — sondern die Struktur danach bauen.'
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
        },

        /* Konzept 04 — Mediale Architektur: Schichten */
        souveraenitaet: {
            title: 'Schicht 1 — Digitale Souveränität',
            body: 'Das Fundament. Eigene Server (Hetzner, DE-Jurisdiktion), keine Cloud-Abhängigkeiten. Keine Cookies, kein externes Tracking. Server-side Datenerfassung unter eigener Kontrolle. Selbstgehostete Automatisierung (n8n). Lokale KI-Instanzen für Textarbeit und Analyse. Datenhoheit bedeutet: Niemand kann uns den Zugang zu unseren eigenen Daten entziehen. Diese Seite ist der Beweis.'
        },
        medien: {
            title: 'Schicht 2 — Medienproduktion',
            body: 'Ein kleines, aber professionelles Medienteam mit klaren Zuständigkeiten. Abgestimmte Bild- und Sprachlinie — eigene Begriffe statt übernommene Narrative. Krisenkommunikationsprotokoll: Informationssammlung → erste Haltung → ausführliche Einordnung. Öffentliche Stellungnahmen folgen der Struktur: Tatsache – Haltung – Handlung – Angebot.'
        },
        kommunikation: {
            title: 'Schicht 3 — Kommunikation & Koordination',
            body: 'Zentrale digitale Plattform für Dokumente, Protokolle, Material. Gemeinsamer Kommunikationskanal für jede lokale Struktur. Stringente Regeln zu Zugängen und Passwortverwaltung. Die Koordinationsebene verbindet lokale Strukturen mit dem Rat — bidirektional, nicht als Weisungskette.'
        },
        analyse: {
            title: 'Schicht 4 — Inhalt & Analyse',
            body: 'Hier lebt die integrierte Intelligenz in der Praxis: Monatliche Lagebilder (Tendenzen, Risiken, Chancen). Hypothesenlisten — explizit, überprüfbar, verantwortlich zugeordnet. Entscheidungsnotizen — Beschluss, Begründung, Termin, Verantwortliche. Quartalsweise Reviews — was hat gewirkt, was nicht, was lernen wir? Ohne diese Schicht bleibt Analytik ein Vorsatz.'
        },
        oeffentlichkeit: {
            title: 'Schicht 5 — Öffentlichkeit & Wirkung',
            body: 'Die sichtbare Spitze: Kampagnen, Stellungnahmen, Bündnisse, Allianzen. Aber nur wirksam, wenn die vier Schichten darunter tragen. Eine Pressemitteilung ohne Lagebild ist Reaktion. Eine Kampagne ohne Analyse ist Aktionismus. Die obere Schicht ist die Wirkung — die unteren sind die Ursache.'
        },

        /* Konzept 04 — Module */
        newsroom_med: {
            title: 'Modul: Newsroom',
            body: 'Eigene Stimme, eigene Begriffe, eigene Prioritäten. Kein Outsourcing an Social-Media-Plattformen, die Reichweite nach Algorithmus verteilen. Ein Newsroom auf eigener Infrastruktur bedeutet: Wir entscheiden, was sichtbar wird. Nicht Meta, nicht X, nicht Google.'
        },
        wissensgraph_med: {
            title: 'Modul: Wissensgraph',
            body: 'Politisches Wissen wird strukturiert, vernetzt, durchsuchbar. Begriffe sind nicht isolierte Glossareinträge, sondern Knoten in einem lebendigen Netzwerk. Der Wissensgraph auf hasimuener.org zeigt, wie das funktioniert — Begriffe wie „demokratische Gesellschaft", „Gegenpol", „integrierte Intelligenz" sind miteinander verknüpft und kontextualisiert.'
        },
        sicherheit_med: {
            title: 'Modul: Digitaler Selbstschutz',
            body: 'Digitale Sicherheit ist kein Sonderwissen für Expert:innen — sie ist Organisationsstandard. Verschlüsselte Kommunikation, Passwortverwaltung, Zugangskontrolle, Datensparsamkeit. Eingebaut in den Alltag, nicht als Sonderschulung. Feministische Sicherheitsstandards eingeschlossen: Schutz vor digitaler Gewalt und Doxxing.'
        },

        /* Inspirationsquelle */
        konfoederalismus: {
            title: 'Demokratischer Konföderalismus',
            body: 'Das Paradigma des Demokratischen Konföderalismus verlagert politische Entscheidungsmacht zurück in die Basis — Nachbarschaft, Kommune, Rat. Es zielt nicht auf die Gründung eines neuen Nationalstaats, sondern auf den Aufbau eines funktionalen demokratischen Systems innerhalb der bestehenden Zivilgesellschaft. Für die Diaspora bedeutet das: Wir sind keine Exilanten, die auf einen Staat warten. Wir sind Akteure, die hier und jetzt eine demokratische Gesellschaft bauen — als gleichberechtigter Pol neben dem Staat.'
        },
        kommune: {
            title: 'Die Kommune als Grundzelle',
            body: 'Die Kommune ist die kleinste basisdemokratische Einheit. Entscheidungen entstehen durch direkten Konsens, nicht durch Delegation an Repräsentanten. Jede lokale Struktur — Stadtgruppe, Fachgruppe, Frauenrat — organisiert sich nach diesem Prinzip. Das ist keine romantische Vorstellung: Es erfordert Rhythmus, Rollen und Routinen — organisierte Freiheit, wie in Konzept 01 beschrieben.'
        },
        nicht_verhandelbar: {
            title: 'Nicht verhandelbare Säulen',
            body: 'Frauenbefreiung und ökologische Nachhaltigkeit sind keine Programmpunkte, die man bei Bedarf streichen kann. Sie sind strukturelle Voraussetzungen: Ohne autonome Frauenstrukturen mit eigenen Entscheidungsrechten gibt es keine demokratische Gesellschaft — nur eine modernisierte Variante des Patriarchats. Ohne ökologisches Bewusstsein gibt es keine nachhaltige Organisation — nur kurzfristigen Aktivismus.'
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
            svg.querySelectorAll('path, line, polyline, circle, ellipse').forEach(function (el) {
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
       5. STICKY TOC — SCROLL TRACKING
       ========================================= */

    function initTOC() {
        var toc = document.getElementById('da-toc');
        if (!toc) return;

        var items = toc.querySelectorAll('.da-toc__item');
        var sectionIds = [];
        items.forEach(function (item) {
            sectionIds.push(item.getAttribute('data-section'));
        });

        var sections = sectionIds.map(function (id) {
            return document.getElementById(id);
        }).filter(Boolean);

        // Show TOC after scrolling past hero
        var heroEnd = 300;
        function updateTOCVisibility() {
            if (window.scrollY > heroEnd) {
                toc.classList.add('is-visible');
            } else {
                toc.classList.remove('is-visible');
            }
        }

        // Track active section
        var tocObserver = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    var id = entry.target.id;
                    items.forEach(function (item) {
                        item.classList.toggle('da-toc__item--active', item.getAttribute('data-section') === id);
                    });
                }
            });
        }, {
            threshold: 0,
            rootMargin: '-40% 0px -55% 0px'
        });

        sections.forEach(function (s) { tocObserver.observe(s); });

        // Scroll progress
        function updateProgress() {
            var scrollTop = window.scrollY;
            var docHeight = document.documentElement.scrollHeight - window.innerHeight;
            var progress = docHeight > 0 ? Math.min(100, (scrollTop / docHeight) * 100) : 0;
            toc.style.setProperty('--da-scroll-progress', progress + '%');
            updateTOCVisibility();
        }

        window.addEventListener('scroll', updateProgress, { passive: true });
        updateProgress();
    }

    /* =========================================
       6. SMOOTH SCROLL (inkl. TOC-Links)
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
       6. D3 FORCE GRAPH — GESELLSCHAFTSKNOTEN
       ========================================= */

    function initD3Graph() {
        if (typeof d3 === 'undefined') return;

        var container = document.getElementById('da-gesellschaft-graph');
        if (!container) return;

        var graphObserver = new IntersectionObserver(function (entries) {
            if (entries[0].isIntersecting) {
                graphObserver.disconnect();
                buildD3Graph(container);
            }
        }, { threshold: 0.05 });

        graphObserver.observe(container);
    }

    function buildD3Graph(container) {
        // Remove placeholder text
        var placeholder = container.querySelector('.da-rat__d3-placeholder');
        if (placeholder) placeholder.remove();

        var width = container.offsetWidth || 720;
        var height = 320;

        var nodes = [
            { id: 'berlin',       label: 'Berlin',          category: 'lokal',      radius: 22 },
            { id: 'koeln',        label: 'Köln',             category: 'lokal',      radius: 22 },
            { id: 'hamburg',      label: 'Hamburg',          category: 'lokal',      radius: 20 },
            { id: 'hannover',     label: 'Hannover',         category: 'lokal',      radius: 20 },
            { id: 'studierende',  label: 'Studierende',      category: 'jugend',     radius: 20 },
            { id: 'junge_berufs', label: 'Junge Berufst.',   category: 'jugend',     radius: 22 },
            { id: 'wirtschaft',   label: 'Wirtschaftsnetw.', category: 'wirtschaft', radius: 22 },
            { id: 'selbst',       label: 'Selbständige',     category: 'wirtschaft', radius: 20 },
            { id: 'feministisch', label: 'Femin. Gruppen',   category: 'frauen',     radius: 22 },
            { id: 'frauenrat',    label: 'Frauenrat',        category: 'frauen',     radius: 20 },
            { id: 'sprachkurse',  label: 'Sprachkurse',      category: 'kultur',     radius: 20 },
            { id: 'kulturz',      label: 'Kulturzentren',    category: 'kultur',     radius: 22 },
            { id: 'juristen',     label: 'Jurist:innen',     category: 'fach',       radius: 22 },
            { id: 'it',           label: 'IT-Professionals', category: 'fach',       radius: 24 },
            { id: 'aerzte',       label: 'Ärzt:innen',       category: 'fach',       radius: 22 }
        ];

        var links = [
            { source: 'berlin',       target: 'koeln'        },
            { source: 'berlin',       target: 'hannover'     },
            { source: 'koeln',        target: 'hamburg'      },
            { source: 'hamburg',      target: 'hannover'     },
            { source: 'studierende',  target: 'junge_berufs' },
            { source: 'studierende',  target: 'berlin'       },
            { source: 'wirtschaft',   target: 'selbst'       },
            { source: 'selbst',       target: 'koeln'        },
            { source: 'feministisch', target: 'frauenrat'    },
            { source: 'frauenrat',    target: 'berlin'       },
            { source: 'sprachkurse',  target: 'kulturz'      },
            { source: 'kulturz',      target: 'hamburg'      },
            { source: 'juristen',     target: 'it'           },
            { source: 'it',           target: 'aerzte'       },
            { source: 'aerzte',       target: 'hannover'     },
            { source: 'it',           target: 'junge_berufs' },
            { source: 'wirtschaft',   target: 'juristen'     },
            { source: 'feministisch', target: 'kulturz'      },
            { source: 'berlin',       target: 'frauenrat'    },
            { source: 'koeln',        target: 'kulturz'      }
        ];

        // Read CSS variable colors (robust fallbacks)
        var cs = getComputedStyle(document.documentElement);
        function cssVar(name, fallback) {
            var v = cs.getPropertyValue(name).trim();
            return v || fallback;
        }

        var categoryColor = {
            lokal:      cssVar('--da-green',  'hsl(120 50% 45%)'),
            jugend:     cssVar('--da-purple', 'hsl(260 55% 58%)'),
            wirtschaft: cssVar('--da-amber',  'hsl(40 85% 55%)'),
            frauen:     cssVar('--da-pink',   'hsl(340 60% 55%)'),
            kultur:     cssVar('--da-teal',   'hsl(160 70% 38%)'),
            fach:       cssVar('--da-blue',   'hsl(210 70% 50%)')
        };

        var detailKeyMap = {
            lokal: 'lokal', jugend: 'jugend', wirtschaft: 'wirtschaft',
            frauen: 'frauen', kultur: 'kultur', fach: 'fach'
        };

        var svg = d3.select(container)
            .append('svg')
            .attr('width', '100%')
            .attr('height', height)
            .attr('viewBox', '0 0 ' + width + ' ' + height)
            .attr('role', 'img')
            .attr('aria-label', 'Myzel-Netzwerk der demokratischen Gesellschaft');

        var simulation = d3.forceSimulation(nodes)
            .force('charge', d3.forceManyBody().strength(-90))
            .force('center', d3.forceCenter(width / 2, height / 2))
            .force('collision', d3.forceCollide().radius(function (d) { return d.radius + 10; }))
            .force('link', d3.forceLink(links).id(function (d) { return d.id; }).distance(65).strength(0.3))
            .alphaDecay(0.025);

        var link = svg.append('g')
            .attr('aria-hidden', 'true')
            .selectAll('line')
            .data(links)
            .enter()
            .append('line')
            .attr('stroke', 'rgba(34,50,70,0.22)')
            .attr('stroke-width', 0.85);

        var node = svg.append('g')
            .selectAll('g')
            .data(nodes)
            .enter()
            .append('g')
            .attr('tabindex', '0')
            .attr('role', 'button')
            .attr('data-detail', function (d) { return detailKeyMap[d.category] || d.category; })
            .attr('aria-label', function (d) { return 'Detail: ' + d.label; });

        node.append('circle')
            .attr('r', function (d) { return d.radius; })
            .attr('fill', function (d) { return categoryColor[d.category] || 'rgba(34,50,70,0.12)'; })
            .attr('opacity', 0.18)
            .attr('stroke', function (d) { return categoryColor[d.category] || '#223246'; })
            .attr('stroke-width', 1.5);

        node.append('text')
            .text(function (d) { return d.label; })
            .attr('text-anchor', 'middle')
            .attr('dy', '0.35em')
            .attr('font-family', 'Outfit, sans-serif')
            .attr('font-size', 9)
            .attr('font-weight', '600')
            .attr('fill', '#222222')
            .attr('pointer-events', 'none');

        simulation.on('tick', function () {
            var r0 = 4;
            link
                .attr('x1', function (d) { return d.source.x; })
                .attr('y1', function (d) { return d.source.y; })
                .attr('x2', function (d) { return d.target.x; })
                .attr('y2', function (d) { return d.target.y; });

            node.attr('transform', function (d) {
                var x = Math.max(d.radius + r0, Math.min(width  - d.radius - r0, d.x));
                var y = Math.max(d.radius + r0, Math.min(height - d.radius - r0, d.y));
                return 'translate(' + x + ',' + y + ')';
            });
        });

        // Stop simulation after 4 s (GPU rest)
        setTimeout(function () { simulation.stop(); }, 4000);

        // Hover: highlight connected links
        node.on('mouseenter', function (event, d) {
            link.attr('opacity', function (l) {
                return (l.source.id === d.id || l.target.id === d.id) ? 1 : 0.08;
            });
            link.attr('stroke-width', function (l) {
                return (l.source.id === d.id || l.target.id === d.id) ? 1.6 : 0.85;
            });
        }).on('mouseleave', function () {
            link.attr('opacity', 1).attr('stroke-width', 0.85);
        });
    }

    /* =========================================
       7. BODY CLASS FOR THEME OVERRIDE
       ========================================= */

    function initPageClass() {
        document.body.classList.add('da-page-active');
    }

    /* =========================================
       7. KAMPAGNEN-WIDGET
       ========================================= */

    var DA_KAMPAGNE = {
        layers: [
            { num: 'Schicht 5', name: 'Öffentlichkeit & Wirkung' },
            { num: 'Schicht 4', name: 'Inhalt & Analyse' },
            { num: 'Schicht 3', name: 'Kommunikation & Koordination' },
            { num: 'Schicht 2', name: 'Medienproduktion' },
            { num: 'Schicht 1', name: 'Digitale Souveränität' }
        ],

        phases: [
            {
                id: 'analyse',
                label: 'Phase 1: Lagebild',
                title: 'Lagebild erstellen — bevor ein Wort nach außen geht',
                activeLayers: [1, 2],
                body: 'Jede Kampagne beginnt mit Analyse, nicht mit Kommunikation. Das Lagebild beantwortet: Wie wird die kurdische Community aktuell wahrgenommen? Welche politischen Fenster sind offen? Wer sind potenzielle Bündnispartner? Welche Narrative dominieren?',
                actions: [
                    'Monatliches Lagebild des Vorstands auswerten',
                    'Hypothese formulieren: \u201ePositionierung als Akteur erhöht Gesprächsanfragen um 30%\u201c',
                    'Zielgruppen definieren: Politik, Medien, Bündnispartner, eigene Community'
                ],
                output: 'Lagebild-Dokument + Hypothesenliste + Zielgruppen-Matrix',
                insight: 'Schicht 4 liefert die Analyse. Schicht 3 koordiniert die Informationssammlung aus lokalen Strukturen. Ohne diesen Schritt ist alles, was folgt, Aktionismus.'
            },
            {
                id: 'produktion',
                label: 'Phase 2: Produktion',
                title: 'Eigene Stimme, eigene Begriffe — auf eigener Infrastruktur',
                activeLayers: [1, 2, 3, 4],
                body: 'Das Medienteam produziert alle Materialien auf eigener Infrastruktur. Kein Outsourcing, keine Plattformabhängigkeit. Die Bild- und Sprachlinie steht: professionell, ruhig, institutionell — nicht aktivistisch.',
                actions: [
                    'Pressemitteilung: \u201eKurdischer Rat Deutschland gegründet — demokratisch legitimiert, professionell organisiert\u201c',
                    'Hintergrundpapier (2 Seiten): Struktur, Legitimation, Forderungen',
                    'Visual Identity: Logo, Briefkopf, Social-Media-Templates',
                    'Website-Seite mit Selbstdarstellung, Gremien, Kontakt',
                    'Lokale Strukturen werden gebrieft — einheitliche Sprache, einheitliche Kernbotschaft'
                ],
                output: 'Pressemitteilung + Hintergrundpapier + Visuals + Website + Briefing-Paket für Ortsgruppen',
                insight: 'Schicht 1 (eigene Server) trägt Schicht 2 (Medienproduktion). Schicht 3 koordiniert das Briefing der lokalen Strukturen. Alles läuft auf eigener Infrastruktur — kein Zufall, sondern Architektur.'
            },
            {
                id: 'launch',
                label: 'Phase 3: Launch',
                title: 'Öffentlicher Auftritt — als Institution, nicht als Demo',
                activeLayers: [0, 1, 2, 3, 4],
                body: 'Der Rat tritt zum ersten Mal öffentlich auf. Nicht als Protestbewegung, sondern als institutioneller Gesprächspartner. Die Botschaft: Wir sind organisiert, legitimiert und gesprächsbereit.',
                actions: [
                    'Pressekonferenz oder Pressebriefing mit ausgewählten Journalist:innen',
                    'Parallele Veröffentlichung auf eigener Website und eigenen Kanälen',
                    'Direkte Anschreiben an Bundestagsabgeordnete, Landtagsfraktionen, relevante NGOs',
                    'Lokale Strukturen laden zeitgleich lokale Ansprechpartner:innen ein',
                    'Social Media: Keine Memes, keine Parolen — institutionelle Kommunikation'
                ],
                output: 'Pressekonferenz + 20 direkte Anschreiben + Social-Media-Kampagne + lokale Termine',
                insight: 'Jetzt arbeiten alle fünf Schichten. Schicht 5 (Öffentlichkeit) funktioniert nur, weil die vier Schichten darunter tragen. Die Pressekonferenz ist die sichtbare Spitze — Analyse, Produktion, Koordination und Infrastruktur sind die Ursache.'
            },
            {
                id: 'review',
                label: 'Phase 4: Auswertung',
                title: 'Review — was hat gewirkt, was nicht?',
                activeLayers: [1, 2],
                body: 'Zwei Wochen nach dem Launch: strukturierte Auswertung. Keine Bauchgefühle, keine informellen Einschätzungen — methodische Analyse. Hier trennt sich professionelle Organisation von Aktionismus.',
                actions: [
                    'Hypothese prüfen: Wurden mehr Gesprächsanfragen generiert?',
                    'Medienresonanz dokumentieren: Wer hat berichtet? Welche Begriffe wurden übernommen?',
                    'Rückmeldungen aus lokalen Strukturen sammeln',
                    'Entscheidungsnotiz: Was wiederholen wir? Was ändern wir? Wer ist verantwortlich?',
                    'Ergebnis in nächstes monatliches Lagebild einarbeiten'
                ],
                output: 'Review-Dokument + aktualisiertes Lagebild + Entscheidungsnotiz für nächste Kampagne',
                insight: 'Der Kreis schließt sich. Die Auswertung fließt zurück in Schicht 4 (Analyse) — und wird zur Grundlage der nächsten Kampagne. So entsteht eine lernende Organisation. Reaktiv wird gestaltend.'
            }
        ]
    };

    function initKampagne() {
        var data = DA_KAMPAGNE;
        var stackEl = document.getElementById('da-k-stack');
        var detailEl = document.getElementById('da-k-detail');
        var navEl = document.getElementById('da-k-nav');
        var tlEl = document.getElementById('da-k-timeline');
        var insightEl = document.getElementById('da-k-insight');

        if (!stackEl || !detailEl || !navEl || !tlEl || !insightEl) return;

        // Schichten-Stack rendern (Schicht 5 oben, Schicht 1 unten)
        data.layers.forEach(function(layer, i) {
            var div = document.createElement('div');
            div.className = 'da-k-layer';
            div.setAttribute('data-layer-index', i);
            div.innerHTML =
                '<div class="da-k-layer-num">' + layer.num + '</div>' +
                '<div class="da-k-layer-name">' + layer.name + '</div>';
            stackEl.appendChild(div);
        });

        // Phase-Navigation rendern
        data.phases.forEach(function(phase, i) {
            var btn = document.createElement('button');
            btn.className = 'da-k-nav-btn';
            btn.textContent = phase.label;
            btn.addEventListener('click', function() { showPhase(i); });
            navEl.appendChild(btn);
        });

        // Timeline rendern
        data.phases.forEach(function(phase) {
            var step = document.createElement('div');
            step.className = 'da-k-tl-step';
            step.innerHTML =
                '<div class="da-k-tl-dot"></div>' +
                '<div class="da-k-tl-label">' + phase.label.replace('Phase ', 'P') + '</div>';
            tlEl.appendChild(step);
        });

        function showPhase(idx) {
            var phase = data.phases[idx];

            // Nav-Buttons
            navEl.querySelectorAll('.da-k-nav-btn').forEach(function(btn, i) {
                btn.classList.toggle('active', i === idx);
            });

            // Schichten aktivieren/deaktivieren
            stackEl.querySelectorAll('.da-k-layer').forEach(function(layer, i) {
                layer.classList.toggle('active', phase.activeLayers.indexOf(i) !== -1);
            });

            // Timeline
            tlEl.querySelectorAll('.da-k-tl-step').forEach(function(step, i) {
                step.classList.toggle('done', i < idx);
                step.classList.toggle('now', i === idx);
            });

            // Detail-Panel
            var html = '';
            html += '<div class="da-k-detail-phase">' + phase.label + '</div>';
            html += '<div class="da-k-detail-title">' + phase.title + '</div>';
            html += '<div class="da-k-detail-body">' + phase.body + '</div>';
            html += '<div class="da-k-actions">';
            phase.actions.forEach(function(action) {
                html += '<div class="da-k-action">';
                html += '<div class="da-k-action-dot"></div>';
                html += '<span>' + action + '</span>';
                html += '</div>';
            });
            html += '</div>';
            html += '<div class="da-k-output">';
            html += '<div class="da-k-output-label">Output</div>';
            html += '<div class="da-k-output-text">' + phase.output + '</div>';
            html += '</div>';

            detailEl.innerHTML = html;

            // Insight-Box
            insightEl.textContent = phase.insight;
        }

        // Start mit Phase 1
        showPhase(0);

        // Intersection Observer für Scroll-Animation
        var widget = document.querySelector('.da-kampagne-widget');
        if (widget && typeof IntersectionObserver !== 'undefined') {
            var obs = new IntersectionObserver(function(entries) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('is-visible');
                        obs.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.15, rootMargin: '0px 0px -60px 0px' });
            obs.observe(widget);
        }
    }

    /* =========================================
       8. INIT
       ========================================= */

    function init() {
        initPageClass();
        initSVGDash();
        initReveal();
        initDetailPanels();
        initSmoothScroll();
        initTOC();
        initD3Graph();
        initKampagne();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

})();
