<script lang="ts">
    import { createEventDispatcher, onMount, onDestroy } from "svelte";
    import { fade } from "svelte/transition";

    import { modalUp } from "../stores";


    const dispatch = createEventDispatcher();

    onMount(() => $modalUp = true)
    onDestroy(() => $modalUp = false)
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

        background-color: hsla(0, 0%, 0%, 0.5);
        z-index: 2000;

        display: flex;
        justify-content: center;
        align-items: center;
    }
</style>
