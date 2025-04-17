import { useQuery } from "@tanstack/vue-query";
import axios from "@/libs/axios";

export function useBarang(options = {}) {
    return useQuery({
        queryKey: ["barang"],
        queryFn: async () =>
            await axios.get("/tambah/barang").then((res: any) => res.data.data),
        ...options,
    });
}