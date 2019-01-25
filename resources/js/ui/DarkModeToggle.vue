<template>
    <a href="#" @click="toggleDarkMode">
        <span><img src="/images/icon-toggle.svg" alt="toggle ui"></span>
        <span v-if="darkMode" class="hidden | sm:inline">light ui</span>
        <span v-else class="hidden | sm:inline">dark ui</span>
    </a>
</template>
<script>
    export default {
        computed: {
            darkMode() {
                return this.$store.state.darkMode;
            }
        },
        methods: {
            toggleDarkMode() {
                this.$store.commit('toggleDarkMode')
            },
        },
    }
</script>
