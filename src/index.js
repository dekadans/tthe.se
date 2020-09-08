import letterboxd from './letterboxd'
import Film from './components/Film.vue'

new Vue({
    el: '#film',
    template : '<Film v-bind:film="film"></Film>',
    data: {
        film : null
    },
    components: {
        Film
    },
    created: async function () {
        this.film = await letterboxd('filmfeed.php').latest();
    }
})
