import BCookie from '../utils/BCookie';

const state = {
    siteName: 'Deadbeat Affiliate Scout',
    accessToken: BCookie.check('DBAccessToken')
        ? BCookie.get('DBAccessToken')
        : null,
    forgot_email: '',
    isAdmin: false,
    isLoggedIn: false,
    userData: null,
    scout_network: [
        'All Networks',
        'cj.com',
        'clickbank.com',
        'shareasale.com',
    ],
};

export default state;
