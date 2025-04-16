
<template>
  <div>
    <FilmWidget :film="film"></FilmWidget>
  </div>
</template>

<script>
import FilmWidget from "./FilmWidget.vue";
import letterboxd from "../letterboxd";
import BookWidget from "./BookWidget.vue";

export default {
  name: "Activity",
  components: {BookWidget, FilmWidget},
  data() {
    return {
      film: null,
      book: null
    }
  },
  created: async function () {
    this.film = await letterboxd('/activity/film/rss').latest();
    //this.book = await fetch('book.php').then(d => d.json());
  }
}
</script>

<style scoped>

</style>