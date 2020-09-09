import letterboxd from './letterboxd'
import Vue from 'vue';
import FilmWidget from './components/FilmWidget.vue'

new Vue({
    el: '#film',
    data: {
        film : null
    },
    components: {
        FilmWidget
    },
    render(h) {
        return h('FilmWidget', {
            props : {
                film : this.film
            }
        });
    },
    created: async function () {
        this.film = await letterboxd('filmfeed.php').latest();
    }
})
