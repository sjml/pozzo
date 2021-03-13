<script lang="ts">
    import { onMount, createEventDispatcher, onDestroy } from "svelte";

    import type { Album } from "../pozzo.type";
    import { AlbumType } from "../pozzo.type";
    import { currentAlbumStore, navSelection } from "../stores";
    import { RunApi } from "../api";
    import LazyLoad from "./LazyLoad.svelte";
    import Button from "./Button.svelte";
    import Overlay from "./Overlay.svelte";

    const dispatch = createEventDispatcher();

    export let posX: number;
    export let posY: number;

    let contextMenuDiv: HTMLDivElement = null;
    let albumList: Album[] = [];
    let albumListShown = false;
    let newAlbumPromptShowing = false;
    async function getAlbumList() {
        const res = await RunApi("/album/list", {authorize: true});
        if (res.success) {
            albumList = res.data;
        }
        else {
            console.error(res);
        }
    }

    onMount(() => {
        getAlbumList();

        if (contextMenuDiv.clientWidth + posX > document.body.clientWidth) {
            posX -= contextMenuDiv.clientWidth;
        }
        if (contextMenuDiv.clientHeight + posY > document.body.clientHeight) {
            posY -= contextMenuDiv.clientHeight;
        }
    });

    onDestroy(() => {
        if ($navSelection.length == 1) {
            $navSelection = [];
        }
    })

</script>


{#if newAlbumPromptShowing}
    <LazyLoad loader={"NewAlbumPrompt"}
        on:dismissed={() => newAlbumPromptShowing = false}
        on:done={(evt) => dispatch("move", {targetAlbumID: evt.detail.newAlbumID})}
    />
{/if}

<Overlay
    on:clickedOutside={() => dispatch("dismissed")}
    isInvisible={true}
    zIndex={1300}
    fadeTime={0}
/>

<div
    bind:this={contextMenuDiv}
    class="contextMenu"
    style={`left: ${posX}px; top: ${posY}px`}
>
    <div class="header menuItem">{$navSelection.length} photo{$navSelection.length == 1 ? "" : "s"} selected</div>
    {#if $currentAlbumStore.type == AlbumType.Album}
        {#if $navSelection.length == 1}
            <Button on:click={() => dispatch("coverPhotoClicked", {})}>
                <div class="cover menuItem">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 256 256"><rect width="256" height="256" fill="none"></rect><polyline points="200 176 127.993 136 56 176" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></polyline><path d="M200,224l-72.0074-40L56,224V40a8,8,0,0,1,8-8H192a8,8,0,0,1,8,8Z" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></path></svg>
                    <div>{$currentAlbumStore.coverPhoto?.id === $navSelection[0].id ? "Unset" : "Set"} as Cover Photo</div>
                </div>
            </Button>
        {/if}
        {#if $navSelection.length == 1}
            <Button on:click={() => dispatch("splitGroup", {})}>
                <div class="splitGroup menuItem">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 256 256"><rect width="256" height="256" fill="none"></rect><line x1="128" y1="40" x2="128" y2="216" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></line><line x1="88" y1="128" x2="12" y2="128" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></line><polyline points="44 96 12 128 44 160" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></polyline><line x1="168" y1="128" x2="244" y2="128" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></line><polyline points="212 160 244 128 212 96" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></polyline></svg>
                    <div>Split Group Here</div>
                </div>
            </Button>
        {:else}
            <Button on:click={() => dispatch("makeNewGroup", {})}>
                <div class="makeNewGroup menuItem">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 256 256"><rect width="256" height="256" fill="none"></rect><polyline points="168 167.993 216 167.993 216 39.993 88 39.993 88 87.993" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"></polyline><rect x="39.99902" y="87.99414" width="128" height="128" stroke-width="16" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" fill="none"></rect></svg>
                    <div>Make Separate Group</div>
                </div>
            </Button>
        {/if}
    {/if}
    <Button on:click={() => albumListShown = !albumListShown}>
        <div class="move menuItem">
            {#if !albumListShown}
                <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 256 256"><rect width="256" height="256" fill="none"></rect><polyline points="96 48 176 128 96 208" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></polyline></svg>
            {:else}
                <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 256 256"><rect width="256" height="256" fill="none"></rect><polyline points="208 96 128 176 48 96" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></polyline></svg>
            {/if}
            {#if $currentAlbumStore.type == AlbumType.Dynamic && $currentAlbumStore.slug == "all"}
                <div class="label">Add To</div>
            {:else}
                <div class="label">Move To</div>
            {/if}
        </div>
    </Button>
    {#if albumListShown}
        <div class="album menuItem"
            on:click={() => newAlbumPromptShowing = true}
        >
            <Button>
                <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 256 256"><rect width="256" height="256" fill="none"></rect><circle cx="128" cy="128" r="96" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></circle><line x1="88" y1="128" x2="168" y2="128" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></line><line x1="128" y1="88" x2="128" y2="168" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></line></svg>
                <div class="label">New Album…</div>
            </Button>
        </div>
        {#each albumList as album}
            {#if album.id != $currentAlbumStore.id}
                <Button on:click={(evt) => dispatch("move", {targetAlbumID: album.id})}>
                    <div class="album menuItem">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 256 256"><rect width="256" height="256" fill="none"></rect><rect x="32" y="48" width="192" height="160" rx="8" stroke-width="24" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" fill="none"></rect><path d="M32,167.99982l50.343-50.343a8,8,0,0,1,11.31371,0l44.68629,44.6863a8,8,0,0,0,11.31371,0l20.68629-20.6863a8,8,0,0,1,11.31371,0L223.99982,184" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></path><circle cx="156" cy="100" r="16"></circle></svg>
                        <div class="label">{album.title}</div>
                    </div>
                </Button>
            {/if}
        {/each}
    {/if}
    <Button on:click={() => dispatch("delete", {})}>
        <div class="delete menuItem">
            <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 256 256"><rect width="256" height="256" fill="none"></rect><line x1="215.99609" y1="60" x2="39.99609" y2="60.00005" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></line><line x1="104" y1="104" x2="104" y2="168" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></line><line x1="152" y1="104" x2="152" y2="168" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></line><path d="M199.99609,60.00005V208a8,8,0,0,1-8,8h-128a8,8,0,0,1-8-8v-148" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></path><path d="M168,60V36a16,16,0,0,0-16-16H104A16,16,0,0,0,88,36V60" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></path></svg>
            <div class="label">Delete</div>
        </div>
    </Button>
</div>

<style>
    .contextMenu {
        position: fixed;
        min-width: 150px;

        background-color: var(--ui-medium-color);
        border: 1px solid var(--ui-border-color);
        z-index: 1400;
        cursor: pointer;
        -webkit-touch-callout: none;
          -webkit-user-select: none;
              -ms-user-select: none;
                  user-select: none;
    }

    .contextMenu svg {
        width: 20px;
        margin-right: 10px
    }

    .menuItem {
        width: 100%;

        padding: 0 10px 0 5px;
        border-bottom: 1px solid black;

        display: flex;
        align-items: center;
        white-space: nowrap;
    }

    .menuItem .label {
        flex-grow: 2;
    }

    .menuItem:hover {
        background-color: var(--ui-light-color);
    }

    .menuItem.header {
        padding-top: 5px;
        padding-bottom: 5px;
        font-weight: bold;
        border-bottom: 3px solid black;
        background-color: var(--ui-dark-color);

        cursor: default;
    }

    .menuItem.delete {
        background-color: var(--warning-color-disabled);
    }

    .menuItem.delete:hover {
        background-color: var(--warning-color);
    }

    .menuItem.album {
        background-color: var(--ui-dark-color);;
        padding-left: 20px;
    }
    .menuItem.album:hover {
        background-color: var(--ui-light-color);;
    }
</style>
