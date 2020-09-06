import letterboxd from './letterboxd';

Vue.component('film', {
    props : ['film'],
    template : `
        <p v-if="!film">Loading...</p>  
        <article v-else class="media">
            <figure class="media-left">
                <img :src="film.poster" style="max-width: 150px">
            </figure>
            <div class="media-content">
                <p>
                    <strong>{{film.title}}</strong> <small>{{film.year}}</small>
                </p>
                <rating :rating="film.rating"></rating>
                <p class="mt-4">
                    <small>{{film.watched}} - via <a :href="film.link" target="_blank">Letterboxd</a></small>
                </p>
            </div>
        </article>
    `
});

Vue.component('rating', {
    props : ['rating'],
    methods : {
        icons : function() {
            let stars = Math.floor(this.rating);
            let halfStars = (this.rating % 1 === 0.5) ? 1 : 0;
            let icons = [];

            for (let i = 0; i < stars; i++) {
                icons.push('fas fa-star');
            }

            if (halfStars === 1) {
                icons.push('fas fa-star-half');
            }

            return icons;
        }
    },
    template: `
        <p>
            <span v-for="icon in icons()" style="padding-right: 2px;">
                <i :class="icon"></i>
            </span>
        </p>
    `
});

let latestFilm = new Vue({
    el: '#film',
    data: {
        film : null
    },
    created: async function () {
        this.film = await letterboxd('filmfeed.php').latest();
    }
})
