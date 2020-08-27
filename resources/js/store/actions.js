import axios from "axios";

const actions = {
    post({commit}, post_data) {
        return new Promise((resolve, reject) => {
            axios.post(post_data.url, post_data.data)
                .then((response) => {
                    resolve(response);
                })
                .catch((error) => {
                    reject(error);
                });
        });
    },

    getData({commit}, params) {
        return new Promise((resolve, reject) => {
            axios.get(params.url, params.config)
                .then((response) => {
                    resolve(response);
                })
                .catch((error) => {
                    reject(error);
                });
        });
    },

    putData({commit}, put_data) {
        return new Promise((resolve, reject) => {
            axios.put(put_data.url, put_data.data)
                .then((response) => {
                    resolve(response);
                })
                .catch((error) => {
                    reject(error);
                });
        });
    },

    delete({commit}, url) {
        return new Promise((resolve, reject) => {
            axios.delete(url)
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
    retrieveToken({commit}, credentials) {
        return new Promise((resolve, reject) => {
            axios.post("/login", credentials)
                .then((response) => {
                    const {
                        access_token: accessToken,
                        expires_in: tokenExpired,
                    } = response.data;

                    commit("setAccessToken", {
                        token: accessToken,
                        expires: tokenExpired
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
    destroyToken({getters, commit}) {
        if (getters["isAuthenticated"]) {
            return new Promise((resolve, reject) => {
                axios.post("/logout")
                    .then((response) => {
                        commit("destroyAccessToken");

                        resolve(response);
                    })
                    .catch((error) => {
                        commit("destroyAccessToken");

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
    async getUserData({getters, commit}) {
        if (getters["isAuthenticated"]) {
            return new Promise((resolve, reject) => {
                axios.get("/users/me")
                    .then((response) => {
                        commit("setUserInfo", response.data);

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
    registerUser({commit}, credentials) {
        return new Promise((resolve, reject) => {
            axios.post("/register", credentials)
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
    resetPasswordByToken({commit}, credentials) {
        return new Promise((resolve, reject) => {
            axios.post('/reset-password-by-token', credentials)
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
