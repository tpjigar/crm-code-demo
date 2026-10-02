import { Eye, EyeOff } from 'lucide-react';
import { useState } from 'react';

type Props = {
    value: string | null;
    masked: boolean;
    label?: string;
    className?: string;
};

/**
 * Displays a potentially-sensitive value (email, phone, …). When the
 * server marked the value as `masked`, it has already been obscured
 * — the raw value never leaves the backend. The click-to-reveal
 * toggle here can only hide/show whatever the server chose to send,
 * so a client-role user staring at `j*****@a*****.com` cannot unmask
 * it client-side.
 *
 * For an unmasked value (super admin), the toggle just collapses the
 * display back to dots on request.
 */
export function MaskedField({ value, masked, label, className }: Props) {
    const [hidden, setHidden] = useState(false);

    if (value === null || value === '') {
        return <span className="text-muted-foreground">—</span>;
    }

    const display = hidden ? '•'.repeat(Math.min(value.length, 10)) : value;

    return (
        <span className={`inline-flex items-center gap-1.5 ${className ?? ''}`}>
            <span className={masked ? 'font-mono text-sm' : ''}>{display}</span>
            {!masked && (
                <button
                    type="button"
                    onClick={() => setHidden((v) => !v)}
                    className="text-muted-foreground transition-colors hover:text-foreground"
                    aria-label={hidden ? `Show ${label ?? 'value'}` : `Hide ${label ?? 'value'}`}
                >
                    {hidden ? <Eye className="size-3.5" /> : <EyeOff className="size-3.5" />}
                </button>
            )}
            {masked && (
                <span
                    className="rounded bg-amber-100 px-1.5 py-0.5 text-[10px] font-medium text-amber-800 dark:bg-amber-900/40 dark:text-amber-200"
                    title="This value is masked for your role"
                >
                    masked
                </span>
            )}
        </span>
    );
}
