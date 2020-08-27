import Ls from "../utils/Ls";
import BCookie from "../utils/BCookie";

let mutations = {
    /**
     * Set Authenticated Access Token.
     *
     * @param state
     * @param token
     * @param expires
     */
    setAccessToken: (state, {token, expires}) => {
        BCookie.set("BCAccessToken", token, expires);

        state.accessToken = token;
    },

    /**
     * Change Site Page Title.
     *
     * @param state
     * @param title
     */
    changePageTitle(state, title) {
        state.pageTitle = title;
        
        document.title = title + " | " + state.siteName
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
        BCookie.remove("BCAccessToken");

        Ls.remove("BC-Auth-Remember");

        state.userData = null;
        state.accessToken = null;
    }
};

export default mutations
