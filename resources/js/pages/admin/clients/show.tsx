import { Head, Link } from '@inertiajs/react';
import { Globe, Mail, Phone, Pencil } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import type { Client } from '@/types/models';

type Props = { client: Client };

export default function ClientShow({ client }: Props) {
    return (
        <>
            <Head title={client.name} />
            <div className="flex flex-1 flex-col gap-4 p-4">
                <div className="flex items-start justify-between">
                    <div>
                        <h1 className="text-2xl font-semibold">{client.name}</h1>
                        {client.industry && (
                            <p className="text-muted-foreground">{client.industry}</p>
                        )}
                    </div>
                    <Button asChild variant="secondary">
                        <Link href={`/admin/clients/${client.id}/edit`}>
                            <Pencil className="mr-1 size-4" />
                            Edit
                        </Link>
                    </Button>
                </div>

                <div className="grid gap-4 md:grid-cols-2">
                    <Card>
                        <CardHeader>
                            <CardTitle>Contact</CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-2 text-sm">
                            <Row icon={Mail} value={client.contact_email} />
                            <Row icon={Phone} value={client.contact_phone} />
                            <Row icon={Globe} value={client.website} link />
                            <div className="pt-2 text-muted-foreground">
                                Country: {client.country ?? '—'}
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>Ownership</CardTitle>
                        </CardHeader>
                        <CardContent className="text-sm">
                            {client.owner?.name ? (
                                <>
                                    <div className="font-medium">{client.owner.name}</div>
                                    <div className="text-muted-foreground">{client.owner.email}</div>
                                </>
                            ) : (
                                <div className="text-muted-foreground">Unassigned</div>
                            )}
                        </CardContent>
                    </Card>
                </div>

                {client.notes && (
                    <Card>
                        <CardHeader>
                            <CardTitle>Notes</CardTitle>
                        </CardHeader>
                        <CardContent className="whitespace-pre-wrap text-sm">
                            {client.notes}
                        </CardContent>
                    </Card>
                )}
            </div>
        </>
    );
}

function Row({ icon: Icon, value, link }: { icon: React.ComponentType<{ className?: string }>; value: string | null; link?: boolean }) {
    if (!value) return <div className="flex items-center gap-2 text-muted-foreground"><Icon className="size-4" />—</div>;

    return (
        <div className="flex items-center gap-2">
            <Icon className="size-4 text-muted-foreground" />
            {link ? (
                <a href={value} target="_blank" rel="noreferrer noopener" className="hover:underline">
                    {value}
                </a>
            ) : (
                <span>{value}</span>
            )}
        </div>
    );
}

ClientShow.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: '/admin/dashboard' },
        { title: 'Clients', href: '/admin/clients' },
    ],
};
