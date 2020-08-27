import Vue from 'vue';

const VEvent = class {
    constructor() {
        this.vue = new Vue();
    }

    /**
     * Vue Event on.
     * @param event
     * @param callback
     * @returns {VEvent}
     */
    listen(event, callback) {
        if (Array.isArray(event)) {
            event.forEach(
                eventName => this.vue.$on(eventName, (callback))
            );
        } else {
            this.vue.$on(event, (callback));
        }

        return this;
    }

    /**
     * Vue Event emit.
     * @param event
     * @param data
     * @returns {VEvent}
     */
    fire(event, data = null) {
        this.vue.$emit(event, data);

        return this;
    }

    /**
     * Vue Event off.
     * @param event
     * @param callback
     * @returns {VEvent}
     */
    stop(event, callback) {
        if (Array.isArray(event)) {
            event.forEach(
                eventName => this.vue.$off(eventName, (callback))
            );
        } else {
            this.vue.$off(event, (callback));
        }

        return this;
    }
};

export default VEvent;
