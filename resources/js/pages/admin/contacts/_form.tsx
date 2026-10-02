import { router, useForm } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type { ClientOption, Contact } from '@/types/models';

type Mode =
    | { kind: 'create'; preselectedClientId: number | null }
    | { kind: 'edit'; contact: Contact };

type Props = {
    mode: Mode;
    clients: ClientOption[];
};

type FormState = {
    client_id: string;
    first_name: string;
    last_name: string;
    job_title: string;
    email: string;
    phone: string;
    is_primary: boolean;
    notes: string;
};

function initial(mode: Mode): FormState {
    if (mode.kind === 'edit') {
        const c = mode.contact;
        return {
            client_id: c.client_id.toString(),
            first_name: c.first_name,
            last_name: c.last_name,
            job_title: c.job_title ?? '',
            email: c.email_masked ? '' : (c.email ?? ''),
            phone: c.phone_masked ? '' : (c.phone ?? ''),
            is_primary: c.is_primary,
            notes: c.notes ?? '',
        };
    }
    return {
        client_id: mode.preselectedClientId?.toString() ?? '',
        first_name: '',
        last_name: '',
        job_title: '',
        email: '',
        phone: '',
        is_primary: false,
        notes: '',
    };
}

export function ContactForm({ mode, clients }: Props) {
    const form = useForm<FormState>(initial(mode));

    function submit(e: React.FormEvent) {
        e.preventDefault();
        if (mode.kind === 'create') {
            form.post('/admin/contacts');
        } else {
            form.put(`/admin/contacts/${mode.contact.id}`);
        }
    }

    return (
        <form onSubmit={submit} className="grid gap-4 md:grid-cols-2">
            <Field label="Client" error={form.errors.client_id} required>
                <select
                    className="h-9 w-full rounded-md border bg-background px-2 text-sm"
                    value={form.data.client_id}
                    onChange={(e) => form.setData('client_id', e.target.value)}
                >
                    <option value="">— Select a client —</option>
                    {clients.map((c) => (
                        <option key={c.id} value={c.id}>
                            {c.name}
                        </option>
                    ))}
                </select>
            </Field>

            <Field label="Job Title" error={form.errors.job_title}>
                <Input
                    value={form.data.job_title}
                    onChange={(e) => form.setData('job_title', e.target.value)}
                />
            </Field>

            <Field label="First Name" error={form.errors.first_name} required>
                <Input
                    value={form.data.first_name}
                    onChange={(e) => form.setData('first_name', e.target.value)}
                    autoFocus
                />
            </Field>

            <Field label="Last Name" error={form.errors.last_name} required>
                <Input
                    value={form.data.last_name}
                    onChange={(e) => form.setData('last_name', e.target.value)}
                />
            </Field>

            <Field label="Email" error={form.errors.email}>
                <Input
                    type="email"
                    value={form.data.email}
                    onChange={(e) => form.setData('email', e.target.value)}
                />
            </Field>

            <Field label="Phone" error={form.errors.phone}>
                <Input
                    value={form.data.phone}
                    onChange={(e) => form.setData('phone', e.target.value)}
                    placeholder="+1 555 0100"
                />
            </Field>

            <div className="md:col-span-2">
                <label className="inline-flex items-center gap-2 text-sm">
                    <input
                        type="checkbox"
                        checked={form.data.is_primary}
                        onChange={(e) => form.setData('is_primary', e.target.checked)}
                        className="size-4 rounded border-input"
                    />
                    Mark as primary contact (demotes any other primary for the same client)
                </label>
            </div>

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
                    {mode.kind === 'create' ? 'Create Contact' : 'Save Changes'}
                </Button>
                <Button type="button" variant="ghost" onClick={() => router.visit('/admin/contacts')}>
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
