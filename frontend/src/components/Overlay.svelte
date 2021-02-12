<script lang="ts">
    import { createEventDispatcher } from "svelte";
    import { fade } from "svelte/transition";

    const dispatch = createEventDispatcher();
</script>


<div class="overlay"
    on:mousedown|stopPropagation
    on:mouseup|stopPropagation
    on:click|self={() => dispatch("clickedOutside")}
    transition:fade={{duration: 200}}
>
    <slot></slot>
</div>


<style>
    .overlay {
        position: fixed;
        top: 0;
        right: 0;
        bottom: 0;
        left: 0;

        background-color: rgba(0, 0, 0, 0.5);
        z-index: 1000;

        display: flex;
        justify-content: center;
        align-items: center;
    }
</style>
