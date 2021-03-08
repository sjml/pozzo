<script lang="ts">
    import { createEventDispatcher, onDestroy, onMount } from "svelte";
    import { fade } from "svelte/transition";

    import { modalUp } from "../stores";

    const dispatch = createEventDispatcher();

    onMount(() => {$modalUp = true;})
    onDestroy(() => {$modalUp = false;})

    export let zIndex = 2000;
    export let fadeTime = 200;
    // quote invisible endquote
    export let isInvisible = false;
</script>


<div class="overlay"
    on:mousedown|stopPropagation
    on:mouseup|stopPropagation
    on:click|self={() => dispatch("clickedOutside")}
    transition:fade={{duration: fadeTime}}
    style={`z-index: ${zIndex}; opacity: ${isInvisible ? 0.001 : 1.0}`}
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

        display: flex;
        justify-content: center;
        align-items: center;
    }
</style>
