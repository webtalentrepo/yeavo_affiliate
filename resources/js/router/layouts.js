const layouts = [
    {
        path: '/',
        name: 'Home',
        // eslint-disable-next-line no-undef
        component: (resolve) => require(['../layout/users/index'], resolve),
        meta: {
            title: 'Home',
            auth: true,
        },
    },
    {
        path: '/offer-scout',
        name: 'Offer Scout',
        // eslint-disable-next-line no-undef
        component: (resolve) => require(['../pages/offer-scout'], resolve),
        meta: {
            title: 'Offer Scout',
            auth: true,
        },
    },
    {
        path: '/privacy',
        name: 'Offer Scout',
        // eslint-disable-next-line no-undef
        component: (resolve) => require(['../layout/users/index'], resolve),
        meta: {
            title: 'Offer Scout',
            auth: false,
        },
    },
];

export default layouts;
