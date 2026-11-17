export type User = {
    id: number;
    username: string;
    email: string;
    avatar?: string;
    estatus?: boolean;
    status_id?: number;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
    [key: string]: unknown;
};

export type Auth = {
    user: User;
};

export type TwoFactorConfigContent = {
    title: string;
    description: string;
    buttonText: string;
};
