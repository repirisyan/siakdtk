import { onBeforeUnmount, onMounted, ref } from 'vue';

export type ChartTheme = {
    primary: string;
    chart1: string;
    chart2: string;
    card: string;
    cardForeground: string;
    border: string;
    mutedForeground: string;
    isDark: boolean;
};

const cssToken = (token: string) =>
    getComputedStyle(document.documentElement).getPropertyValue(token).trim();

export function useChartTheme() {
    const chartTheme = ref<ChartTheme | null>(null);
    let observer: MutationObserver | undefined;

    const refresh = () => {
        chartTheme.value = {
            primary: cssToken('--primary'),
            chart1: cssToken('--chart-1'),
            chart2: cssToken('--chart-2'),
            card: cssToken('--card'),
            cardForeground: cssToken('--card-foreground'),
            border: cssToken('--border'),
            mutedForeground: cssToken('--muted-foreground'),
            isDark: document.documentElement.classList.contains('dark'),
        };
    };

    onMounted(() => {
        refresh();
        observer = new MutationObserver(refresh);
        observer.observe(document.documentElement, {
            attributes: true,
            attributeFilter: ['class'],
        });
    });

    onBeforeUnmount(() => observer?.disconnect());

    return { chartTheme };
}
