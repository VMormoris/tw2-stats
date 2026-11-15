import { defineComponent, h } from 'vue';
import { Scatter, Bar, Pie } from 'vue-chartjs';
import { Chart, registerables } from 'chart.js';
Chart.register(...registerables);

/**
 * Wraps a vue-chartjs component in the markup vue-chart-3 rendered, which the
 * pages are laid out for: a relative container that receives the component's
 * attributes, around a 400x400 canvas limited to the container's size.
 */
function framed(component)
{
    return defineComponent({
        inheritAttrs: false,
        props: { data: Object, options: Object },
        setup(props, { attrs })
        {
            return () => h('div', { ...attrs, style: [{ maxWidth: '100%' }, attrs.style, { position: 'relative' }] }, [
                h(component, {
                    data: props.data,
                    options: props.options ?? {},
                    width: 400,
                    height: 400,
                    style: { maxWidth: '100%', maxHeight: '100%' }
                })
            ]);
        }
    });
}

export const ScatterChart = framed(Scatter);
export const BarChart = framed(Bar);
export const PieChart = framed(Pie);
