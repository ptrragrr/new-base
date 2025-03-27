export interface kurir {
    id?: number;
    name: string;
    email: string;
    phone: string;
    photo?: string;
    password: string;
    rating: string;
    status: "aktif" | "nonaktif";
    created_at?: string;
    updated_at?: string;
}
