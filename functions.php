<?php
/**
 * Hasimuener Journal — Bootstrap
 *
 * Zentraler Einstiegspunkt des Child-Themes.
 * Delegiert das Laden der Module an inc/bootstrap.php.
 *
 * Architektur-Prinzip: Diese Datei enthält KEINE Businesslogik.
 * Jedes Modul ist eigenständig testbar und austauschbar.
 *
 * Die konkrete Ladereihenfolge steht in inc/manifest.php.
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

require_once get_stylesheet_directory() . '/inc/bootstrap.php';
