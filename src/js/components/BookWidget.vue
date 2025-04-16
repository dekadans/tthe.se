<template>
  <div class="box">
    <p v-if="!book">Loading...</p>
    <article v-else class="media">
      <div class="media-content">
        <p>
          <span class="tag is-light is-pulled-right">Book</span>
        </p>
        <p>
          <strong>{{book.title}}</strong> <small>{{book.year}}</small>
        </p>
        <p>
          {{book.author}}
        </p>

        <p v-if="book.error">
          <em>{{book.error}}</em>
        </p>
      </div>
    </article>
  </div>
</template>

<script>
export default {
  name: "BookWidget",
  props: ["book"]
}
</script>

<style scoped>

</style>