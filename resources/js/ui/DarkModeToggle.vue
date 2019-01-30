<template>
    <a href="#" @click="toggleDarkMode" class="hover:text-accent">
        <span v-html="darkMode ? 'switch to light ui' : 'switch to dark ui'"/>
    </a>
</template>
<script>
    export default {
        computed: {
            darkMode() {
                return this.$store.state.darkMode;
            },
        },
        methods: {
            toggleDarkMode() {
                this.$store.commit('toggleDarkMode');

                document.documentElement.classList.toggle('darkmode', this.darkMode);
            },
        },
    };
</script>
