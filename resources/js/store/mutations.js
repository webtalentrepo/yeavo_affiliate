import Ls from '../utils/Ls';
import BCookie from '../utils/BCookie';

let mutations = {
    ADD_CANCEL_TOKEN(state, token) {
        state.cancelTokens.push(token);
    },
    CLEAR_CANCEL_TOKENS(state) {
        state.cancelTokens = [];
    },
    /**
     * Set Authenticated Access Token.
     *
     * @param state
     * @param token
     * @param isAdmin
     * @param expires
     */
    setAccessToken: (state, { token, expires, isAdmin }) => {
        BCookie.set('DBAccessToken', token, expires);

        state.accessToken = token;

        state.isLoggedIn = true;

        state.isAdmin = isAdmin;
    },

    /**
     * Change Site Page Title.
     *
     * @param state
     * @param title
     */
    changePageTitle(state, title) {
        state.pageTitle = title;

        document.title = title + ' | ' + state.siteName;
    },

    /**
     * Set User Account Information.
     *
     * @param state
     * @param user
     */
    setUserInfo(state, user) {
        state.userData = user;
    },

    /**
     * User log out.
     * @param state
     */
    destroyAccessToken(state) {
        BCookie.remove('DBAccessToken');

        Ls.remove('DB-Auth-Remember');

        state.userData = null;
        state.accessToken = null;
    },
};

export default mutations;
