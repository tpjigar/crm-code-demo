type ColorKey = 'emerald' | 'blue' | 'amber' | 'red' | string;

const colorMap: Record<string, string> = {
    emerald: 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-200',
    blue: 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-200',
    amber: 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-200',
    red: 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-200',
};

export function IncidentBadge({ label, color }: { label: string; color: ColorKey }) {
    return (
        <span className={`inline-block rounded-full px-2.5 py-0.5 text-xs font-medium ${colorMap[color] ?? ''}`}>
            {label}
        </span>
    );
}
