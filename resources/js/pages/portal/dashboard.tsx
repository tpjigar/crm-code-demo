import { Head } from '@inertiajs/react';
import { AlertTriangle, CheckCircle2, Users } from 'lucide-react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

type Props = {
    stats: {
        contacts: number;
        open_incidents: number;
        resolved_incidents: number;
    };
};

const tiles = [
    { key: 'contacts', label: 'My Contacts', icon: Users, accent: 'text-emerald-500' },
    { key: 'open_incidents', label: 'Open Incidents', icon: AlertTriangle, accent: 'text-amber-500' },
    { key: 'resolved_incidents', label: 'Resolved Incidents', icon: CheckCircle2, accent: 'text-green-500' },
] as const;

export default function PortalDashboard({ stats }: Props) {
    return (
        <>
            <Head title="My Dashboard" />
            <div className="flex h-full flex-1 flex-col gap-4 p-4">
                <div className="grid gap-4 md:grid-cols-3">
                    {tiles.map(({ key, label, icon: Icon, accent }) => (
                        <Card key={key}>
                            <CardHeader className="flex flex-row items-center justify-between pb-2">
                                <CardTitle className="text-sm font-medium">{label}</CardTitle>
                                <Icon className={`size-5 ${accent}`} />
                            </CardHeader>
                            <CardContent>
                                <div className="text-3xl font-semibold">{stats[key]}</div>
                            </CardContent>
                        </Card>
                    ))}
                </div>
            </div>
        </>
    );
}

PortalDashboard.layout = {
    breadcrumbs: [{ title: 'Dashboard', href: '/portal/dashboard' }],
};
