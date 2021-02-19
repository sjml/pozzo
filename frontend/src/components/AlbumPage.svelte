<script lang="ts">
    import { onDestroy } from "svelte";
    import { router } from "tinro";

    import justifiedLayout from "justified-layout";

    import type { Album } from "../pozzo.type";
    import { currentAlbumStore, isLoggedInStore, navSelection } from "../stores";
    import { RunApi } from "../api";
    import NavPhoto from "./NavPhoto.svelte";
    import NavCollection from "./NavCollection.svelte";
    import EditableLayout from "./EditableLayout.svelte";
    import Markdown from "./Markdown.svelte";
    import Button from "./Button.svelte";
    import PhotoMap from "./PhotoMap.svelte";
    import UploadZone from "./UploadZone.svelte";


    let containerWidth: number;
    let layout = null;
    function calculateLayout(a: Album, width: number) {
        if (!width || !$currentAlbumStore) {
            return; // initial loads; don't worry yet
        }
        const aspects = a.photos.map(p => p.aspect);
        layout = justifiedLayout(aspects, {
            targetRowHeight: 300,
            containerWidth: width,
            containerPadding: 10,
            widowLayoutStyle: "center",
        });
    }
    $: calculateLayout($currentAlbumStore, containerWidth)


    let editingDescTitle = false;
    let rawTitle: string;
    let rawDescription: string;
    let reordering = false;
    async function toggleEditingTitleDesc() {
        if (!editingDescTitle) {
            rawTitle = $currentAlbumStore.title;
            rawDescription = $currentAlbumStore.description;
            editingDescTitle = true;
        }
        else {
            $currentAlbumStore.title = rawTitle;
            $currentAlbumStore.description = rawDescription;
            await updateMetaData();
            editingDescTitle = false;
        }
    }

    async function updateMetaData() {
        console.log("metadata update call");
        if ($currentAlbumStore.showMap == null || $currentAlbumStore.isPrivate == null) {
            // probably just initial load
            return;
        }
        const res = await RunApi(`/album/edit/${$currentAlbumStore.id}`, {
            params: {
                showMap: $currentAlbumStore.showMap,
                isPrivate: $currentAlbumStore.isPrivate,
                description: $currentAlbumStore.description,
                title: $currentAlbumStore.title,
            },
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

    async function handlePhotoDeletion(evt: CustomEvent) {
        for (let p of $navSelection) {
            const delRes = await RunApi("/photo/delete", {
                authorize: true,
                method: "POST",
                params: {
                    photoID: p.id
                }
            });
            if (!delRes.success) {
                console.error("Could not delete photo", delRes);
                return;
            }
        }

        $currentAlbumStore.photos = evt.detail.newStubs;
    }

    async function handlePhotoMove(evt:CustomEvent) {
        const res = await RunApi(`/photo/move`, {
            params: {
                photoIDs: $navSelection.map(ps => ps.id),
                fromAlbumID: $currentAlbumStore.id,
                toAlbumID: evt.detail.targetAlbumID
            },
            method: "POST",
            authorize: true
        });
        if (res.success) {
            $currentAlbumStore.photos = evt.detail.newStubs;
        }
        else {
            console.error(res);
        }
    }

    async function handlePhotoReorder(evt: CustomEvent) {
        const res = await RunApi(`/album/reorder/${$currentAlbumStore.id}`, {
            params: {newOrdering: evt.detail.newStubs.map(ps => ps.id)},
            method: "POST",
            authorize: true
        });
        if (res.success) {
            $currentAlbumStore.photos = evt.detail.newStubs;
        }
        else {
            console.error(res);
        }
    }
</script>

<div class="album">
{#if $currentAlbumStore == null}
    <div>Loading…</div>
{:else}
    {#if $isLoggedInStore && !reordering}
        <UploadZone on:done={() => router.goto(`/album/${$currentAlbumStore.slug}`) } />
    {/if}

    <div class="titleRow">
        {#if editingDescTitle}
            <h2 contenteditable="true" class="editing" bind:innerHTML={rawTitle}></h2>
        {:else}
            <h2>{$currentAlbumStore.title}</h2>
        {/if}
        {#if $isLoggedInStore}
            <Button
                margin="0 0 0 10px"
                title={editingDescTitle ? "Commit Changes" : "Edit Title and Description"}
                isToggled={editingDescTitle}
                on:click={toggleEditingTitleDesc}
            >
                <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 256 256"><rect width="256" height="256" fill="none"></rect><path d="M92.68629,216H48a8,8,0,0,1-8-8V163.31371a8,8,0,0,1,2.34315-5.65686l120-120a8,8,0,0,1,11.3137,0l44.6863,44.6863a8,8,0,0,1,0,11.3137l-120,120A8,8,0,0,1,92.68629,216Z" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></path><line x1="136" y1="64" x2="192" y2="120" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></line><line x1="44" y1="156" x2="100" y2="212" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></line></svg>
            </Button>
            <div class="spacer"></div>
            <Button
                margin="0 0 0 10px"
                title={$currentAlbumStore.isPrivate ? "Make Public" : "Make Private"}
                on:click={() => {$currentAlbumStore.isPrivate = !$currentAlbumStore.isPrivate; updateMetaData();}}
            >
                {#if !$currentAlbumStore.isPrivate}
                    <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 256 256"><rect width="256" height="256" fill="none"></rect><path d="M128,55.99219C48,55.99219,16,128,16,128s32,71.99219,112,71.99219S240,128,240,128,208,55.99219,128,55.99219Z" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></path><circle cx="128" cy="128" r="32" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></circle></svg>
                {:else}
                    <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 256 256"><rect width="256" height="256" fill="none"></rect><line x1="201.14971" y1="127.30467" x2="223.95961" y2="166.81257" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></line><line x1="154.18201" y1="149.26298" x2="161.29573" y2="189.60689" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></line><line x1="101.72972" y1="149.24366" x2="94.61483" y2="189.59423" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></line><line x1="54.80859" y1="127.27241" x2="31.88882" y2="166.97062" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></line><path d="M31.99943,104.87509C48.81193,125.68556,79.63353,152,128,152c48.36629,0,79.18784-26.31424,96.00039-47.12468" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></path></svg>
                {/if}
            </Button>
            {#if $currentAlbumStore.photos.length > 0}
                <Button
                    margin="0 0 0 10px"
                    isToggled={$currentAlbumStore.showMap}
                    title={`${$currentAlbumStore.showMap ? "Hide" : "Show"} Map`}
                    on:click={() => {$currentAlbumStore.showMap = !$currentAlbumStore.showMap; updateMetaData();}}
                >
                    <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 256 256"><rect width="256" height="256" fill="none"></rect><polyline points="96 184 32 200 32 56 96 40" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></polyline><polygon points="160 216 96 184 96 40 160 72 160 216" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></polygon><polyline points="160 72 224 56 224 200 160 216" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></polyline></svg>
                </Button>
            {/if}
        {/if}
    </div>

    {#if editingDescTitle}
        <textarea cols="500" rows="10" class="description editing" bind:value={rawDescription}></textarea>
    {:else}
        <div class="description"
        >
            <Markdown markdown={$currentAlbumStore.description} />
        </div>
    {/if}

    {#if $currentAlbumStore.showMap && $currentAlbumStore.photos.length > 0}
        <div class="albumMap">
            <PhotoMap photoIDs={$currentAlbumStore.photos.map(p => p.id)} />
        </div>
    {/if}

    {#if $isLoggedInStore && $currentAlbumStore.photos.length > 1}
        <div class="reorderButton" class:toggled={reordering}>
            <Button
                margin="0 0 0 10px"
                on:click={() => {reordering = !reordering}}
                title={`${reordering ? "Exit" : "Enter"} Reorder Mode`}
            >
                <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 256 256"><rect width="256" height="256" fill="none"></rect><polyline points="192 144 224 176 192 208" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></polyline><line x1="32" y1="176" x2="224" y2="176" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></line><polyline points="64 112 32 80 64 48" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></polyline><line x1="224.00006" y1="80" x2="32.00006" y2="80" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></line></svg>
            </Button>
        </div>
    {/if}

    {#if reordering}
        <EditableLayout
            stubList={$currentAlbumStore.photos}
            on:reordered={handlePhotoReorder}
        />
    {:else}
        <div class="albumPhotos"
            bind:clientWidth={containerWidth}
            style={`height: ${layout?.containerHeight || 0}px;`}
        >

        {#if $currentAlbumStore.photos.length == 0}
            <div>(No photos in this album… yet.)</div>
        {/if}

        {#if layout}
            <NavCollection stubs={$currentAlbumStore.photos}
                on:deleted={handlePhotoDeletion}
                on:moved={handlePhotoMove}
            >
            {#each $currentAlbumStore.photos as pstub, pi}
                <a href="/album/{$currentAlbumStore.slug}/{pstub.id}">
                    <NavPhoto size="medium" stub={pstub} layoutDims={layout.boxes[pi]} />
                </a>
            {/each}
            </NavCollection>
        {/if}
        </div>
    {/if}
{/if}
</div>

<style>
    .album {
        width: 100%;
    }

    .albumPhotos {
        position: relative;
        height: 50px;
        margin-left: auto;
        margin-right: auto;
    }

    .titleRow {
        margin-right: 30px;

        display: flex;
        align-items: baseline;
    }

    .spacer {
        flex-grow: 1;
    }

    h2 {
        margin-left: 20px;

        font-size: 3em;
        padding: 5px;
    }

    h2.editing {
        background-color: white;
        color: black;
    }

    svg {
        width: 30px;
        height: 30px;
    }

    .albumMap {
        height: 400px;
        width: 100%;
        margin: 10px 0px;
    }

    .reorderButton {
        margin-left: 30px;
        max-width: 50px;

        padding: 10px 0 5px 0;
    }

    .reorderButton.toggled {
        background-color: rgb(101, 101, 252);
    }

    .description {
        max-width: 900px;
        margin: 0 auto 40px auto;

        font-size: 1.15em;
        line-height: 1.5;

        padding: 5px 20px;
    }

    .description :global(a) {
        text-decoration: underline;
    }

    .description.editing {
        display: block;

        background-color: white;
        color: black;
    }
</style>
