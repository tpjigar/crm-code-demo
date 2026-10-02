import { Head } from '@inertiajs/react';
import { ClientForm } from '@/pages/admin/clients/_form';
import type { OwnerOption } from '@/types/models';

type Props = { owners: OwnerOption[] };

export default function ClientCreate({ owners }: Props) {
    return (
        <>
            <Head title="New Client" />
            <div className="flex flex-1 flex-col gap-4 p-4">
                <h1 className="text-2xl font-semibold">New Client</h1>
                <ClientForm mode={{ kind: 'create' }} owners={owners} />
            </div>
        </>
    );
}

ClientCreate.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: '/admin/dashboard' },
        { title: 'Clients', href: '/admin/clients' },
        { title: 'New', href: '/admin/clients/create' },
    ],
};
