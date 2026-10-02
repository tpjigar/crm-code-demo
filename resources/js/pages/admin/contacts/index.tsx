import { Head, Link, router } from '@inertiajs/react';
import { Pencil, Plus, Search, Star, Trash2 } from 'lucide-react';
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
import type { ClientOption, Contact, Paginated } from '@/types/models';

type Props = {
    contacts: Paginated<Contact>;
    filters: { search: string | null; client_id: number | null };
    clients: ClientOption[];
};

export default function ContactIndex({ contacts, filters, clients }: Props) {
    const [search, setSearch] = useState<string>(filters.search ?? '');
    const [clientId, setClientId] = useState<string>(filters.client_id?.toString() ?? '');

    function applyFilters(e?: React.FormEvent) {
        e?.preventDefault();
        router.get(
            '/admin/contacts',
            {
                search: search || undefined,
                client_id: clientId || undefined,
            },
            { preserveState: true, replace: true },
        );
    }

    function destroy(contact: Contact) {
        if (!confirm(`Delete ${contact.full_name}? This can be undone from the trash.`)) {
            return;
        }
        router.delete(`/admin/contacts/${contact.id}`);
    }

    return (
        <>
            <Head title="Contacts" />
            <div className="flex flex-1 flex-col gap-4 p-4">
                <div className="flex flex-wrap items-center justify-between gap-3">
                    <form onSubmit={applyFilters} className="flex items-center gap-2">
                        <div className="relative">
                            <Search className="pointer-events-none absolute top-1/2 left-2 size-4 -translate-y-1/2 text-muted-foreground" />
                            <Input
                                type="search"
                                placeholder="Search name, email, title…"
                                value={search}
                                onChange={(e) => setSearch(e.target.value)}
                                className="pl-8"
                            />
                        </div>
                        <select
                            className="h-9 rounded-md border bg-background px-2 text-sm"
                            value={clientId}
                            onChange={(e) => {
                                setClientId(e.target.value);
                                setTimeout(() => applyFilters(), 0);
                            }}
                        >
                            <option value="">All clients</option>
                            {clients.map((c) => (
                                <option key={c.id} value={c.id}>
                                    {c.name}
                                </option>
                            ))}
                        </select>
                        <Button type="submit" variant="secondary">
                            Search
                        </Button>
                    </form>

                    <Button asChild>
                        <Link href="/admin/contacts/create">
                            <Plus className="mr-1 size-4" />
                            New Contact
                        </Link>
                    </Button>
                </div>

                <div className="rounded-xl border">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead className="w-10"></TableHead>
                                <TableHead>Name</TableHead>
                                <TableHead>Client</TableHead>
                                <TableHead>Email</TableHead>
                                <TableHead>Phone</TableHead>
                                <TableHead className="w-32 text-right">Actions</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {contacts.data.length === 0 && (
                                <TableRow>
                                    <TableCell colSpan={6} className="py-8 text-center text-muted-foreground">
                                        No contacts yet.
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
                                        <Link href={`/admin/contacts/${contact.id}`} className="hover:underline">
                                            {contact.full_name}
                                        </Link>
                                        {contact.job_title && (
                                            <div className="text-xs text-muted-foreground">{contact.job_title}</div>
                                        )}
                                    </TableCell>
                                    <TableCell>{contact.client?.name ?? '—'}</TableCell>
                                    <TableCell>
                                        <MaskedField
                                            value={contact.email}
                                            masked={contact.email_masked}
                                            label="email"
                                        />
                                    </TableCell>
                                    <TableCell>
                                        <MaskedField
                                            value={contact.phone}
                                            masked={contact.phone_masked}
                                            label="phone"
                                        />
                                    </TableCell>
                                    <TableCell className="flex items-center justify-end gap-2">
                                        <Button asChild size="icon" variant="ghost">
                                            <Link href={`/admin/contacts/${contact.id}/edit`}>
                                                <Pencil className="size-4" />
                                            </Link>
                                        </Button>
                                        <Button
                                            size="icon"
                                            variant="ghost"
                                            onClick={() => destroy(contact)}
                                        >
                                            <Trash2 className="size-4 text-destructive" />
                                        </Button>
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

ContactIndex.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: '/admin/dashboard' },
        { title: 'Contacts', href: '/admin/contacts' },
    ],
};
