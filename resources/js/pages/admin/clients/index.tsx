import { Head, Link, router } from '@inertiajs/react';
import { Pencil, Plus, Search, Trash2 } from 'lucide-react';
import { useState } from 'react';
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
import type { Client, Paginated } from '@/types/models';

type Props = {
    clients: Paginated<Client>;
    filters: { search: string | null };
};

export default function ClientIndex({ clients, filters }: Props) {
    const [search, setSearch] = useState<string>(filters.search ?? '');

    function submitSearch(e: React.FormEvent) {
        e.preventDefault();
        router.get(
            '/admin/clients',
            { search: search || undefined },
            { preserveState: true, replace: true },
        );
    }

    function destroy(client: Client) {
        if (!confirm(`Delete ${client.name}? This can be undone from the trash.`)) {
            return;
        }
        router.delete(`/admin/clients/${client.id}`);
    }

    return (
        <>
            <Head title="Clients" />
            <div className="flex flex-1 flex-col gap-4 p-4">
                <div className="flex flex-wrap items-center justify-between gap-3">
                    <form onSubmit={submitSearch} className="flex items-center gap-2">
                        <div className="relative">
                            <Search className="pointer-events-none absolute top-1/2 left-2 size-4 -translate-y-1/2 text-muted-foreground" />
                            <Input
                                type="search"
                                placeholder="Search name, industry, email…"
                                value={search}
                                onChange={(e) => setSearch(e.target.value)}
                                className="pl-8"
                            />
                        </div>
                        <Button type="submit" variant="secondary">
                            Search
                        </Button>
                    </form>

                    <Button asChild>
                        <Link href="/admin/clients/create">
                            <Plus className="mr-1 size-4" />
                            New Client
                        </Link>
                    </Button>
                </div>

                <div className="rounded-xl border">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Name</TableHead>
                                <TableHead>Industry</TableHead>
                                <TableHead>Contact</TableHead>
                                <TableHead>Owner</TableHead>
                                <TableHead className="w-32 text-right">Actions</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {clients.data.length === 0 && (
                                <TableRow>
                                    <TableCell colSpan={5} className="py-8 text-center text-muted-foreground">
                                        No clients yet.
                                    </TableCell>
                                </TableRow>
                            )}

                            {clients.data.map((client) => (
                                <TableRow key={client.id}>
                                    <TableCell className="font-medium">
                                        <Link href={`/admin/clients/${client.id}`} className="hover:underline">
                                            {client.name}
                                        </Link>
                                    </TableCell>
                                    <TableCell>{client.industry ?? '—'}</TableCell>
                                    <TableCell className="text-muted-foreground">
                                        {client.contact_email ?? '—'}
                                    </TableCell>
                                    <TableCell>{client.owner?.name ?? '—'}</TableCell>
                                    <TableCell className="flex items-center justify-end gap-2">
                                        <Button asChild size="icon" variant="ghost">
                                            <Link href={`/admin/clients/${client.id}/edit`}>
                                                <Pencil className="size-4" />
                                            </Link>
                                        </Button>
                                        <Button
                                            size="icon"
                                            variant="ghost"
                                            onClick={() => destroy(client)}
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
                    Showing {clients.from ?? 0}–{clients.to ?? 0} of {clients.total}
                </div>
            </div>
        </>
    );
}

ClientIndex.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: '/admin/dashboard' },
        { title: 'Clients', href: '/admin/clients' },
    ],
};
