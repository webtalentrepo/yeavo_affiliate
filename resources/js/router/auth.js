import store from '../store/store';

const Login = () =>
    import(/* webpackChunkName: "login" */ '../pages/Auth/Login.vue');
const Logout = () =>
    import(/* webpackChunkName: "logout" */ '../pages/Auth/Logout.vue');
// const Register = () =>
//     import(/* webpackChunkName: "register" */ '../pages/Auth/Register.vue');
const Activate = () =>
    import(/* webpackChunkName: "activate" */ '../pages/Auth/Activate.vue');
const ForgotPassword = () =>
    import(
        /* webpackChunkName: "forgotpassword" */ '../pages/Auth/ForgotPassword.vue'
    );
const ResetPassword = () =>
    import(
        /* webpackChunkName: "resetpassword" */ '../pages/Auth/ResetPassword.vue'
    );

let loginBeforeEnter = function (next) {
    if (store.getters.isAuthenticated) {
        // if (store.getters.userInfo.user_status === 1) {
        // if (store.getters.userInfo.billing_status && store.getters['auth/userInfo'].billing_status !== null) {
        return next({
            name: 'Dashboard',
        });
        // }
        // }
    }

    return next();
};

const auth_routes = [
    {
        path: '/login',
        name: 'Login',
        component: Login,
        meta: {
            title: 'Login',
            auth: false,
        },
        beforeEnter: (to, from, next) => {
            return loginBeforeEnter(next);
        },
    },
    {
        path: '/logout',
        name: 'Logout',
        component: Logout,
        meta: {
            title: 'Logout',
            auth: false,
        },
    },
    // {
    //     path: '/register',
    //     name: 'Register',
    //     component: Register,
    //     meta: {
    //         title: 'Sign up',
    //         auth: false,
    //     },
    // },
    {
        path: '/forgot-password',
        name: 'ForgotPassword',
        component: ForgotPassword,
        meta: {
            title: 'Send Email',
            auth: false,
        },
    },
    {
        path: '/reset-password/:token',
        name: 'ResetPassword',
        component: ResetPassword,
        meta: {
            title: 'Reset Password',
            auth: false,
        },
    },
    {
        path: '/activate/:token',
        name: 'Activate',
        component: Activate,
        meta: {
            title: 'Activate',
            auth: false,
        },
    },
];

export default auth_routes;
