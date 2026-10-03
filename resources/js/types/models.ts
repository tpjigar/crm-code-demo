export type Client = {
    id: number;
    name: string;
    industry: string | null;
    website: string | null;
    contact_email: string | null;
    contact_phone: string | null;
    country: string | null;
    notes: string | null;
    owner?: {
        id: number | null;
        name: string | null;
        email: string | null;
    };
    created_at: string | null;
    updated_at: string | null;
};

export type OwnerOption = {
    id: number;
    name: string;
};

export type ClientOption = {
    id: number;
    name: string;
};

export type Contact = {
    id: number;
    client_id: number;
    first_name: string;
    last_name: string;
    full_name: string;
    job_title: string | null;
    email: string | null;
    phone: string | null;
    email_masked: boolean;
    phone_masked: boolean;
    is_primary: boolean;
    notes: string | null;
    client?: {
        id: number | null;
        name: string | null;
    };
    created_at: string | null;
    updated_at: string | null;
};

export type IncidentSeverityValue = 'low' | 'medium' | 'high' | 'critical';
export type IncidentStatusValue = 'new' | 'investigating' | 'resolved' | 'closed';

export type IncidentStatusInfo = {
    value: IncidentStatusValue;
    label: string;
    color: string;
    allowed_next: { value: IncidentStatusValue; label: string }[];
};

export type IncidentSeverityInfo = {
    value: IncidentSeverityValue;
    label: string;
    color: string;
    ack_sla_hours: number;
};

export type Incident = {
    id: number;
    reference: string;
    client_id: number;
    title: string;
    description: string;
    severity: IncidentSeverityInfo;
    status: IncidentStatusInfo;
    sla_breached: boolean;
    acknowledged_at: string | null;
    resolved_at: string | null;
    closed_at: string | null;
    client?: { id: number | null; name: string | null };
    assignee?: { id: number; name: string } | null;
    reporter?: { id: number; name: string } | null;
    created_at: string | null;
    updated_at: string | null;
};

export type AssigneeOption = {
    id: number;
    name: string;
};

export type ActiveSession = {
    id: string;
    user: { id: number; name: string; email: string } | null;
    ip_address: string | null;
    browser: string;
    os: string;
    device: string;
    is_current: boolean;
    last_activity_at: string;
    last_activity_human: string;
};

export type AuditLogEntry = {
    id: number;
    log_name: string | null;
    event: string | null;
    description: string;
    subject_type: string | null;
    subject_id: number | null;
    causer: { id: number; name: string | null; email: string | null } | null;
    properties: Record<string, unknown>;
    created_at: string | null;
};

export type Paginated<T> = {
    data: T[];
    links: { url: string | null; label: string; active: boolean }[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number | null;
    to: number | null;
};
