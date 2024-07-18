import ZonesListComponent from "../../components/admin/zones/ZonesListComponent";
import ZonesComponent from "../../components/admin/zones/ZonesComponent";
import ZonesShowComponent from "../../components/admin/zones/ZonesShowComponent";

export default [
    {
        path: "/admin/zones",
        component: ZonesComponent,
        name: "admin.zones",
        redirect: { name: "admin.zones.list" },
        meta: {
            isFrontend: false,
            auth: true,
            permissionUrl: "settings",
            breadcrumb: "zones",
        },
        children: [
            {
                path: "list",
                component: ZonesListComponent,
                name: "admin.zones.list",
                meta: {
                    isFrontend: false,
                    auth: true,
                    permissionUrl: "settings",
                    breadcrumb: "",
                },
            },
            {
                path: "show/:id",
                component: ZonesShowComponent,
                name: "admin.zones.show",
                meta: {
                    isFrontend: false,
                    auth: true,
                    permissionUrl: "settings",
                    breadcrumb: "view",
                },
            },
        ],
    },
]
