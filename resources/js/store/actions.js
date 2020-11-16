import axios from 'axios';

const actions = {
    CANCEL_PENDING_REQUESTS(context) {
        // Cancel all request where a token exists
        // eslint-disable-next-line no-unused-vars
        context.state.cancelTokens.forEach((request, i) => {
            if (request.cancel) {
                request.cancel();
            }
        });

        // Reset the cancelTokens store
        context.commit('CLEAR_CANCEL_TOKENS');
    },
    // eslint-disable-next-line no-unused-vars
    post({ commit }, post_data) {
        return new Promise((resolve, reject) => {
            axios
                .post(post_data.url, post_data.data)
                .then((response) => {
                    resolve(response);
                })
                .catch((error) => {
                    reject(error);
                });
        });
    },

    // eslint-disable-next-line no-unused-vars
    getData({ commit }, params) {
        return new Promise((resolve, reject) => {
            axios
                .get(params.url, params.config)
                .then((response) => {
                    resolve(response);
                })
                .catch((error) => {
                    reject(error);
                });
        });
    },

    // eslint-disable-next-line no-unused-vars
    putData({ commit }, put_data) {
        return new Promise((resolve, reject) => {
            axios
                .put(put_data.url, put_data.data)
                .then((response) => {
                    resolve(response);
                })
                .catch((error) => {
                    reject(error);
                });
        });
    },

    // eslint-disable-next-line no-unused-vars
    delete({ commit }, url) {
        return new Promise((resolve, reject) => {
            axios
                .delete(url)
                .then((response) => {
                    resolve(response);
                })
                .catch((error) => {
                    reject(error);
                });
        });
    },
    /**
     * Login a user
     *
     * @param commit
     * @param credentials {Object} User credentials
     * @param credentials.email {string} User email
     * @param credentials.password {string} User password
     * @param credentials.remember_me {boolean} Remember me
     * @returns {Promise<unknown>}
     */
    retrieveToken({ commit }, credentials) {
        return new Promise((resolve, reject) => {
            axios
                .post('/login', credentials)
                .then((response) => {
                    const { accessToken, expiresIn, isAdmin } = response.data;

                    commit('setAccessToken', {
                        token: accessToken,
                        expires: expiresIn,
                        isAdmin: isAdmin,
                    });

                    resolve(response);
                })
                .catch((error) => {
                    reject(error);
                });
        });
    },

    /**
     * Logout a user
     * @param context {Object}
     */
    destroyToken({ getters, commit }) {
        if (getters['isAuthenticated']) {
            return new Promise((resolve, reject) => {
                axios
                    .post('/logout')
                    .then((response) => {
                        commit('destroyAccessToken');

                        resolve(response);
                    })
                    .catch((error) => {
                        commit('destroyAccessToken');

                        reject(error);
                    });
            });
        }
    },

    /**
     * Get authenticated account information.
     * @param context
     * @returns {Promise<unknown>}
     */
    async getUserData({ getters, commit }) {
        if (getters['isAuthenticated']) {
            return new Promise((resolve, reject) => {
                axios
                    .get('/users/me')
                    .then((response) => {
                        if (response.data.result === 'success') {
                            commit('setUserInfo', response.data.userInfo);
                        }

                        resolve(response);
                    })
                    .catch((error) => {
                        reject(error);
                    });
            });
        }
    },

    /**
     * Create Account Base Information
     *
     * @param commit
     * @param credentials
     * @returns {Promise<unknown>}
     */
    // eslint-disable-next-line no-unused-vars
    registerUser({ commit }, credentials) {
        return new Promise((resolve, reject) => {
            axios
                .post('/register', credentials)
                .then((response) => {
                    resolve(response);
                })
                .catch((error) => {
                    reject(error);
                });
        });
    },

    // eslint-disable-next-line no-unused-vars
    getEmailByCode({ commit }, token) {
        return new Promise((resolve, reject) => {
            axios
                .post('/get-email-by-token', {
                    activation_code: token,
                })
                .then((response) => {
                    resolve(response);
                })
                .catch((error) => {
                    reject(error);
                });
        });
    },

    /**
     * Reset New Password
     *
     * @param dispatch
     * @param credentials
     * @returns {Promise}
     */
    // eslint-disable-next-line no-unused-vars
    resetPasswordByToken({ commit }, credentials) {
        return new Promise((resolve, reject) => {
            axios
                .post('/reset-password-by-token', credentials)
                .then((response) => {
                    resolve(response);
                })
                .catch((error) => {
                    reject(error);
                });
        });
    },

    // eslint-disable-next-line no-unused-vars
    getEmailByToken({ commit }, token) {
        return new Promise((resolve, reject) => {
            axios
                .post('/get-email-by-token', { token: token })
                .then((response) => {
                    resolve(response);
                })
                .catch((error) => {
                    reject(error);
                });
        });
    },
};

export default actions;
