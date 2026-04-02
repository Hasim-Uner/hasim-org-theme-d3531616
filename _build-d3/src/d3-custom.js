/**
 * Custom D3 Bundle — Hasimuener Journal
 *
 * Enthält nur die Module, die graph.js tatsächlich nutzt:
 *   - d3-selection (select, selectAll)
 *   - d3-scale (scaleSqrt)
 *   - d3-zoom (zoom, zoomIdentity)
 *   - d3-drag (drag)
 *   - d3-force (forceSimulation, forceLink, forceManyBody,
 *               forceCenter, forceCollide, forceX, forceY)
 *
 * Einmaliger Build: npm install && npm run build
 * Ergebnis: /assets/js/d3-custom.min.js (~80–100 KB)
 */

export { select, selectAll } from 'd3-selection';
export { scaleSqrt } from 'd3-scale';
export { zoom, zoomIdentity } from 'd3-zoom';
export { drag } from 'd3-drag';
export {
    forceSimulation,
    forceLink,
    forceManyBody,
    forceCenter,
    forceCollide,
    forceX,
    forceY
} from 'd3-force';
