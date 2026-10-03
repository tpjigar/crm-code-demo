import { Head, router, useForm } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type { IncidentSeverityInfo, IncidentSeverityValue } from '@/types/models';

type Props = {
    severities: IncidentSeverityInfo[];
};

type FormState = {
    title: string;
    description: string;
    severity: IncidentSeverityValue | '';
};

export default function PortalIncidentCreate({ severities }: Props) {
    const form = useForm<FormState>({ title: '', description: '', severity: '' });

    function submit(e: React.FormEvent) {
        e.preventDefault();
        form.post('/portal/incidents');
    }

    return (
        <>
            <Head title="Report Incident" />
            <div className="flex flex-1 flex-col gap-4 p-4">
                <h1 className="text-2xl font-semibold">Report an Incident</h1>
                <p className="text-sm text-muted-foreground">
                    Describe the issue clearly. Our SOC team will acknowledge within the
                    SLA window for the severity you choose.
                </p>

                <form onSubmit={submit} className="grid gap-4">
                    <div className="flex flex-col gap-1.5">
                        <Label>
                            Severity <span className="text-destructive">*</span>
                        </Label>
                        <select
                            className="h-9 w-full rounded-md border bg-background px-2 text-sm"
                            value={form.data.severity}
                            onChange={(e) => form.setData('severity', e.target.value as IncidentSeverityValue)}
                        >
                            <option value="">— Choose severity —</option>
                            {severities.map((s) => (
                                <option key={s.value} value={s.value}>
                                    {s.label} — SLA to acknowledge: {s.ack_sla_hours}h
                                </option>
                            ))}
                        </select>
                        {form.errors.severity && <p className="text-sm text-destructive">{form.errors.severity}</p>}
                    </div>

                    <div className="flex flex-col gap-1.5">
                        <Label>
                            Title <span className="text-destructive">*</span>
                        </Label>
                        <Input
                            value={form.data.title}
                            onChange={(e) => form.setData('title', e.target.value)}
                            autoFocus
                        />
                        {form.errors.title && <p className="text-sm text-destructive">{form.errors.title}</p>}
                    </div>

                    <div className="flex flex-col gap-1.5">
                        <Label>
                            Description <span className="text-destructive">*</span>
                        </Label>
                        <textarea
                            rows={8}
                            className="w-full rounded-md border bg-background px-3 py-2 text-sm"
                            value={form.data.description}
                            onChange={(e) => form.setData('description', e.target.value)}
                        />
                        {form.errors.description && <p className="text-sm text-destructive">{form.errors.description}</p>}
                    </div>

                    <div className="flex items-center gap-2">
                        <Button type="submit" disabled={form.processing}>
                            Submit Incident
                        </Button>
                        <Button type="button" variant="ghost" onClick={() => router.visit('/portal/incidents')}>
                            Cancel
                        </Button>
                    </div>
                </form>
            </div>
        </>
    );
}

PortalIncidentCreate.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: '/portal/dashboard' },
        { title: 'Incidents', href: '/portal/incidents' },
        { title: 'Report', href: '/portal/incidents/create' },
    ],
};
