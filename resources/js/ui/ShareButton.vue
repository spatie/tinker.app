<template>
    <a href="#" v-clipboard:copy="url" v-clipboard:success="onCopy">
        <span>
            share session
        </span>
        <span v-if="successful" class="absolute pin flex justify-center items-center bg-background text-accent">
            <span class="hidden | sm:inline">URL&nbsp;</span>copied!
        </span>
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
