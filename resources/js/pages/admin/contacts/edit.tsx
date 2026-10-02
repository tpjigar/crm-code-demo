import { Head } from '@inertiajs/react';
import { ContactForm } from '@/pages/admin/contacts/_form';
import type { ClientOption, Contact } from '@/types/models';

type Props = {
    contact: Contact;
    clients: ClientOption[];
};

export default function ContactEdit({ contact, clients }: Props) {
    return (
        <>
            <Head title={`Edit ${contact.full_name}`} />
            <div className="flex flex-1 flex-col gap-4 p-4">
                <h1 className="text-2xl font-semibold">Edit {contact.full_name}</h1>
                <ContactForm mode={{ kind: 'edit', contact }} clients={clients} />
            </div>
        </>
    );
}

ContactEdit.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: '/admin/dashboard' },
        { title: 'Contacts', href: '/admin/contacts' },
        { title: 'Edit', href: '#' },
    ],
};
