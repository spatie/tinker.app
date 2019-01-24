<template>
    <a href="#" v-clipboard:copy="url" v-clipboard:success="onCopy">
        <span><img src="/images/icon-share.svg" alt="share session"></span>
        <span v-if="successful" class="hidden | sm:inline">URL copied!</span>
        <span v-else class="hidden | sm:inline">share session</span>
    </a>
</template>
<script>
    export default {
        data() {
            return {
                successful: false,
            };
        },

        computed: {
            url() {
                return window.location;
            }
        },

        methods: {
            onCopy() {
                this.successful = true;

                setTimeout(() => {
                    this.successful = false;
                }, 1500);
            }
        }
    }
</script>
