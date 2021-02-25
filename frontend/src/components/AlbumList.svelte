<script lang="ts">
    import { navigateTo, Link } from "yrv";

    import justifiedLayout from "justified-layout";

    import type { Album, Photo } from "../pozzo.type";
    import { isLoggedInStore } from "../stores";
    import { RunApi } from "../api";
    import Button from "./Button.svelte";
    import NavCollection from "./NavCollection.svelte";
    import NavPhoto from "./NavPhoto.svelte";

    let albumList: Album[];
    let albumCovers: Photo[];


    async function getAlbumList(loggedIn: boolean) {
        const res = await RunApi("/album/list", {
            authorize: loggedIn
        });
        if (res.success) {
            albumList = res.data;
        }
        else {
            console.error(res);
        }
    }
    $: getAlbumList($isLoggedInStore)

    function assembleStubs(alist: Album[]) {
        if (!alist) {
            return;
        }
        albumCovers = albumList.map(a => {
            if (a.coverPhoto) {
                const pr = Object.assign({}, a.coverPhoto);
                pr.id = a.id;
                pr.title = a.title;
                return pr;
            }
            // annoying to spec out all this nonsense :-/
            return {
                id: a.id,
                title: a.title,
                aspect: 4.0 / 3.0,
                hash: "",
                uniq: "",
                blurHash: "",
                isVideo: false,
                uploadTimeStamp: 0,
                uploadedBy: 0,
                originalFilename: "",
                size: 0,
                width: 0,
                height: 0,
                tags: [],
                make: null,
                model: null,
                lens: null,
                mime: null,
                creationDate: null,
                subjectArea: null,
                aperture: null,
                iso: null,
                shutterSpeed: null,
                gpsLat: null,
                gpsLon: null,
                gpsAlt: null,
            };
        });
    }
    $: assembleStubs(albumList)


    let containerWidth: number;
    let layout = null;

    function calculateLayout(width: number, photos: Photo[]) {
        if (!width || !photos) {
            return; // initial loads; don't worry yet
        }

        const aspects = photos.map((acs) => {
            if (acs != null) {
                return acs.aspect;
            }
            return 4.0 / 3.0;
        });

        layout = justifiedLayout(aspects, {
            targetRowHeight: 300,
            containerWidth: width,
            containerPadding: 10,
            widowLayoutStyle: "center",
        });
    }
    $: if (albumList) calculateLayout(containerWidth, albumCovers)


    let addingNew = false;
    let reordering = false;

    function onUploadDone(evt: CustomEvent) {
        if (evt.detail.numFiles > 0) {
            navigateTo("/album/unsorted");
        }
    }

    async function handleAlbumReorder(evt: CustomEvent) {
        const res = await RunApi("/album/reorderList/", {
            params: {newOrdering: evt.detail.newStubs.map(ps => ps.id)},
            method: "POST",
            authorize: true
        });
        if (res.success) {
            albumCovers = evt.detail.newStubs;
        }
        else {
            console.error(res);
        }
    }
</script>

{#if $isLoggedInStore && !reordering}
    {#await import("./UploadZone.svelte") then {default: uploadZone}}
        <svelte:component this={uploadZone} on:done={onUploadDone} />
    {/await}
{/if}

{#if addingNew}
    {#await import("./NewAlbumPrompt.svelte") then {default: newAlbumPrompt}}
        <svelte:component this={newAlbumPrompt}
            on:dismissed={() => addingNew = false}
            on:done={() => {addingNew = false; getAlbumList($isLoggedInStore);}}
        />
    {/await}
{/if}

<div class="albumList">
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
            {#if albumList?.length > 1}
                <div class="reorderButton" class:toggled={reordering}>
                    <Button
                        margin="0 0 0 0"
                        on:click={() => {reordering = !reordering}}
                        title={`${reordering ? "Exit" : "Enter"} Edit Mode`}
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 256 256"><rect width="256" height="256" fill="none"></rect><polyline points="192 144 224 176 192 208" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></polyline><line x1="32" y1="176" x2="224" y2="176" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></line><polyline points="64 112 32 80 64 48" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></polyline><line x1="224.00006" y1="80" x2="32.00006" y2="80" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></line></svg>
                    </Button>
                </div>
            {/if}
        {/if}
    </div>


    {#if albumList}
        {#if reordering}
            {#await import("./EditableLayout.svelte") then {default: editableLayout}}
                <svelte:component this={editableLayout}
                    stubList={albumCovers}
                    on:reordered={handleAlbumReorder}
                />
            {/await}
        {:else}
        <div class="albumListDisplay"
            bind:clientWidth={containerWidth}
            style={`height: ${layout?.containerHeight || 0}px;`}
        >
            {#if albumList.length == 0}
                <div>(No albums on this site… yet.)</div>
            {/if}

            {#if layout}
                <NavCollection stubs={albumCovers}>
                {#each albumList as album, ai}
                    <Link href={`/album/${album.slug}`}>
                        <NavPhoto size="medium"
                            photo={albumCovers[ai]}
                            layoutDims={layout.boxes[ai]}
                            textOverlay={album.title}
                        />
                    </Link>
                {/each}
                </NavCollection>
            {/if}
        </div>
        {/if}
    {/if}
</div>

<style>
    .albumList {
        margin-top: 1em;
    }

    h2 {
        margin: 0 0 0 15px;
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

    .reorderButton {
        margin-left: 5px;
        max-width: 50px;

        padding: 5px;
    }

    .reorderButton.toggled {
        background-color: var(--edit-color);
    }
</style>
