export interface User {
    id: number;
    name: string;
    email: string;
    email_verified_at?: string;
    phone?: string;
    bio?: string;
    avatar_url?: string;
    role?: string;
    is_admin?: boolean;
    panel_title?: string;
    is_approved_writer?: boolean;
    has_writer_profile?: boolean;
    writer_status?: string | null;
    unread_notifications_count?: number;
}

export type PageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
    auth: {
        user: User;
    };
};
