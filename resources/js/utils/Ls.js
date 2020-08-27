export default {
    /**
     * Get sessionStorage Item By Key
     *
     * @param key
     * @returns {any}
     */
    get(key) {
        return sessionStorage.getItem(key) ? sessionStorage.getItem(key) : null
    },

    /**
     * Set sessionStorage Item
     *
     * @param key
     * @param val
     */
    set(key, val) {
        sessionStorage.setItem(key, val)
    },

    /**
     * Remove sessionStorage Item By Key
     *
     * @param key
     */
    remove(key) {
        sessionStorage.removeItem(key)
    }
}
