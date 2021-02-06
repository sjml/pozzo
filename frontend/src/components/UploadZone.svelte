<script lang="ts">
    import { fade } from "svelte/transition";

    import { loginCredentialStore } from "../stores";
    import FileUploadList from "./FileUploadList.svelte";

    let fileList: File[];

    async function handleDrop(event: DragEvent) {
        let filteringList = [...event.dataTransfer.files];
        filteringList = filteringList.filter(f => f.type == "image/jpeg");
        fileList = filteringList;
        dragCount = 0;
    }

    let dragCount = 0;
</script>

<svelte:window
    on:dragover|preventDefault
    on:dragenter|preventDefault={() => ++dragCount }
    on:dragleave|preventDefault={() => --dragCount }
    on:drop|preventDefault={handleDrop}
/>
{#if (dragCount > 0 || fileList != null) && $loginCredentialStore.length > 0}
    {#if fileList}
        <div class="uploadZone">
            <FileUploadList fileList={fileList} on:finished={() => fileList = null} />
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
        z-index: 200;
        display: flex;
        pointer-events: none;
    }
    .dragIndicator {
        background-image: radial-gradient(ellipse, rgba(0,0,0,0) 50%, rgb(121, 121, 121) 120%);
        opacity: 0.5;
        width: 100%;
        height: 100%;
        display: flex;
    }
    .dragIndicatorOutline {
        width: 95%;
        height: 95%;
        margin: auto;
        border: 10px dashed rgb(255, 255, 255);
        border-radius: 30px;
    }
</style>
