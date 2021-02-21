<script lang="ts">
    import { createEventDispatcher } from "svelte";
    import { fade } from "svelte/transition";

    import { isLoggedInStore, siteData } from "../stores";
    import FileUploadList from "./FileUploadList.svelte";

    let fileList: File[];
    const dispatch = createEventDispatcher();


    let dragCount = 0;
    async function handleDrop(event: DragEvent) {
        dragCount = 0;
        let filteringList = [...event.dataTransfer.files];
        filteringList = filteringList.filter(isValidFile);
        if (filteringList.length == 0) {
            return;
        }
        fileList = filteringList;
    }

    function isValidFile(f: File) {
        if (!$siteData.formats.includes(f.type)) {
            return false;
        }
        if (f.size > $siteData.maxUploadBytes) {
            return false;
        }
        return true;
    }


    function onFinish() {
        dispatch("done", {numFiles: fileList.length});
        fileList = null;
    }

    function onDismiss() {
        fileList = null;
        dispatch("done", {numFiles: 0});
    }
</script>

<svelte:window
    on:dragover|preventDefault
    on:dragenter|preventDefault={() => ++dragCount }
    on:dragleave|preventDefault={() => --dragCount }
    on:drop|preventDefault={handleDrop}
/>
{#if (dragCount > 0 || fileList != null) && $isLoggedInStore}
    {#if fileList}
        <div class="uploadZone">
            <FileUploadList
                fileList={fileList}
                on:finished={onFinish}
                on:dismissed={onDismiss}
            />
        </div>
    {:else}
        <div class="uploadZone"
        >
            <div class="dragIndicator"
                in:fade={{duration: 100}}
                out:fade={{duration: 200}}
            >
                <div class="dragIndicatorOutline"></div>
            </div>
        </div>
    {/if}
{/if}

<style>
    .uploadZone {
        position: fixed;
        top: 0;
        right: 0;
        bottom: 0;
        left: 0;

        z-index: 1500;
        pointer-events: none;

        display: flex;
    }
    .dragIndicator {
        width: 100%;
        height: 100%;

        background-image: radial-gradient(ellipse, hsla(0,0%,0%,0) 50%, var(--main-text-color) 120%);
        opacity: 0.5;

        display: flex;
    }
    .dragIndicatorOutline {
        width: 95%;
        height: 95%;
        margin: auto;

        border: 10px dashed var(--main-text-color);
        border-radius: 30px;
    }
</style>
