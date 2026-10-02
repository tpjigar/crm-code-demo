import { Head, Link } from '@inertiajs/react';
import { ArrowLeft, Briefcase, Mail, Phone, Star } from 'lucide-react';
import { MaskedField } from '@/components/masked-field';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import type { Contact } from '@/types/models';

type Props = { contact: Contact };

export default function PortalContactShow({ contact }: Props) {
    return (
        <>
            <Head title={contact.full_name} />
            <div className="flex flex-1 flex-col gap-4 p-4">
                <Button asChild variant="ghost" className="w-fit">
                    <Link href="/portal/contacts">
                        <ArrowLeft className="mr-1 size-4" />
                        Back to contacts
                    </Link>
                </Button>

                <div>
                    <h1 className="flex items-center gap-2 text-2xl font-semibold">
                        {contact.full_name}
                        {contact.is_primary && (
                            <Star className="size-5 fill-amber-400 text-amber-400" />
                        )}
                    </h1>
                </div>

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
            </div>
        </>
    );
}

PortalContactShow.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: '/portal/dashboard' },
        { title: 'Contacts', href: '/portal/contacts' },
    ],
};
