const layouts = [{
    path: "/",
    name: "Dashboard",
    component: resolve => require(['../layout/users/index'], resolve),
    meta: {
        title: "Deadbeat Affiliate Scout",
        auth: true,
    },
}];

export default layouts
