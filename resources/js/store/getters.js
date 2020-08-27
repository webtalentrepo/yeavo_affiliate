const getters = {
    getNetworkList(state) {
        return state.scout_network
    },
    adminCheck(state) {
        return state.isAdmin;
    },
    /**
     * Check authentication state.
     *
     * @param state
     * @returns {null|string|boolean}
     */
    isAuthenticated: (state) => {
        return (state.accessToken && state.accessToken !== "null" && state.accessToken !== "" && state.accessToken !== null && state.accessToken === BCookie.get("BCAccessToken"));
    },

    /**
     * Return authenticated user data.
     *
     * @param state
     * @returns {*}
     */
    userInfo: (state) => {
        return state.authInfo;
    }
}

export default getters;
