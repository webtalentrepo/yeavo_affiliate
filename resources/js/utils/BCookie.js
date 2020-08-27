export default {
    /**
     * Set Cookie
     *
     * @param cName Cookie Name
     * @param cValue Cookie Value
     * @param exSeconds Expire Day
     */
    set(cName, cValue, exSeconds) {
        let d = new Date();
        d.setTime(d.getTime() + exSeconds * 1000);

        document.cookie = `${cName}=${cValue};expires=${d.toUTCString()};path=/`;
    },

    /**
     * Get Cookie Value By Name
     *
     * @param cName
     * @returns {string}
     */
    get(cName) {
        let name = `${cName}=`;
        let ca = document.cookie.split(';');

        for (let i = 0; i < ca.length; i++) {
            let c = ca[i];
            while (c.charAt(0) === ' ') {
                c = c.substring(1);
            }

            if (c.indexOf(name) === 0) {
                return c.substring(name.length, c.length);
            }
        }

        return '';
    },

    /**
     * Check Cookie Exists By Name
     *
     * @param cName
     * @returns {*|string|boolean}
     */
    check(cName) {
        let cookie_data = this.get(cName);

        return (cookie_data && cookie_data !== '');
    },

    /**
     * Remove Cookie By Name
     *
     * @param cName
     */
    remove(cName) {
        document.cookie = `${cName}=;expires=Thu, 01 Jan 1970 00:00:00 GMT;path=/`;
    }
}
