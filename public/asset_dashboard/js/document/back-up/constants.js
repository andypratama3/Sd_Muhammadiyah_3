/**
 * constants.js — A4 dimensions, snap thresholds, global state
 *
 * A4 @ 96 DPI (CSS standard):
 *   210mm × 3.7795 = 794px
 *   297mm × 3.7795 = 1123px
 *   1px = 0.75pt (exact at 96dpi)
 *   Margin 20mm = 76px
 */

// ── Unit conversions ──────────────────────────────────────────
var MM_TO_PX = 96 / 25.4;   // 3.779527... px per mm
var PX_TO_PT = 72 / 96;     // 0.75 pt per px (exact)
var R        = PX_TO_PT;    // alias untuk backward-compat

// ── Canvas dimensions ─────────────────────────────────────────
var CANVAS_W = Math.round(210 * MM_TO_PX);  // 794 px
var CANVAS_H = Math.round(297 * MM_TO_PX);  // 1123 px
var MARGIN   = Math.round(20  * MM_TO_PX);  // 76 px  (20mm @page margin)

// ── Grid spacing ──────────────────────────────────────────────
var GRID_MINOR_PX = 5  * MM_TO_PX;  // 5mm  = 18.90px
var GRID_MAJOR_PX = 10 * MM_TO_PX;  // 10mm = 37.80px

// ── PDF export (pt) ───────────────────────────────────────────
var A4_W = 210 * (72 / 25.4);  // 595.276 pt
var A4_H = 297 * (72 / 25.4);  // 841.890 pt

// ── Snap / guide thresholds ───────────────────────────────────
var SNAP_THRESHOLD = 6;   // px — pull-in distance
var SNAP_RELEASE   = 10;  // px — release distance (hysteresis)
var GUIDE_FADE_IN  = 0.22;
var GUIDE_FADE_OUT = 0.15;

// ── Global state ──────────────────────────────────────────────
var TABLE_STORE  = {};
var tableCounter = 0;
var pages        = [];
var currentPage  = 0;
var snapEnabled  = true;
var currentZoom  = 1;
var _clipboard   = null;
