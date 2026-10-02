import { Head, Link } from '@inertiajs/react';
import { Briefcase, Mail, Pencil, Phone, Star } from 'lucide-react';
import { MaskedField } from '@/components/masked-field';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import type { Contact } from '@/types/models';

type Props = { contact: Contact };

export default function ContactShow({ contact }: Props) {
    return (
        <>
            <Head title={contact.full_name} />
            <div className="flex flex-1 flex-col gap-4 p-4">
                <div className="flex items-start justify-between">
                    <div>
                        <h1 className="flex items-center gap-2 text-2xl font-semibold">
                            {contact.full_name}
                            {contact.is_primary && (
                                <Star className="size-5 fill-amber-400 text-amber-400" />
                            )}
                        </h1>
                        {contact.client?.name && (
                            <Link
                                href={`/admin/clients/${contact.client.id}`}
                                className="text-muted-foreground hover:underline"
                            >
                                {contact.client.name}
                            </Link>
                        )}
                    </div>
                    <Button asChild variant="secondary">
                        <Link href={`/admin/contacts/${contact.id}/edit`}>
                            <Pencil className="mr-1 size-4" />
                            Edit
                        </Link>
                    </Button>
                </div>

                <div className="grid gap-4 md:grid-cols-2">
                    <Card>
                        <CardHeader>
                            <CardTitle>Contact Details</CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-3 text-sm">
                            {contact.job_title && (
                                <div className="flex items-center gap-2">
                                    <Briefcase className="size-4 text-muted-foreground" />
                                    <span>{contact.job_title}</span>
                                </div>
                            )}
                            <div className="flex items-center gap-2">
                                <Mail className="size-4 text-muted-foreground" />
                                <MaskedField value={contact.email} masked={contact.email_masked} label="email" />
                            </div>
                            <div className="flex items-center gap-2">
                                <Phone className="size-4 text-muted-foreground" />
                                <MaskedField value={contact.phone} masked={contact.phone_masked} label="phone" />
                            </div>
                        </CardContent>
                    </Card>

                    {contact.notes && (
                        <Card>
                            <CardHeader>
                                <CardTitle>Notes</CardTitle>
                            </CardHeader>
                            <CardContent className="whitespace-pre-wrap text-sm">
                                {contact.notes}
                            </CardContent>
                        </Card>
                    )}
                </div>
            </div>
        </>
    );
}

ContactShow.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: '/admin/dashboard' },
        { title: 'Contacts', href: '/admin/contacts' },
    ],
};
