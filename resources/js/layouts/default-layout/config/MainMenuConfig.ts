import type { MenuItem } from "@/layouts/default-layout/config/types";

const MainMenuConfig: Array<MenuItem> = [
    {
        pages: [
            {
                heading: "Dashboard",
                name: "dashboard",
                route: "/dashboard",
                keenthemesIcon: "element-11",
            },
        ],
    },

    // WEBSITE
    {
        heading: "Website",
        route: "/dashboard/website",
        name: "website",
        pages: [
            // MASTER
            {
                sectionTitle: "Master",
                route: "/master",
                keenthemesIcon: "cube-3",
                name: "master",
                sub: [
                    {
                        sectionTitle: "User",
                        route: "/users",
                        name: "master-user",
                        sub: [
                            {
                                heading: "Role",
                                name: "master-role",
                                route: "/dashboard/master/users/roles",
                            },
                            {
                                heading: "User",
                                name: "master-user",
                                route: "/dashboard/master/users",
                            },
                        ],
                    },
                ],
            },
            {
                sectionTitle: "Tambah",
                route: "/tambah",
                name: "tambah-barang",
                keenthemesIcon: "bi bi-patch-plus",
                sub: [
                    {
                        heading: "Kategori",
                        name: "tambah-kategori",
                        route: "/dashboard/tambah/kategori",
                    },
                    {
                        heading: "Barang",
                        name: "tambah-barang",
                        route: "/dashboard/tambah/barang",
                    },
                ],
            },
            {
                heading: "Pembayaran",
                route: "/dashboard/pembayaran",
                name: "pembayaran",
                keenthemesIcon: "bi bi-calculator",
            },
            {
                heading: "History",
                route: "/dashboard/history",
                name: "history",
                keenthemesIcon: "bi bi-clock-history",
            },
            {
                heading: "Setting",
                route: "/dashboard/setting",
                name: "setting",
                keenthemesIcon: "bi bi-gear-wide-connected",
            },
        ],
    },
];

export default MainMenuConfig;
