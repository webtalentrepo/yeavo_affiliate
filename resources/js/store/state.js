import BCookie from '../utils/BCookie';

const state = {
    siteName: 'Tewl Kit',
    siteSubName: 'Market Data Demystified',
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
