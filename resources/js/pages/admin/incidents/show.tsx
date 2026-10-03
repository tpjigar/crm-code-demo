import { Head, Link, router } from '@inertiajs/react';
import { AlertTriangle, Pencil, UserCheck } from 'lucide-react';
import { useState } from 'react';
import { IncidentBadge } from '@/components/incident-badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import type { AssigneeOption, Incident, IncidentStatusValue } from '@/types/models';

type Props = { incident: Incident; assignees: AssigneeOption[] };

export default function IncidentShow({ incident, assignees }: Props) {
    const [assigneeId, setAssigneeId] = useState<string>(incident.assignee?.id?.toString() ?? '');

    function transition(next: IncidentStatusValue) {
        router.post(`/admin/incidents/${incident.id}/transition`, { status: next });
    }

    function changeAssignee(id: string) {
        setAssigneeId(id);
        router.post(`/admin/incidents/${incident.id}/assign`, {
            assigned_to_id: id ? Number(id) : null,
        });
    }

    return (
        <>
            <Head title={incident.reference} />
            <div className="flex flex-1 flex-col gap-4 p-4">
                <div className="flex items-start justify-between gap-4">
                    <div>
                        <div className="font-mono text-xs text-muted-foreground">{incident.reference}</div>
                        <h1 className="text-2xl font-semibold">{incident.title}</h1>
                        <div className="mt-2 flex items-center gap-2">
                            <IncidentBadge label={incident.severity.label} color={incident.severity.color} />
                            <IncidentBadge label={incident.status.label} color={incident.status.color} />
                            {incident.sla_breached && (
                                <span className="inline-flex items-center gap-1 rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-800 dark:bg-red-900/40 dark:text-red-200">
                                    <AlertTriangle className="size-3" />
                                    SLA breached
                                </span>
                            )}
                        </div>
                    </div>
                    <Button asChild variant="secondary">
                        <Link href={`/admin/incidents/${incident.id}/edit`}>
                            <Pencil className="mr-1 size-4" />
                            Edit
                        </Link>
                    </Button>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>Workflow</CardTitle>
                    </CardHeader>
                    <CardContent className="flex flex-wrap items-center gap-2">
                        <span className="text-sm text-muted-foreground">Advance to:</span>
                        {incident.status.allowed_next.length === 0 && (
                            <span className="text-sm italic">Terminal state — no further transitions.</span>
                        )}
                        {incident.status.allowed_next.map((next) => (
                            <Button key={next.value} size="sm" onClick={() => transition(next.value)}>
                                {next.label}
                            </Button>
                        ))}
                    </CardContent>
                </Card>

                <div className="grid gap-4 md:grid-cols-2">
                    <Card>
                        <CardHeader>
                            <CardTitle>Description</CardTitle>
                        </CardHeader>
                        <CardContent className="whitespace-pre-wrap text-sm">
                            {incident.description}
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <UserCheck className="size-4" />
                                Assignment
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-3 text-sm">
                            <div className="text-muted-foreground">
                                <span className="font-medium text-foreground">Client:</span> {incident.client?.name ?? '—'}
                            </div>
                            <div className="text-muted-foreground">
                                <span className="font-medium text-foreground">Reporter:</span> {incident.reporter?.name ?? '—'}
                            </div>
                            <div>
                                <label className="mb-1 block text-xs font-medium">Assignee</label>
                                <select
                                    className="h-9 w-full rounded-md border bg-background px-2 text-sm"
                                    value={assigneeId}
                                    onChange={(e) => changeAssignee(e.target.value)}
                                >
                                    <option value="">— Unassigned —</option>
                                    {assignees.map((a) => (
                                        <option key={a.id} value={a.id}>
                                            {a.name}
                                        </option>
                                    ))}
                                </select>
                            </div>
                            <div className="pt-2 text-xs text-muted-foreground">
                                SLA to acknowledge: {incident.severity.ack_sla_hours}h
                                {incident.acknowledged_at && ' · acknowledged'}
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </>
    );
}

IncidentShow.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: '/admin/dashboard' },
        { title: 'Incidents', href: '/admin/incidents' },
    ],
};
