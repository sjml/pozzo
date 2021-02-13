<script lang="ts">
    import { onMount } from "svelte";
    import { flip } from "svelte/animate";
    import { Link, navigate } from "svelte-routing";

    import justifiedLayout from "justified-layout";
    import { dndzone } from "svelte-dnd-action";

    import type { Album } from "../pozzo.type";
    import { isLoggedInStore } from "../stores";
    import { GetImgPath } from "../util";
    import { RunApi } from "../api";
    import NewAlbumPrompt from "./NewAlbumPrompt.svelte";
    import UploadZone from "./UploadZone.svelte";
    import Button from "./Button.svelte";


    onMount(() => {
        getAlbumList(null);
    });


    let albumList: Album[];
    let addingNew = false;
    async function getAlbumList(_) {
        const res = await RunApi("/album/list", {authorize: true});
        if (res.success) {
            albumList = res.data;
        }
        else {
            console.error(res);
        }
    }

    async function reorderAlbumList(newOrder: number[]) {
        const res = await RunApi("/album/reorderList", {
            params: {newOrdering: newOrder},
            method: "POST",
            authorize: true
        });
        if (res.success) {
            // no-op; frontend already shows backend's reality
        }
        else {
            console.error(res);
        }
    }

    $: getAlbumList($isLoggedInStore)


    function onUploadDone(evt: CustomEvent) {
        if (evt.detail.numFiles > 0) {
            navigate("/Unsorted");
        }
    }


    let containerWidth: number;
    let layout = null;

    function calculateLayout(width: number) {
        if (!width || !albumList) {
            return; // initial loads; don't worry yet
        }

        const aspects = albumList.map(a => {
            if (a.coverPhoto == -1) {
                return 4.0 / 3.0;
            }
            return a.coverAspect;
        });

        layout = justifiedLayout(aspects, {
            targetRowHeight: 300,
            containerWidth: width,
            containerPadding: 10,
            widowLayoutStyle: "center",
        });
    }

    $: if (albumList) calculateLayout(containerWidth)


    let editing: boolean = false;

    function dragAndDropConsider(e) {
        albumList = e.detail.items;
    }

    function dragAndDropFinalize(e) {
        albumList = e.detail.items;
        reorderAlbumList(albumList.map(a => a.id));
    }
</script>

{#if $isLoggedInStore && !editing}
    <UploadZone on:done={onUploadDone} />
{/if}

<div class="albumList">
    {#if addingNew}
        <NewAlbumPrompt
            on:dismissed={() => addingNew = false}
            on:done={() => {addingNew = false; getAlbumList(null);}}
        />
    {/if}

    <div class="header">
        {#if $isLoggedInStore}
            <h2>Albums</h2>
            <Button
                title="Add Album"
                margin="0 0 0 10px"
                on:click={() => addingNew = true}
            >
                <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 256 256"><rect width="256" height="256" fill="none"></rect><circle cx="128" cy="128" r="96" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></circle><line x1="88" y1="128" x2="168" y2="128" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></line><line x1="128" y1="88" x2="128" y2="168" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></line></svg>
            </Button>
        {/if}
    </div>

    {#if $isLoggedInStore && albumList?.length > 0}
        <div class="reorderButton" class:toggled={editing}>
            <Button
                margin="0 0 0 10px"
                on:click={() => {editing = !editing}}
                title={`${editing ? "Exit" : "Enter"} Edit Mode`}
            >
                <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 256 256"><rect width="256" height="256" fill="none"></rect><polyline points="192 144 224 176 192 208" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></polyline><line x1="32" y1="176" x2="224" y2="176" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></line><polyline points="64 112 32 80 64 48" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></polyline><line x1="224.00006" y1="80" x2="32.00006" y2="80" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></line></svg>
            </Button>
        </div>
    {/if}
    <div class="albumListDisplay"
        bind:clientWidth={containerWidth}
        style={`height: ${layout?.containerHeight || 0}px;`}
    >
        {#if editing && albumList}
            <!--
                 my EditableLayout is not as
                 generalizable as I'd hoped. :-/
             -->
             <div class="editableLayout"
                use:dndzone={{
                    items: albumList,
                    flipDurationMs: 300
                }}
                on:consider={dragAndDropConsider}
                on:finalize={dragAndDropFinalize}
             >
                {#each albumList as album (album.id)}
                    <div class="editableAlbum"
                        animate:flip={{duration: 300}}
                    >
                        {#if album.coverPhoto >= 0}
                            <img
                                src={GetImgPath("medium", album.coverHash, album.coverUniq)} alt={album.title}
                            />
                        {/if}
                        <div class="albumTitle">
                            {album.title}
                        </div>
                    </div>
                {/each}
             </div>
        {:else if albumList && layout}
            {#each albumList as album, ai}
                <Link to={`/${album.slug}`}>
                    <div class="navAlbum"
                        style={`top: ${layout.boxes[ai].top}px; left: ${layout.boxes[ai].left}px; width: ${layout.boxes[ai].width}px; height: ${layout.boxes[ai].height}px;`}
                    >
                        <div class="albumTitle">
                            {album.title}
                        </div>
                        {#if album.coverPhoto >= 0}
                            <img
                                src={GetImgPath("medium", album.coverHash, album.coverUniq)} alt={album.title}
                                width="{layout.boxes[ai].width}px" height="{layout.boxes[ai].height}px"
                            />
                        {/if}
                    </div>
                </Link>
            {/each}
        {/if}
    </div>
</div>

<style>
    .albumList {
        margin-top: 1em;

        padding-left: 30px;
    }

    h2 {
        margin: 0;
    }

    .header {
        display: flex;
        align-items: center;
    }

    .albumListDisplay {
        position: relative;
        max-width: 95%;
        height: 50px;
        margin-left: auto;
        margin-right: auto;
    }

    .navAlbum {
        position: absolute;

        background-color: black;

        display: flex;
    }

    .navAlbum .albumTitle {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);

        text-align: center;
        font-size: 3.5em;
        font-weight: 600;
        text-shadow: 0px 0px 10px black;
    }

    .reorderButton {
        margin-left: 30px;
        max-width: 50px;

        padding: 10px 0 5px 0;
    }

    .reorderButton.toggled {
        background-color: rgb(101, 101, 252);
    }

    .editableLayout {
        background-color: rgb(101, 101, 252);
        -webkit-touch-callout: none;
          -webkit-user-select: none;
              -ms-user-select: none;
                  user-select: none;

        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: center;
    }

    .editableAlbum {
        position: relative;
        width: 300px;
        height: 300px;
        margin: 10px;

        background-color: black;

        display: flex;
    }

    .editableAlbum .albumTitle {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);

        text-align: center;
        font-size: 3.5em;
        font-weight: 600;
        text-shadow: 0px 0px 10px black;
    }

    .editableAlbum img {
        max-width: 300px;
        max-height: 300px;
        margin: auto;
    }
</style>
