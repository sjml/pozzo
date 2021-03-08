<script lang="ts">
    import { createEventDispatcher } from "svelte";

    import type { Photo } from "../pozzo.type";
    import { currentAlbumStore, currentPerusalStore, isLoggedInStore, navSelection } from "../stores";
    import { RunApi } from "../api";
    import LazyLoad from "./LazyLoad.svelte";
    import PhotoGroup from "./PhotoGroup.svelte";
    import Markdown from "./Markdown.svelte";
    import Button from "./Button.svelte";

    const dispatch = createEventDispatcher();

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
                coverPhoto: $currentAlbumStore.coverPhoto.id,
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

    // async function handlePhotoDeletion(evt: CustomEvent) {
    //     for (let p of $navSelection) {
    //         const delRes = await RunApi("/photo/delete", {
    //             authorize: true,
    //             method: "POST",
    //             params: {
    //                 photoID: p.id
    //             }
    //         });
    //         if (!delRes.success) {
    //             console.error("Could not delete photo", delRes);
    //             return;
    //         }
    //     }

    //     $currentAlbumStore.photos = evt.detail.newPhotos;
    // }

    // async function handlePhotoMove(evt:CustomEvent) {
    //     const res = await RunApi(`/photo/move`, {
    //         params: {
    //             photoIDs: $navSelection.map(ps => ps.id),
    //             fromAlbumID: $currentAlbumStore.id,
    //             toAlbumID: evt.detail.targetAlbumID
    //         },
    //         method: "POST",
    //         authorize: true
    //     });
    //     if (res.success) {
    //         $currentAlbumStore.photos = evt.detail.newPhotos;
    //     }
    //     else {
    //         console.error(res);
    //     }
    // }

    async function newGroup(fromGroupID: number, offshoots: Photo[]) {
        const res = await RunApi("/group/new", {
            params: {
                albumID: $currentAlbumStore.id,
                fromGroup: fromGroupID,
                photoIDs: offshoots.map(p => p.id)
            },
            method: "POST",
            authorize: true
        });
        if (res.success) {
            dispatch("structuralChange");
        }
        else {
            console.error(res);
        }
    }

    function handleSplitGroup(evt: CustomEvent) {
        // assume only one item in nav selection
        const splitPointID = $navSelection[0].id;
        const offshoots: Photo[] = [];
        evt.detail.originGroup.photos.forEach(p => {
            if (p.id == splitPointID) {
                offshoots.push(p);
            }
            else if (offshoots.length > 0) {
                offshoots.push(p);
            }
        });

        newGroup(evt.detail.originGroup.id, offshoots);
    }

    function handleMakeNewGroup(evt: CustomEvent) {
        newGroup(evt.detail.originGroup.id, $navSelection);
    }

    async function handleShiftGroup(evt: CustomEvent) {
        // intentionally creating new array instead of in-place splicing to trigger
        //   reactions to change
        const plucked = $currentAlbumStore.photoGroups.splice(evt.detail.groupIdx, 1)[0];
        $currentAlbumStore.photoGroups =
            $currentAlbumStore.photoGroups.slice(0, evt.detail.groupIdx + evt.detail.movement)
            .concat(plucked)
            .concat($currentAlbumStore.photoGroups.slice(evt.detail.groupIdx + evt.detail.movement));

        const res = await RunApi(`/album/reorderGroups/${$currentAlbumStore.id}`, {
            params: {
                newOrdering: $currentAlbumStore.photoGroups.map(pg => pg.id)
            },
            method: "POST",
            authorize: true
        });

        if (res.success) {
            // no-op
        }
        else {
            console.error(res);
        }
    }

    async function handleMergeUp(evt: CustomEvent) {
        if (evt.detail.groupIdx == 0) {
            console.error("Invalid merge index.");
            return;
        }
        const absorbedGroup = $currentAlbumStore.photoGroups[evt.detail.groupIdx];
        const absorbingGroup = $currentAlbumStore.photoGroups[evt.detail.groupIdx-1];
        const res = await RunApi(`/group/merge/${absorbedGroup.id}`, {
            params: {
                into: absorbingGroup.id,
            },
            method: "POST",
            authorize: true
        });
        if (res.success) {
            dispatch("structuralChange");
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
        <LazyLoad loader={"UploadZone"}
            on:done={() => dispatch("uploaded")}
        />
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
            {#if $currentPerusalStore.photoSet.length > 0}
                <Button
                    margin="0 0 0 10px"
                    isToggled={$currentAlbumStore.showMap}
                    title={`${$currentAlbumStore.showMap ? "Hide" : "Show"} Map`}
                    on:click={() => {$currentAlbumStore.showMap = !$currentAlbumStore.showMap; updateMetaData();}}
                >
                    <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 256 256"><rect width="256" height="256" fill="none"></rect><polyline points="96 184 32 200 32 56 96 40" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></polyline><polygon points="160 216 96 184 96 40 160 72 160 216" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></polygon><polyline points="160 72 224 56 224 200 160 216" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></polyline></svg>
                </Button>
            {/if}
            <div class="spacer"></div>
            <!-- delete album button goes here -->
        {/if}
    </div>

    {#if editingDescTitle}
        <textarea cols="500" rows="10" class="description editing" bind:value={rawDescription}></textarea>
    {:else}
        <div class="description">
            <Markdown markdown={$currentAlbumStore.description} />
        </div>
    {/if}

    {#if $currentAlbumStore.showMap && $currentPerusalStore.photoSet.length > 0}
        <div class="albumMap">
            <LazyLoad loader={"PhotoMap"}
                photos={$currentPerusalStore.photoSet}
            />
        </div>
    {/if}


    {#each $currentAlbumStore.photoGroups as pg, pgi}
        <PhotoGroup photoGroup={pg} photoGroupIndex={pgi}
            on:splitGroup={handleSplitGroup}
            on:makeNewGroup={handleMakeNewGroup}
            on:shiftGroup={handleShiftGroup}
            on:mergeUp={handleMergeUp}
        />
    {/each}

{/if}
</div>

<style>
    .album {
        width: 100%;
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

    @media only screen and (max-device-width: 480px) {
        h2 {
            font-size: 2em;
        }
    }

    .editing {
        background-color: var(--main-text-color);
        color: var(--main-background-color);
    }

    svg {
        width: var(--button-size);
        height: var(--button-size);
    }

    .albumMap {
        height: 400px;
        width: 100%;
        margin: 10px 0px;
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
    }
</style>
