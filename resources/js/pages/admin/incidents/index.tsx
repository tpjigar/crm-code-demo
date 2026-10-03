import { Head, Link, router } from '@inertiajs/react';
import { AlertTriangle, Plus, Search } from 'lucide-react';
import { useState } from 'react';
import { IncidentBadge } from '@/components/incident-badge';
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
import type {
    ClientOption,
    Incident,
    IncidentStatusValue,
    Paginated,
} from '@/types/models';

type StatusOption = { value: IncidentStatusValue; label: string; color: string };

type Props = {
    incidents: Paginated<Incident>;
    filters: { search: string | null; status: IncidentStatusValue | null; client_id: number | null };
    clients: ClientOption[];
    statuses: StatusOption[];
};

export default function IncidentIndex({ incidents, filters, clients, statuses }: Props) {
    const [search, setSearch] = useState<string>(filters.search ?? '');
    const [status, setStatus] = useState<string>(filters.status ?? '');
    const [clientId, setClientId] = useState<string>(filters.client_id?.toString() ?? '');

    function apply(e?: React.FormEvent) {
        e?.preventDefault();
        router.get(
            '/admin/incidents',
            {
                search: search || undefined,
                status: status || undefined,
                client_id: clientId || undefined,
            },
            { preserveState: true, replace: true },
        );
    }

    return (
        <>
            <Head title="Incidents" />
            <div className="flex flex-1 flex-col gap-4 p-4">
                <div className="flex flex-wrap items-center justify-between gap-3">
                    <form onSubmit={apply} className="flex items-center gap-2">
                        <div className="relative">
                            <Search className="pointer-events-none absolute top-1/2 left-2 size-4 -translate-y-1/2 text-muted-foreground" />
                            <Input
                                type="search"
                                placeholder="Search ref, title, body…"
                                value={search}
                                onChange={(e) => setSearch(e.target.value)}
                                className="pl-8"
                            />
                        </div>
                        <select
                            className="h-9 rounded-md border bg-background px-2 text-sm"
                            value={status}
                            onChange={(e) => {
                                setStatus(e.target.value);
                                setTimeout(() => apply(), 0);
                            }}
                        >
                            <option value="">All statuses</option>
                            {statuses.map((s) => (
                                <option key={s.value} value={s.value}>
                                    {s.label}
                                </option>
                            ))}
                        </select>
                        <select
                            className="h-9 rounded-md border bg-background px-2 text-sm"
                            value={clientId}
                            onChange={(e) => {
                                setClientId(e.target.value);
                                setTimeout(() => apply(), 0);
                            }}
                        >
                            <option value="">All clients</option>
                            {clients.map((c) => (
                                <option key={c.id} value={c.id}>
                                    {c.name}
                                </option>
                            ))}
                        </select>
                        <Button type="submit" variant="secondary">
                            Search
                        </Button>
                    </form>

                    <Button asChild>
                        <Link href="/admin/incidents/create">
                            <Plus className="mr-1 size-4" />
                            New Incident
                        </Link>
                    </Button>
                </div>

                <div className="rounded-xl border">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Reference</TableHead>
                                <TableHead>Title</TableHead>
                                <TableHead>Client</TableHead>
                                <TableHead>Severity</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead>Assignee</TableHead>
                                <TableHead>SLA</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {incidents.data.length === 0 && (
                                <TableRow>
                                    <TableCell colSpan={7} className="py-8 text-center text-muted-foreground">
                                        No incidents.
                                    </TableCell>
                                </TableRow>
                            )}

                            {incidents.data.map((inc) => (
                                <TableRow key={inc.id}>
                                    <TableCell className="font-mono text-xs">
                                        <Link href={`/admin/incidents/${inc.id}`} className="hover:underline">
                                            {inc.reference}
                                        </Link>
                                    </TableCell>
                                    <TableCell className="font-medium">{inc.title}</TableCell>
                                    <TableCell>{inc.client?.name ?? '—'}</TableCell>
                                    <TableCell>
                                        <IncidentBadge label={inc.severity.label} color={inc.severity.color} />
                                    </TableCell>
                                    <TableCell>
                                        <IncidentBadge label={inc.status.label} color={inc.status.color} />
                                    </TableCell>
                                    <TableCell>{inc.assignee?.name ?? '—'}</TableCell>
                                    <TableCell>
                                        {inc.sla_breached ? (
                                            <span className="inline-flex items-center gap-1 text-xs font-medium text-destructive">
                                                <AlertTriangle className="size-3.5" /> Breached
                                            </span>
                                        ) : (
                                            <span className="text-xs text-muted-foreground">On track</span>
                                        )}
                                    </TableCell>
                                </TableRow>
                            ))}
                        </TableBody>
                    </Table>
                </div>

                <div className="text-sm text-muted-foreground">
                    Showing {incidents.from ?? 0}–{incidents.to ?? 0} of {incidents.total}
                </div>
            </div>
        </>
    );
}

IncidentIndex.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: '/admin/dashboard' },
        { title: 'Incidents', href: '/admin/incidents' },
    ],
};
