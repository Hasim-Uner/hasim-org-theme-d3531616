<?php
/**
 * Hasimuener Journal — Bootstrap
 *
 * Zentraler Einstiegspunkt des Child-Themes.
 * Lädt modulare Includes aus /inc/ in definierter Reihenfolge.
 *
 * Architektur-Prinzip: Diese Datei enthält KEINE Businesslogik.
 * Jedes Modul ist eigenständig testbar und austauschbar.
 *
 * Ladereihenfolge:
 * 1. Helpers         → Utility-Funktionen (Lesedauer, Body-Klassen)
 * 2. Post Types      → CPT-Registrierung (essay, note)
 * 3. Taxonomies      → Taxonomie „topic" + Default-Terms
 * 4. Enqueue         → Asset-Loading, Preload, Defer
 * 5. GP Compat       → GeneratePress Meta-Unterdrückung
 * 6. Meta Fields     → Social-Teaser (Editor-Panel + REST)
 * 7. SEO Schema      → JSON-LD für Essays
 * 8. SEO Meta        → Description, Open Graph, Twitter Cards
 *
 * @package Hasimuener_Journal
 * @version 4.0.0
 */

defined( 'ABSPATH' ) || exit;

/*
 * ROBOTS.TXT — Statische Datei im Theme-Root.
 * Falls /robots.txt trotzdem HTML ausliefert: physische
 * robots.txt im WordPress-Root ablegen (Nginx-Cache Hostpress).
 */

/* =========================================
   MODULE LADEN
   ========================================= */

$hp_inc_dir = get_stylesheet_directory() . '/inc';

require_once $hp_inc_dir . '/helpers.php';
require_once $hp_inc_dir . '/post-types.php';
require_once $hp_inc_dir . '/taxonomies.php';
require_once $hp_inc_dir . '/enqueue.php';
require_once $hp_inc_dir . '/generatepress-compat.php';
require_once $hp_inc_dir . '/meta-fields.php';
require_once $hp_inc_dir . '/seo-schema.php';
require_once $hp_inc_dir . '/seo-meta.php';
require_once $hp_inc_dir . '/glossary.php';
require_once $hp_inc_dir . '/breadcrumbs.php';
require_once $hp_inc_dir . '/header-nav.php';
require_once $hp_inc_dir . '/comments.php';
require_once $hp_inc_dir . '/contacts-admin.php';
require_once $hp_inc_dir . '/contact.php';
require_once $hp_inc_dir . '/newsletter.php';
require_once $hp_inc_dir . '/privacy-maintenance.php';
require_once $hp_inc_dir . '/graph-api.php';
require_once $hp_inc_dir . '/votes.php';
require_once $hp_inc_dir . '/votes-api.php';
require_once $hp_inc_dir . '/stitch-admin.php';
