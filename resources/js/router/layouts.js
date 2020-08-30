const layouts = [
    {
        path: '/',
        name: 'Dashboard',
        // eslint-disable-next-line no-undef
        component: (resolve) => require(['../layout/users/index'], resolve),
        meta: {
            title: 'Deadbeat Affiliate Scout',
            auth: true,
        },
    },
];

export default layouts;
