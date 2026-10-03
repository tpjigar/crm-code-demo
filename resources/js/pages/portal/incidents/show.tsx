import { Head, Link } from '@inertiajs/react';
import { ArrowLeft } from 'lucide-react';
import { IncidentBadge } from '@/components/incident-badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import type { Incident } from '@/types/models';

type Props = { incident: Incident };

export default function PortalIncidentShow({ incident }: Props) {
    return (
        <>
            <Head title={incident.reference} />
            <div className="flex flex-1 flex-col gap-4 p-4">
                <Button asChild variant="ghost" className="w-fit">
                    <Link href="/portal/incidents">
                        <ArrowLeft className="mr-1 size-4" />
                        Back to incidents
                    </Link>
                </Button>

                <div>
                    <div className="font-mono text-xs text-muted-foreground">{incident.reference}</div>
                    <h1 className="text-2xl font-semibold">{incident.title}</h1>
                    <div className="mt-2 flex items-center gap-2">
                        <IncidentBadge label={incident.severity.label} color={incident.severity.color} />
                        <IncidentBadge label={incident.status.label} color={incident.status.color} />
                    </div>
                </div>

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
                        <CardTitle>Status</CardTitle>
                    </CardHeader>
                    <CardContent className="space-y-1 text-sm">
                        <div>
                            <span className="font-medium">Assignee:</span> {incident.assignee?.name ?? 'Not yet assigned'}
                        </div>
                        <div>
                            <span className="font-medium">Acknowledged:</span> {incident.acknowledged_at ?? '—'}
                        </div>
                        <div>
                            <span className="font-medium">Resolved:</span> {incident.resolved_at ?? '—'}
                        </div>
                    </CardContent>
                </Card>
            </div>
        </>
    );
}

PortalIncidentShow.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: '/portal/dashboard' },
        { title: 'Incidents', href: '/portal/incidents' },
    ],
};
