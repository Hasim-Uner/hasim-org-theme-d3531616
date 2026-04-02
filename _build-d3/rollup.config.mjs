import { nodeResolve } from '@rollup/plugin-node-resolve';
import terser from '@rollup/plugin-terser';

export default {
    input: 'src/d3-custom.js',
    output: {
        file: '../assets/js/d3-custom.min.js',
        format: 'umd',
        name: 'd3',
    },
    plugins: [ nodeResolve(), terser() ]
};
