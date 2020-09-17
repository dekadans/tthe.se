<template>
    <p v-if="!film">Loading...</p>
    <article v-else class="media">
        <figure class="media-left">
            <img alt="Film poster" :src="film.poster">
        </figure>
        <div class="media-content">
            <p>
                <strong>{{film.title}}</strong> <small>{{film.year}}</small>
            </p>
            <StarRating :rating="film.rating"></StarRating>
            <p class="mt-4">
                <small>{{film.watched}} - via <a :href="film.link" target="_blank">Letterboxd</a></small>
            </p>
        </div>
    </article>
</template>

<script>
    import StarRating from './StarRating.vue'

    export default {
        props : ['film'],
        components : {
            StarRating
        }
    }
</script>

<style scoped>
    img {
        max-width: 150px;
    }

    @media(max-width: 576px) {
        img {
            max-width: 75px;
        }
    }
</style>
