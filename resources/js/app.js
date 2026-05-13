import './bootstrap';
import { Chart, registerables } from 'chart.js';
Chart.register(...registerables);

// Inicializa dark mode antes do Alpine para evitar flash
const theme = localStorage.getItem('theme');
if (theme === 'dark' || (!theme && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
    document.documentElement.classList.add('dark');
}

// Alpine component para Chart.js — registrado antes da inicialização do Alpine
document.addEventListener('alpine:init', () => {
    Alpine.data('appointmentChart', (labels, values) => ({
        chart: null,
        observer: null,
        get dark() { return document.documentElement.classList.contains('dark'); },
        init() {
            this.render();
            this.observer = new MutationObserver(() => this.render());
            this.observer.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
        },
        destroy() {
            if (this.observer) this.observer.disconnect();
            if (this.chart)   this.chart.destroy();
        },
        render() {
            if (this.chart) this.chart.destroy();
            const ctx  = this.$refs.canvas.getContext('2d');
            const tick = this.dark ? '#94A3B8' : '#64748B';
            const grid = this.dark ? 'rgba(148,163,184,0.1)' : 'rgba(15,23,42,0.06)';
            const bar  = this.dark ? 'rgba(16,185,129,0.75)' : 'rgba(5,150,105,0.85)';
            this.chart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels,
                    datasets: [{ label: 'Consultas', data: values, backgroundColor: bar, borderRadius: 6, borderSkipped: false }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, ticks: { color: tick, precision: 0 }, grid: { color: grid } },
                        x: { ticks: { color: tick }, grid: { display: false } }
                    }
                }
            });
        }
    }));
});
