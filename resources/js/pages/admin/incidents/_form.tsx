import { router, useForm } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type {
    AssigneeOption,
    ClientOption,
    Incident,
    IncidentSeverityInfo,
    IncidentSeverityValue,
} from '@/types/models';

type Mode = { kind: 'create' } | { kind: 'edit'; incident: Incident };

type Props = {
    mode: Mode;
    clients: ClientOption[];
    assignees: AssigneeOption[];
    severities: IncidentSeverityInfo[];
};

type FormState = {
    client_id: string;
    title: string;
    description: string;
    severity: IncidentSeverityValue | '';
    assigned_to_id: string;
};

function initial(mode: Mode): FormState {
    if (mode.kind === 'edit') {
        const i = mode.incident;
        return {
            client_id: i.client_id.toString(),
            title: i.title,
            description: i.description,
            severity: i.severity.value,
            assigned_to_id: i.assignee?.id?.toString() ?? '',
        };
    }
    return { client_id: '', title: '', description: '', severity: '', assigned_to_id: '' };
}

export function IncidentForm({ mode, clients, assignees, severities }: Props) {
    const form = useForm<FormState>(initial(mode));

    function submit(e: React.FormEvent) {
        e.preventDefault();
        if (mode.kind === 'create') {
            form.post('/admin/incidents');
        } else {
            form.put(`/admin/incidents/${mode.incident.id}`);
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

            <Field label="Severity" error={form.errors.severity} required>
                <select
                    className="h-9 w-full rounded-md border bg-background px-2 text-sm"
                    value={form.data.severity}
                    onChange={(e) => form.setData('severity', e.target.value as IncidentSeverityValue)}
                >
                    <option value="">— Choose severity —</option>
                    {severities.map((s) => (
                        <option key={s.value} value={s.value}>
                            {s.label} — ack SLA {s.ack_sla_hours}h
                        </option>
                    ))}
                </select>
            </Field>

            <div className="md:col-span-2">
                <Field label="Title" error={form.errors.title} required>
                    <Input
                        value={form.data.title}
                        onChange={(e) => form.setData('title', e.target.value)}
                        autoFocus
                    />
                </Field>
            </div>

            <div className="md:col-span-2">
                <Field label="Description" error={form.errors.description} required>
                    <textarea
                        rows={6}
                        className="w-full rounded-md border bg-background px-3 py-2 text-sm"
                        value={form.data.description}
                        onChange={(e) => form.setData('description', e.target.value)}
                    />
                </Field>
            </div>

            <Field label="Assignee" error={form.errors.assigned_to_id}>
                <select
                    className="h-9 w-full rounded-md border bg-background px-2 text-sm"
                    value={form.data.assigned_to_id}
                    onChange={(e) => form.setData('assigned_to_id', e.target.value)}
                >
                    <option value="">— Unassigned —</option>
                    {assignees.map((a) => (
                        <option key={a.id} value={a.id}>
                            {a.name}
                        </option>
                    ))}
                </select>
            </Field>

            <div className="flex items-end gap-2">
                <Button type="submit" disabled={form.processing}>
                    {mode.kind === 'create' ? 'Create Incident' : 'Save Changes'}
                </Button>
                <Button type="button" variant="ghost" onClick={() => router.visit('/admin/incidents')}>
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
