<script lang="ts">
    import { router } from "tinro";

    import justifiedLayout from "justified-layout";

    import type { Album, PhotoStub } from "../pozzo.type";
    import { isLoggedInStore } from "../stores";
    import { RunApi } from "../api";
    import Button from "./Button.svelte";
    import UploadZone from "./UploadZone.svelte";
    import NewAlbumPrompt from "./NewAlbumPrompt.svelte";
    import NavCollection from "./NavCollection.svelte";
    import NavPhoto from "./NavPhoto.svelte";
    import EditableLayout from "./EditableLayout.svelte";


    let albumList: Album[];
    let albumCoverStubs: PhotoStub[];


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
        albumCoverStubs = albumList.map((a) => ({
            id: a.id,
            title: a.title,
            hash: a.coverHash,
            uniq: a.coverUniq,
            blurHash: a.coverBlurHash,
            aspect: a.coverAspect || (4.0 / 3.0),
        }));
    }
    $: assembleStubs(albumList)


    let containerWidth: number;
    let layout = null;

    function calculateLayout(width: number, stubs: PhotoStub[]) {
        if (!width || !stubs) {
            return; // initial loads; don't worry yet
        }

        const aspects = stubs.map((acs) => acs.aspect);

        layout = justifiedLayout(aspects, {
            targetRowHeight: 300,
            containerWidth: width,
            containerPadding: 10,
            widowLayoutStyle: "center",
        });
    }
    $: if (albumList) calculateLayout(containerWidth, albumCoverStubs)


    let addingNew = false;
    let reordering = false;

    function onUploadDone(evt: CustomEvent) {
        if (evt.detail.numFiles > 0) {
            router.goto("/album/unsorted");
        }
    }

    async function handleAlbumReorder(evt: CustomEvent) {
        const res = await RunApi("/album/reorderList/", {
            params: {newOrdering: evt.detail.newStubs.map(ps => ps.id)},
            method: "POST",
            authorize: true
        });
        if (res.success) {
            albumCoverStubs = evt.detail.newStubs;
        }
        else {
            console.error(res);
        }
    }
</script>

{#if $isLoggedInStore && !reordering}
    <UploadZone on:done={onUploadDone} />
{/if}

{#if addingNew}
    <NewAlbumPrompt
        on:dismissed={() => addingNew = false}
        on:done={() => {addingNew = false; getAlbumList($isLoggedInStore);}}
    />
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
            <EditableLayout
                stubList={albumCoverStubs}
                on:reordered={handleAlbumReorder}
            />
        {:else}
        <div class="albumListDisplay"
            bind:clientWidth={containerWidth}
            style={`height: ${layout?.containerHeight || 0}px;`}
        >
            {#if albumList.length == 0}
                <div>(No albums on this site… yet.)</div>
            {/if}

            {#if layout}
                <NavCollection stubs={albumCoverStubs}>
                {#each albumList as album, ai}
                    <a href={`/album/${album.slug}`}>
                        <NavPhoto size="medium"
                            stub={albumCoverStubs[ai]}
                            layoutDims={layout.boxes[ai]}
                            textOverlay={album.title}
                        />
                    </a>
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

    .reorderButton {
        margin-left: 5px;
        max-width: 50px;

        padding: 5px;
    }

    .reorderButton.toggled {
        background-color: rgb(101, 101, 252);
    }
</style>
