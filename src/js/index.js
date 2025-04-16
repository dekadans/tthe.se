import Vue from 'vue';
import Activity from "./components/Activity.vue";

new Vue({
    el: '#activity',
    components: {
        Activity
    },
    render(h) {
        return h('Activity');
    }
})