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

document.querySelector('#scroll-down').addEventListener('click', () => {
    document.querySelector('#cv').scrollIntoView({behavior:'smooth'});
});
