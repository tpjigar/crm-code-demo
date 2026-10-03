import { Head, Link, router } from '@inertiajs/react';
import { Plus, Search } from 'lucide-react';
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
import type { Incident, IncidentStatusValue, Paginated } from '@/types/models';

type Props = {
    incidents: Paginated<Incident>;
    filters: { search: string | null; status: IncidentStatusValue | null };
};

export default function PortalIncidentIndex({ incidents, filters }: Props) {
    const [search, setSearch] = useState<string>(filters.search ?? '');

    function submit(e: React.FormEvent) {
        e.preventDefault();
        router.get(
            '/portal/incidents',
            { search: search || undefined },
            { preserveState: true, replace: true },
        );
    }

    return (
        <>
            <Head title="My Incidents" />
            <div className="flex flex-1 flex-col gap-4 p-4">
                <div className="flex flex-wrap items-center justify-between gap-3">
                    <h1 className="text-2xl font-semibold">My Incidents</h1>
                    <div className="flex items-center gap-2">
                        <form onSubmit={submit} className="flex items-center gap-2">
                            <div className="relative">
                                <Search className="pointer-events-none absolute top-1/2 left-2 size-4 -translate-y-1/2 text-muted-foreground" />
                                <Input
                                    type="search"
                                    placeholder="Search…"
                                    value={search}
                                    onChange={(e) => setSearch(e.target.value)}
                                    className="pl-8"
                                />
                            </div>
                            <Button type="submit" variant="secondary">
                                Search
                            </Button>
                        </form>
                        <Button asChild>
                            <Link href="/portal/incidents/create">
                                <Plus className="mr-1 size-4" />
                                Report Incident
                            </Link>
                        </Button>
                    </div>
                </div>

                <div className="rounded-xl border">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Reference</TableHead>
                                <TableHead>Title</TableHead>
                                <TableHead>Severity</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead>Opened</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {incidents.data.length === 0 && (
                                <TableRow>
                                    <TableCell colSpan={5} className="py-8 text-center text-muted-foreground">
                                        No incidents yet. Report one above.
                                    </TableCell>
                                </TableRow>
                            )}

                            {incidents.data.map((inc) => (
                                <TableRow key={inc.id}>
                                    <TableCell className="font-mono text-xs">
                                        <Link href={`/portal/incidents/${inc.id}`} className="hover:underline">
                                            {inc.reference}
                                        </Link>
                                    </TableCell>
                                    <TableCell className="font-medium">{inc.title}</TableCell>
                                    <TableCell>
                                        <IncidentBadge label={inc.severity.label} color={inc.severity.color} />
                                    </TableCell>
                                    <TableCell>
                                        <IncidentBadge label={inc.status.label} color={inc.status.color} />
                                    </TableCell>
                                    <TableCell className="text-xs text-muted-foreground">
                                        {inc.created_at?.slice(0, 10) ?? '—'}
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

PortalIncidentIndex.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: '/portal/dashboard' },
        { title: 'Incidents', href: '/portal/incidents' },
    ],
};
