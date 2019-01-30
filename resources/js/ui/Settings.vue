<template lang="html">
    <div id="settings" class="settings">
        <div class="settings-grid">
            <dark-mode-toggle style="grid-column: span 2"></dark-mode-toggle>

            <label for="theme">
                syntax theme
            </label>
            <input name="theme" type="text">

            <label for="size">
                font size
            </label>
            <input name="size" type="text">

            <label for="height">
                line height
            </label>
            <input name="height" type="text">

            <a href="#" style="grid-column: span 2" class="modal-grid-span text-dimmed hover:text-accent">exit settings</a>

            <div class="text-dimmed mt-16">
                /* made by alex@spatie.be */
            </div>
        </div>
    </div>
</template>

<script>
    import './DarkModeToggle';
</script>
