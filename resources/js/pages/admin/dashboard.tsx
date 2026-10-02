import { Head } from '@inertiajs/react';
import { AlertTriangle, Building2, ShieldAlert, Users } from 'lucide-react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

type Props = {
    stats: {
        clients: number;
        contacts: number;
        open_incidents: number;
        critical_incidents: number;
    };
};

const tiles = [
    { key: 'clients', label: 'Clients', icon: Building2, accent: 'text-blue-500' },
    { key: 'contacts', label: 'Contacts', icon: Users, accent: 'text-emerald-500' },
    { key: 'open_incidents', label: 'Open Incidents', icon: AlertTriangle, accent: 'text-amber-500' },
    { key: 'critical_incidents', label: 'Critical Incidents', icon: ShieldAlert, accent: 'text-red-500' },
] as const;

export default function AdminDashboard({ stats }: Props) {
    return (
        <>
            <Head title="Admin Dashboard" />
            <div className="flex h-full flex-1 flex-col gap-4 p-4">
                <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
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

AdminDashboard.layout = {
    breadcrumbs: [{ title: 'Dashboard', href: '/admin/dashboard' }],
};
