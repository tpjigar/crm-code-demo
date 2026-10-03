import { Head } from '@inertiajs/react';
import { IncidentForm } from '@/pages/admin/incidents/_form';
import type { AssigneeOption, ClientOption, Incident, IncidentSeverityInfo } from '@/types/models';

type Props = {
    incident: Incident;
    clients: ClientOption[];
    assignees: AssigneeOption[];
    severities: IncidentSeverityInfo[];
};

export default function IncidentEdit({ incident, clients, assignees, severities }: Props) {
    return (
        <>
            <Head title={`Edit ${incident.reference}`} />
            <div className="flex flex-1 flex-col gap-4 p-4">
                <h1 className="text-2xl font-semibold">Edit {incident.reference}</h1>
                <IncidentForm
                    mode={{ kind: 'edit', incident }}
                    clients={clients}
                    assignees={assignees}
                    severities={severities}
                />
            </div>
        </>
    );
}

IncidentEdit.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: '/admin/dashboard' },
        { title: 'Incidents', href: '/admin/incidents' },
        { title: 'Edit', href: '#' },
    ],
};
