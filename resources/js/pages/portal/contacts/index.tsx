import { Head, Link, router } from '@inertiajs/react';
import { Search, Star } from 'lucide-react';
import { useState } from 'react';
import { MaskedField } from '@/components/masked-field';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import type { Contact, Paginated } from '@/types/models';

type Props = {
    contacts: Paginated<Contact>;
    filters: { search: string | null };
};

export default function PortalContactIndex({ contacts, filters }: Props) {
    const [search, setSearch] = useState<string>(filters.search ?? '');

    function submit(e: React.FormEvent) {
        e.preventDefault();
        router.get(
            '/portal/contacts',
            { search: search || undefined },
            { preserveState: true, replace: true },
        );
    }

    return (
        <>
            <Head title="My Contacts" />
            <div className="flex flex-1 flex-col gap-4 p-4">
                <div className="flex items-center justify-between">
                    <h1 className="text-2xl font-semibold">My Contacts</h1>
                    <form onSubmit={submit} className="flex items-center gap-2">
                        <div className="relative">
                            <Search className="pointer-events-none absolute top-1/2 left-2 size-4 -translate-y-1/2 text-muted-foreground" />
                            <Input
                                type="search"
                                placeholder="Search name, email…"
                                value={search}
                                onChange={(e) => setSearch(e.target.value)}
                                className="pl-8"
                            />
                        </div>
                        <Button type="submit" variant="secondary">
                            Search
                        </Button>
                    </form>
                </div>

                <p className="text-sm text-muted-foreground">
                    Email and phone are masked for your role. Contact your account manager
                    if you need the raw values.
                </p>

                <div className="rounded-xl border">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead className="w-10"></TableHead>
                                <TableHead>Name</TableHead>
                                <TableHead>Title</TableHead>
                                <TableHead>Email</TableHead>
                                <TableHead>Phone</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {contacts.data.length === 0 && (
                                <TableRow>
                                    <TableCell colSpan={5} className="py-8 text-center text-muted-foreground">
                                        No contacts on file yet.
                                    </TableCell>
                                </TableRow>
                            )}

                            {contacts.data.map((contact) => (
                                <TableRow key={contact.id}>
                                    <TableCell>
                                        {contact.is_primary && (
                                            <Star className="size-4 fill-amber-400 text-amber-400" />
                                        )}
                                    </TableCell>
                                    <TableCell className="font-medium">
                                        <Link href={`/portal/contacts/${contact.id}`} className="hover:underline">
                                            {contact.full_name}
                                        </Link>
                                    </TableCell>
                                    <TableCell>{contact.job_title ?? '—'}</TableCell>
                                    <TableCell>
                                        <MaskedField value={contact.email} masked={contact.email_masked} label="email" />
                                    </TableCell>
                                    <TableCell>
                                        <MaskedField value={contact.phone} masked={contact.phone_masked} label="phone" />
                                    </TableCell>
                                </TableRow>
                            ))}
                        </TableBody>
                    </Table>
                </div>

                <div className="text-sm text-muted-foreground">
                    Showing {contacts.from ?? 0}–{contacts.to ?? 0} of {contacts.total}
                </div>
            </div>
        </>
    );
}

PortalContactIndex.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: '/portal/dashboard' },
        { title: 'Contacts', href: '/portal/contacts' },
    ],
};
