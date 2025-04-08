export interface kurir {
    id?: number;
    name: string;
    email: string;
    phone: string;
    photo?: string;
    status: "aktif" | "nonaktif";
    created_at?: string;
    updated_at?: string;
}
