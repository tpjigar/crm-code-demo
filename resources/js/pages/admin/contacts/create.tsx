import { Head } from '@inertiajs/react';
import { ContactForm } from '@/pages/admin/contacts/_form';
import type { ClientOption } from '@/types/models';

type Props = {
    clients: ClientOption[];
    preselected_client_id: number | null;
};

export default function ContactCreate({ clients, preselected_client_id }: Props) {
    return (
        <>
            <Head title="New Contact" />
            <div className="flex flex-1 flex-col gap-4 p-4">
                <h1 className="text-2xl font-semibold">New Contact</h1>
                <ContactForm
                    mode={{ kind: 'create', preselectedClientId: preselected_client_id }}
                    clients={clients}
                />
            </div>
        </>
    );
}

ContactCreate.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: '/admin/dashboard' },
        { title: 'Contacts', href: '/admin/contacts' },
        { title: 'New', href: '/admin/contacts/create' },
    ],
};
