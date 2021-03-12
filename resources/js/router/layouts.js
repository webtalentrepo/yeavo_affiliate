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
        path: '/keyword-tool',
        name: 'QuestionTail',
        // eslint-disable-next-line no-undef
        component: (resolve) => require(['../pages/keyword-tool'], resolve),
        meta: {
            title: 'QuestionTail',
            auth: true,
        },
    },
    {
        path: '/privacy',
        name: 'Privacy Policy',
        // eslint-disable-next-line no-undef
        component: (resolve) => require(['../layout/users/privacy'], resolve),
        meta: {
            title: 'Offer Scout',
            auth: false,
        },
    },
    {
        path: '/done-for-you',
        name: 'Done-For-You Connect',
        // eslint-disable-next-line no-undef
        component: (resolve) => require(['../pages/done-for-you'], resolve),
        meta: {
            title: 'Done-For-You Connect',
            auth: true,
        },
    },
    {
        path: '/done-for-you/add-a-worker',
        name: 'Add A Worker',
        // eslint-disable-next-line no-undef
        component: (resolve) =>
            // eslint-disable-next-line no-undef
            require(['../pages/done-for-you/add-a-worker'], resolve),
        meta: {
            title: 'Add A Worker',
            auth: true,
        },
    },
    {
        path: '/done-for-you/edit/:id',
        name: 'Edit A Worker',
        // eslint-disable-next-line no-undef
        component: (resolve) =>
            // eslint-disable-next-line no-undef
            require(['../pages/done-for-you/add-a-worker'], resolve),
        meta: {
            title: 'Edit A Worker',
            auth: true,
        },
    },
    {
        path: '/done-for-you/details',
        name: 'Details',
        // eslint-disable-next-line no-undef
        component: (resolve) =>
            // eslint-disable-next-line no-undef
            require(['../pages/done-for-you/done-for-you-detail'], resolve),
        meta: {
            title: 'Details',
            auth: true,
        },
    },
    {
        path: '/done-for-you/my-listings',
        name: 'My Listings',
        // eslint-disable-next-line no-undef
        component: (resolve) =>
            // eslint-disable-next-line no-undef
            require(['../pages/done-for-you/my-listings'], resolve),
        meta: {
            title: 'My Listings',
            auth: true,
        },
    },
    {
        path: '/done-for-you/favorites',
        name: 'Favorites',
        // eslint-disable-next-line no-undef
        component: (resolve) =>
            // eslint-disable-next-line no-undef
            require(['../pages/done-for-you/favorites'], resolve),
        meta: {
            title: 'Favorites',
            auth: true,
        },
    },
];

export default layouts;
