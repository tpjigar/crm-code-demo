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
