import { Head, router } from '@inertiajs/react';
import { Search } from 'lucide-react';
import { useState } from 'react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import type { AuditLogEntry, Paginated } from '@/types/models';

type Filters = {
    log_name: string | null;
    causer_id: number | null;
    event: string | null;
    from: string | null;
    to: string | null;
    search: string | null;
};

type Props = {
    logs: Paginated<AuditLogEntry>;
    filters: Filters;
    log_names: string[];
};

const eventColorMap: Record<string, string> = {
    created: 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-200',
    updated: 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-200',
    deleted: 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-200',
};

export default function AuditIndex({ logs, filters, log_names }: Props) {
    const [state, setState] = useState<Filters>(filters);
    const [expanded, setExpanded] = useState<number | null>(null);

    function update<K extends keyof Filters>(key: K, value: Filters[K]) {
        const next = { ...state, [key]: value };
        setState(next);
    }

    function apply(e?: React.FormEvent) {
        e?.preventDefault();
        router.get('/admin/security/audit', {
            log_name: state.log_name || undefined,
            event: state.event || undefined,
            from: state.from || undefined,
            to: state.to || undefined,
            search: state.search || undefined,
        }, { preserveState: true, replace: true });
    }

    return (
        <>
            <Head title="Audit Log" />
            <div className="flex flex-1 flex-col gap-4 p-4">
                <div>
                    <h1 className="text-2xl font-semibold">Audit Log</h1>
                    <p className="text-sm text-muted-foreground">
                        Every write to Client, Contact, and Incident records with the
                        acting user, timestamp, and before/after values.
                    </p>
                </div>

                <form onSubmit={apply} className="grid gap-3 md:grid-cols-6">
                    <div className="relative md:col-span-2">
                        <Search className="pointer-events-none absolute top-1/2 left-2 size-4 -translate-y-1/2 text-muted-foreground" />
                        <Input
                            type="search"
                            placeholder="Search description or subject…"
                            value={state.search ?? ''}
                            onChange={(e) => update('search', e.target.value)}
                            className="pl-8"
                        />
                    </div>
                    <select
                        className="h-9 rounded-md border bg-background px-2 text-sm"
                        value={state.log_name ?? ''}
                        onChange={(e) => update('log_name', e.target.value || null)}
                    >
                        <option value="">All log names</option>
                        {log_names.map((n) => <option key={n} value={n}>{n}</option>)}
                    </select>
                    <select
                        className="h-9 rounded-md border bg-background px-2 text-sm"
                        value={state.event ?? ''}
                        onChange={(e) => update('event', e.target.value || null)}
                    >
                        <option value="">All events</option>
                        <option value="created">created</option>
                        <option value="updated">updated</option>
                        <option value="deleted">deleted</option>
                    </select>
                    <Input
                        type="date"
                        value={state.from ?? ''}
                        onChange={(e) => update('from', e.target.value || null)}
                    />
                    <Input
                        type="date"
                        value={state.to ?? ''}
                        onChange={(e) => update('to', e.target.value || null)}
                    />
                    <div className="md:col-span-6">
                        <Button type="submit" variant="secondary">
                            Apply filters
                        </Button>
                    </div>
                </form>

                <div className="rounded-xl border">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>When</TableHead>
                                <TableHead>Who</TableHead>
                                <TableHead>Log</TableHead>
                                <TableHead>Event</TableHead>
                                <TableHead>Subject</TableHead>
                                <TableHead>Description</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {logs.data.length === 0 && (
                                <TableRow>
                                    <TableCell colSpan={6} className="py-8 text-center text-muted-foreground">
                                        No audit entries match these filters.
                                    </TableCell>
                                </TableRow>
                            )}

                            {logs.data.map((log) => (
                                <>
                                    <TableRow
                                        key={log.id}
                                        className="cursor-pointer"
                                        onClick={() => setExpanded(expanded === log.id ? null : log.id)}
                                    >
                                        <TableCell className="whitespace-nowrap text-xs text-muted-foreground">
                                            {log.created_at?.replace('T', ' ').slice(0, 19) ?? '—'}
                                        </TableCell>
                                        <TableCell>
                                            {log.causer ? (
                                                <>
                                                    <div className="text-sm">{log.causer.name ?? '—'}</div>
                                                    <div className="text-xs text-muted-foreground">{log.causer.email ?? ''}</div>
                                                </>
                                            ) : (
                                                <span className="text-muted-foreground">system</span>
                                            )}
                                        </TableCell>
                                        <TableCell className="text-sm font-medium">{log.log_name ?? '—'}</TableCell>
                                        <TableCell>
                                            <span className={`rounded-full px-2 py-0.5 text-[10px] font-medium ${eventColorMap[log.event ?? ''] ?? 'bg-muted text-muted-foreground'}`}>
                                                {log.event ?? '—'}
                                            </span>
                                        </TableCell>
                                        <TableCell className="font-mono text-xs">
                                            {log.subject_type ?? '—'}#{log.subject_id ?? '—'}
                                        </TableCell>
                                        <TableCell className="text-sm">{log.description}</TableCell>
                                    </TableRow>
                                    {expanded === log.id && (
                                        <TableRow key={`${log.id}-detail`} className="bg-muted/30">
                                            <TableCell colSpan={6}>
                                                <pre className="overflow-x-auto rounded bg-background p-3 text-xs">
                                                    {JSON.stringify(log.properties, null, 2)}
                                                </pre>
                                            </TableCell>
                                        </TableRow>
                                    )}
                                </>
                            ))}
                        </TableBody>
                    </Table>
                </div>

                <div className="text-sm text-muted-foreground">
                    Showing {logs.from ?? 0}–{logs.to ?? 0} of {logs.total}
                </div>
            </div>
        </>
    );
}

AuditIndex.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: '/admin/dashboard' },
        { title: 'Security', href: '#' },
        { title: 'Audit Log', href: '/admin/security/audit' },
    ],
};
