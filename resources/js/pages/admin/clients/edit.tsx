import { Head } from '@inertiajs/react';
import { ClientForm } from '@/pages/admin/clients/_form';
import type { Client, OwnerOption } from '@/types/models';

type Props = {
    client: Client;
    owners: OwnerOption[];
};

export default function ClientEdit({ client, owners }: Props) {
    return (
        <>
            <Head title={`Edit ${client.name}`} />
            <div className="flex flex-1 flex-col gap-4 p-4">
                <h1 className="text-2xl font-semibold">Edit {client.name}</h1>
                <ClientForm mode={{ kind: 'edit', client }} owners={owners} />
            </div>
        </>
    );
}

ClientEdit.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: '/admin/dashboard' },
        { title: 'Clients', href: '/admin/clients' },
        { title: 'Edit', href: '#' },
    ],
};
