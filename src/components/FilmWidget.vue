<template>
  <article>
    <header>Latest film I've seen</header>
    <p v-if="!film">Loading...</p>
    <div v-else class="film-poster">
      <figure>
        <img alt="Film poster" :src="film.poster">
      </figure>
      <div class="film-details">
        <p>
          <strong>{{film.title}}</strong> <small>{{film.year}}</small>
        </p>
        <StarRating :rating="film.rating"></StarRating>
        <p>
          <small>{{film.watched}} - via <a :href="film.link" target="_blank">Letterboxd</a></small>
        </p>
      </div>
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

    @media(max-width: 1024px) {
        img {
            max-width: 75px;
        }
    }

    .film-poster {
      display: flex;
    }

    figure {
      margin-right: 1rem;
      flex-basis: auto;
      flex-grow: 0;
    }

    .film-details {
      flex-basis: auto;
      flex-grow: 1;

    }

    p {
      margin-bottom: 0.5rem;
    }
</style>
