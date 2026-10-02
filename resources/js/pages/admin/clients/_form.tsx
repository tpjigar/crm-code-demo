import { router, useForm } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type { Client, OwnerOption } from '@/types/models';

type Mode = { kind: 'create' } | { kind: 'edit'; client: Client };

type Props = {
    mode: Mode;
    owners: OwnerOption[];
};

type FormState = {
    name: string;
    industry: string;
    website: string;
    contact_email: string;
    contact_phone: string;
    country: string;
    notes: string;
    owner_id: string;
};

function initial(mode: Mode): FormState {
    if (mode.kind === 'edit') {
        const c = mode.client;
        return {
            name: c.name,
            industry: c.industry ?? '',
            website: c.website ?? '',
            contact_email: c.contact_email ?? '',
            contact_phone: c.contact_phone ?? '',
            country: c.country ?? '',
            notes: c.notes ?? '',
            owner_id: c.owner?.id?.toString() ?? '',
        };
    }
    return {
        name: '',
        industry: '',
        website: '',
        contact_email: '',
        contact_phone: '',
        country: '',
        notes: '',
        owner_id: '',
    };
}

export function ClientForm({ mode, owners }: Props) {
    const form = useForm<FormState>(initial(mode));

    function submit(e: React.FormEvent) {
        e.preventDefault();
        if (mode.kind === 'create') {
            form.post('/admin/clients');
        } else {
            form.put(`/admin/clients/${mode.client.id}`);
        }
    }

    return (
        <form onSubmit={submit} className="grid gap-4 md:grid-cols-2">
            <Field label="Name" error={form.errors.name} required>
                <Input
                    value={form.data.name}
                    onChange={(e) => form.setData('name', e.target.value)}
                    autoFocus
                />
            </Field>

            <Field label="Industry" error={form.errors.industry}>
                <Input
                    value={form.data.industry}
                    onChange={(e) => form.setData('industry', e.target.value)}
                />
            </Field>

            <Field label="Website" error={form.errors.website}>
                <Input
                    type="url"
                    value={form.data.website}
                    onChange={(e) => form.setData('website', e.target.value)}
                    placeholder="https://…"
                />
            </Field>

            <Field label="Contact Email" error={form.errors.contact_email}>
                <Input
                    type="email"
                    value={form.data.contact_email}
                    onChange={(e) => form.setData('contact_email', e.target.value)}
                />
            </Field>

            <Field label="Contact Phone" error={form.errors.contact_phone}>
                <Input
                    value={form.data.contact_phone}
                    onChange={(e) => form.setData('contact_phone', e.target.value)}
                />
            </Field>

            <Field label="Country (ISO-2)" error={form.errors.country}>
                <Input
                    maxLength={2}
                    value={form.data.country}
                    onChange={(e) => form.setData('country', e.target.value.toUpperCase())}
                    placeholder="US"
                />
            </Field>

            <Field label="Owner" error={form.errors.owner_id}>
                <select
                    className="h-9 w-full rounded-md border bg-background px-2 text-sm"
                    value={form.data.owner_id}
                    onChange={(e) => form.setData('owner_id', e.target.value)}
                >
                    <option value="">— Unassigned —</option>
                    {owners.map((o) => (
                        <option key={o.id} value={o.id}>
                            {o.name}
                        </option>
                    ))}
                </select>
            </Field>

            <div className="md:col-span-2">
                <Field label="Notes" error={form.errors.notes}>
                    <textarea
                        rows={4}
                        className="w-full rounded-md border bg-background px-3 py-2 text-sm"
                        value={form.data.notes}
                        onChange={(e) => form.setData('notes', e.target.value)}
                    />
                </Field>
            </div>

            <div className="flex items-center gap-2 md:col-span-2">
                <Button type="submit" disabled={form.processing}>
                    {mode.kind === 'create' ? 'Create Client' : 'Save Changes'}
                </Button>
                <Button type="button" variant="ghost" onClick={() => router.visit('/admin/clients')}>
                    Cancel
                </Button>
            </div>
        </form>
    );
}

function Field({
    label,
    children,
    error,
    required,
}: {
    label: string;
    children: React.ReactNode;
    error?: string;
    required?: boolean;
}) {
    return (
        <div className="flex flex-col gap-1.5">
            <Label>
                {label} {required && <span className="text-destructive">*</span>}
            </Label>
            {children}
            {error && <p className="text-sm text-destructive">{error}</p>}
        </div>
    );
}
