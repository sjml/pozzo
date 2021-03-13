<script context="module">
    import justifiedLayout from "justified-layout";
</script>

<script lang="ts">
    import { createEventDispatcher } from "svelte";
    import { Link } from "svelte-routing";

    import type { PhotoGroup } from "../pozzo.type";
    import { AlbumType } from "../pozzo.type";
    import { isLoggedInStore, currentAlbumStore } from "../stores";
    import { RunApi } from "../api";
    import LazyLoad from "./LazyLoad.svelte";
    import NavPhoto from "./NavPhoto.svelte";
    import NavCollection from "./NavCollection.svelte";
    import Markdown from "./Markdown.svelte";
    import Button from "./Button.svelte";

    const dispatch = createEventDispatcher();

    export let photoGroup: PhotoGroup;
    export let photoGroupIndex: number;

    let containerWidth: number;
    let layout = null;
    async function calculateLayout(pg: PhotoGroup, width: number) {
        if (!width || !pg) {
            return; // initial loads; don't worry yet
        }
        const aspects = pg.photos.map(p => p.aspect);
        layout = justifiedLayout(aspects, {
            targetRowHeight: 300,
            containerWidth: width,
            containerPadding: 10,
            widowLayoutStyle: "center",
        });
    }
    $: calculateLayout(photoGroup, containerWidth)

    let reordering = false;
    let editingMeta = false;
    let rawDescription: string;
    async function toggleEditingMeta() {
        if (!editingMeta) {
            rawDescription = photoGroup.description;
            editingMeta = true;
        }
        else {
            photoGroup.description = rawDescription;
            await updateMetaData();
            editingMeta = false;
        }
    }

    async function updateMetaData() {
        if (photoGroup.hasMap == null) {
            // probably just initial load
            return;
        }
        const res = await RunApi(`/group/edit/${photoGroup.id}`, {
            params: {
                hasMap: photoGroup.hasMap,
                description: photoGroup.description,
            },
            method: "POST",
            authorize: true
        });
        if (res.success) {
            dispatch("perusalChangeNeeded");
        }
        else {
            console.error(res);
        }
    }

    async function handlePhotoReorder(evt: CustomEvent) {
        const res = await RunApi(`/group/reorder/${photoGroup.id}`, {
            params: {newOrdering: evt.detail.newPhotos.map(ps => ps.id)},
            method: "POST",
            authorize: true
        });
        if (res.success) {
            photoGroup.photos = evt.detail.newPhotos;
            dispatch("perusalChangeNeeded");
        }
        else {
            console.error(res);
        }
    }

    async function handlePhotoDeletion(evt: CustomEvent) {
        const res = await RunApi("/photo/delete", {
            params: {photoIDs: evt.detail.deleted.map(p => p.id)},
            method: "POST",
            authorize: true
        });
        if (res.success) {
            photoGroup.photos = photoGroup.photos.filter(p => evt.detail.deleted.indexOf(p) < 0);
            dispatch("perusalChangeNeeded");
        }
        else {
            console.error(res);
        }
    }

    async function handlePhotoMove(evt: CustomEvent) {
        if ($currentAlbumStore.type == AlbumType.Dynamic) {
            const res = await RunApi(`/photo/copy`, {
                params: {
                    copies: evt.detail.moved.map(p => {
                        return {photoID: p.id, albumID: evt.detail.targetAlbumID}
                    })
                },
                method: "POST",
                authorize: true
            });
            if (res.success) {
                if ($currentAlbumStore.slug == "unsorted") {
                    photoGroup.photos = photoGroup.photos.filter(p => evt.detail.moved.indexOf(p) < 0);
                    dispatch("perusalChangeNeeded");
                }
            }
            else {
                console.error(res);
            }
        }
        else {
            const res = await RunApi(`/photo/move`, {
                params: {
                    photoIDs: evt.detail.moved.map(p => p.id),
                    fromGroupID: photoGroup.id,
                    toAlbumID: evt.detail.targetAlbumID
                },
                method: "POST",
                authorize: true
            });
            if (res.success) {
                photoGroup.photos = photoGroup.photos.filter(p => evt.detail.moved.indexOf(p) < 0);
                dispatch("perusalChangeNeeded");
            }
            else {
                console.error(res);
            }
        }
    }
</script>

<div class="photoGroup"
    bind:clientWidth={containerWidth}
>
    {#if $isLoggedInStore && $currentAlbumStore.type == AlbumType.Album}
        <div class="controls">
            <Button
                margin="0 0 0 10px"
                title={editingMeta ? "Commit Changes" : "Edit Description"}
                isToggled={editingMeta}
                on:click={toggleEditingMeta}
            >
                <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 256 256"><rect width="256" height="256" fill="none"></rect><path d="M92.68629,216H48a8,8,0,0,1-8-8V163.31371a8,8,0,0,1,2.34315-5.65686l120-120a8,8,0,0,1,11.3137,0l44.6863,44.6863a8,8,0,0,1,0,11.3137l-120,120A8,8,0,0,1,92.68629,216Z" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></path><line x1="136" y1="64" x2="192" y2="120" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></line><line x1="44" y1="156" x2="100" y2="212" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></line></svg>
            </Button>
            {#if photoGroup.photos.length > 0}
                <Button
                    margin="0 0 0 10px"
                    isToggled={photoGroup.hasMap}
                    title={`${photoGroup.hasMap ? "Hide" : "Show"} Map`}
                    on:click={() => {photoGroup.hasMap = !photoGroup.hasMap; updateMetaData();}}
                >
                    <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 256 256"><rect width="256" height="256" fill="none"></rect><polyline points="96 184 32 200 32 56 96 40" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></polyline><polygon points="160 216 96 184 96 40 160 72 160 216" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></polygon><polyline points="160 72 224 56 224 200 160 216" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></polyline></svg>
                </Button>
            {/if}

            <Button
                margin="0 0 0 30px"
                title="Move Up"
                isDisabled={photoGroupIndex == 0}
                on:click={() => dispatch("shiftGroup", {groupIdx: photoGroupIndex, group: photoGroup, movement: -1})}
            >
                <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 256 256"><rect width="256" height="256" fill="none"></rect><line x1="128" y1="216" x2="128" y2="40" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></line><polyline points="56 112 128 40 200 112" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></polyline></svg>
            </Button>
            <Button
                margin="0"
                title="Move Down"
                isDisabled={photoGroupIndex == $currentAlbumStore.photoGroups.length - 1}
                on:click={() => dispatch("shiftGroup", {groupIdx: photoGroupIndex, group: photoGroup, movement: 1})}
            >
                <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 256 256"><rect width="256" height="256" fill="none"></rect><line x1="128" y1="40" x2="128" y2="216" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></line><polyline points="56 144 128 216 200 144" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></polyline></svg>
            </Button>
            <div class="spacer"></div>
            <Button
                margin="0 10px 0 0"
                title="Merge with Above"
                isDisabled={photoGroupIndex == 0}
                on:click={() => dispatch("mergeUp", {groupIdx: photoGroupIndex, group: photoGroup})}
            >
                <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 256 256"><rect width="256" height="256" fill="none"></rect><polygon points="32 120 128 24 224 120 176 120 176 140 80 140 80 120 32 120" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></polygon><line x1="176" y1="212" x2="80" y2="212" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></line><line x1="176" y1="176" x2="80" y2="176" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></line></svg>
            </Button>
            <Button
                margin="0 0 0 10px"
                title="Delete Group"
                isDisabled={$currentAlbumStore.photoGroups.length == 1}
                on:click={() => dispatch("deleteGroup", {groupIdx: photoGroupIndex, group: photoGroup})}
            >
                <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 256 256"><rect width="256" height="256" fill="none"></rect><line x1="215.99609" y1="60" x2="39.99609" y2="60.00005" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></line><line x1="104" y1="104" x2="104" y2="168" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></line><line x1="152" y1="104" x2="152" y2="168" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></line><path d="M199.99609,60.00005V208a8,8,0,0,1-8,8h-128a8,8,0,0,1-8-8v-148" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></path><path d="M168,60V36a16,16,0,0,0-16-16H104A16,16,0,0,0,88,36V60" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></path></svg>
            </Button>
        </div>
    {/if}

    {#if photoGroup.hasMap && photoGroup.photos.length > 0}
        <div class="photoGroupMap">
            <LazyLoad loader={"PhotoMap"}
                photos={photoGroup.photos}
            />
        </div>
    {/if}

    {#if editingMeta}
        <textarea cols="500" rows="5" class="description editing" bind:value={rawDescription}></textarea>
    {:else if photoGroup.description.length > 0}
        <div class="description">
            <Markdown markdown={photoGroup.description} />
        </div>
    {/if}

    {#if $isLoggedInStore && $currentAlbumStore.type == AlbumType.Album && photoGroup.photos.length > 1}
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
        <LazyLoad loader={"EditableLayout"}
            photoList={photoGroup.photos}
            on:reordered={handlePhotoReorder}
        />
    {:else}
        {#if layout}
            <div class="photos"
                style={`height: ${layout?.containerHeight || 0}px;`}
            >
                {#if $isLoggedInStore}
                    <NavCollection
                        photos={photoGroup.photos}
                        on:splitGroup={(evt) => dispatch("splitGroup", Object.assign(evt.detail, {originGroup: photoGroup}))}
                        on:makeNewGroup={(evt) => dispatch("makeNewGroup", Object.assign(evt.detail, {originGroup: photoGroup}))}

                        on:deleted={handlePhotoDeletion}
                        on:moved={handlePhotoMove}

                        on:coverChanged
                    >
                        {#each photoGroup.photos as photo, pi}
                            <Link to={`/${$currentAlbumStore.type}/${$currentAlbumStore.slug}/${photo.id}`}>
                                <NavPhoto size="medium" photo={photo} layoutDims={layout.boxes[pi]} />
                            </Link>
                        {/each}
                    </NavCollection>
                {:else}
                    {#each photoGroup.photos as photo, pi}
                        <Link to={`/${$currentAlbumStore.type}/${$currentAlbumStore.slug}/${photo.id}`}>
                            <NavPhoto size="medium" photo={photo} layoutDims={layout.boxes[pi]} />
                        </Link>
                    {/each}
                {/if}
            </div>
        {/if}
    {/if}
</div>

<style>
    .photos {
        position: relative;
        height: 50px;
    }

    /*
    Firefox has trouble keeping up with container width
    changes unless it's a defined width (not in percents)
    so just bumping it back away from the scrollbar.
    */
    .photoGroup {
        position: relative;
        width: calc(100vw - 12px);
    }

    .description {
        max-width: 900px;
        margin: 0 auto 10px auto;

        font-size: 1.15em;
        line-height: 1.5;
    }

    .description :global(a) {
        text-decoration: underline;
    }

    .description.editing {
        display: block;
    }

    .controls {
        border-top: 1px solid var(--separator-color);
        padding: 10px;

        display: flex;
    }

    .spacer {
        flex-grow: 1;
    }

    .photoGroupMap {
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
        background-color: var(--edit-color);
    }
</style>
