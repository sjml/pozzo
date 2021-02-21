<script lang="ts">
    export let title: string = null;
    export let isBig: boolean = false;
    export let isToggled: boolean = false;
    export let isDisabled: boolean = false;
    export let margin: string = "0";
</script>

<div class="button"
    on:click

    class:isBig
    class:isToggled
    class:isDisabled
    title={title}
    style={`margin: ${margin};`}
>
    <slot />
</div>

<style>
    .button {
        cursor: pointer;
        display: flex;
        flex-direction: row;
        align-items: center;
        transition-property: color;
        transition-duration: 200ms;
    }

    .button.isBig {
        flex-direction: column;
    }

    .button.isToggled {
        color: var(--edit-color);
        transition-property: color;
        transition-duration: 200ms;
    }

    .button.isDisabled {
        filter: brightness(0.5);
        cursor: default;
        pointer-events: none;
    }

    .button :global(svg) {
        width: var(--button-size);
        height: var(--button-size);
        transition-property: transform;
        transition-duration: 200ms;
    }

    .button.isBig :global(svg) {
        width: 40px;
        height: 40px;
    }

    .button:hover :global(svg) {
        transform: scale(1.2);
        transition-property: transform;
        transition-duration: 200ms;
    }

    .button.isDisabled:hover :global(svg) {
        transform: scale(1.0);
    }
</style>
