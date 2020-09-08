<template>
    <p v-if="!film">Loading...</p>
    <article v-else class="media">
        <figure class="media-left">
            <img :src="film.poster" style="max-width: 150px">
        </figure>
        <div class="media-content">
            <p>
                <strong>{{film.title}}</strong> <small>{{film.year}}</small>
            </p>
            <Rating :rating="film.rating"></Rating>
            <p class="mt-4">
                <small>{{film.watched}} - via <a :href="film.link" target="_blank">Letterboxd</a></small>
            </p>
        </div>
    </article>
</template>

<script>
    import Rating from './Rating.vue'

    export default {
        props : ['film'],
        components : {
            Rating
        }
    }
</script>
