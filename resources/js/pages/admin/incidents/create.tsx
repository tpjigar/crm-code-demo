import { Head } from '@inertiajs/react';
import { IncidentForm } from '@/pages/admin/incidents/_form';
import type { AssigneeOption, ClientOption, IncidentSeverityInfo } from '@/types/models';

type Props = {
    clients: ClientOption[];
    assignees: AssigneeOption[];
    severities: IncidentSeverityInfo[];
};

export default function IncidentCreate({ clients, assignees, severities }: Props) {
    return (
        <>
            <Head title="New Incident" />
            <div className="flex flex-1 flex-col gap-4 p-4">
                <h1 className="text-2xl font-semibold">New Incident</h1>
                <IncidentForm
                    mode={{ kind: 'create' }}
                    clients={clients}
                    assignees={assignees}
                    severities={severities}
                />
            </div>
        </>
    );
}

IncidentCreate.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: '/admin/dashboard' },
        { title: 'Incidents', href: '/admin/incidents' },
        { title: 'New', href: '/admin/incidents/create' },
    ],
};
