<template>
    <p>
            <span v-for="icon in icons()">
                <i :class="icon"></i>
            </span>
    </p>
</template>

<script>
    export default {
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
        }
    }
</script>

<style scoped>
    span {
        padding-right: 2px;
    }
</style>
