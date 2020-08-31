import BCookie from '../utils/BCookie';

const getters = {
    getNetworkList(state) {
        return state.scout_network;
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
        return (
            state.accessToken &&
            state.accessToken !== 'null' &&
            state.accessToken !== '' &&
            state.accessToken !== null &&
            state.accessToken === BCookie.get('DBAccessToken')
        );
    },
};

export default getters;
