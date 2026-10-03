import { Head, router } from '@inertiajs/react';
import { Monitor, Smartphone, Tablet, Terminal, Trash2 } from 'lucide-react';
import { Button } from '@/components/ui/button';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import type { ActiveSession, Paginated } from '@/types/models';

type Props = {
    sessions: Paginated<ActiveSession>;
    filters: { user_id: number | null };
};

function deviceIcon(device: string) {
    const Icon = device === 'Phone' ? Smartphone : device === 'Tablet' ? Tablet : device === 'Script' ? Terminal : Monitor;
    return <Icon className="size-4 text-muted-foreground" />;
}

export default function SessionsIndex({ sessions }: Props) {
    function revoke(session: ActiveSession) {
        if (session.is_current) return;
        if (!confirm(`Revoke this session for ${session.user?.email ?? 'unknown user'}? They will be signed out on next request.`)) {
            return;
        }
        router.delete(`/admin/security/sessions/${session.id}`);
    }

    return (
        <>
            <Head title="Active Sessions" />
            <div className="flex flex-1 flex-col gap-4 p-4">
                <div>
                    <h1 className="text-2xl font-semibold">Active Sessions</h1>
                    <p className="text-sm text-muted-foreground">
                        Every signed-in session across the fleet. Revoking a session signs
                        that device out on its next request. You cannot revoke your own
                        current session from here.
                    </p>
                </div>

                <div className="rounded-xl border">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>User</TableHead>
                                <TableHead>IP</TableHead>
                                <TableHead>Device</TableHead>
                                <TableHead>Last activity</TableHead>
                                <TableHead className="w-20 text-right">Action</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {sessions.data.length === 0 && (
                                <TableRow>
                                    <TableCell colSpan={5} className="py-8 text-center text-muted-foreground">
                                        No active sessions.
                                    </TableCell>
                                </TableRow>
                            )}

                            {sessions.data.map((s) => (
                                <TableRow key={s.id} className={s.is_current ? 'bg-muted/30' : ''}>
                                    <TableCell>
                                        {s.user ? (
                                            <>
                                                <div className="font-medium">{s.user.name}</div>
                                                <div className="text-xs text-muted-foreground">{s.user.email}</div>
                                            </>
                                        ) : (
                                            <span className="text-muted-foreground">Guest</span>
                                        )}
                                        {s.is_current && (
                                            <span className="ml-2 rounded-full bg-blue-100 px-2 py-0.5 text-[10px] font-medium text-blue-800 dark:bg-blue-900/40 dark:text-blue-200">
                                                this session
                                            </span>
                                        )}
                                    </TableCell>
                                    <TableCell className="font-mono text-xs">{s.ip_address ?? '—'}</TableCell>
                                    <TableCell>
                                        <div className="flex items-center gap-2 text-sm">
                                            {deviceIcon(s.device)}
                                            <span>{s.browser} · {s.os}</span>
                                        </div>
                                    </TableCell>
                                    <TableCell className="text-xs text-muted-foreground" title={s.last_activity_at}>
                                        {s.last_activity_human}
                                    </TableCell>
                                    <TableCell className="text-right">
                                        <Button
                                            size="icon"
                                            variant="ghost"
                                            disabled={s.is_current}
                                            onClick={() => revoke(s)}
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
                    Showing {sessions.from ?? 0}–{sessions.to ?? 0} of {sessions.total}
                </div>
            </div>
        </>
    );
}

SessionsIndex.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: '/admin/dashboard' },
        { title: 'Security', href: '#' },
        { title: 'Sessions', href: '/admin/security/sessions' },
    ],
};
