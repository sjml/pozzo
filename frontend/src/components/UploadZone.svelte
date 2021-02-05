<script lang="ts">
    import { fade } from "svelte/transition";

    import { loginCredentialStore } from "../stores";
    import FileUploadList from "./FileUploadList.svelte";

    function dragEnter(_: DragEvent) {
        dragging = true;
    }

    function dragLeave(_: DragEvent) {
        dragging = false;
    }

    let fileList: File[];

    async function handleDrop(event: DragEvent) {
        dragging = false;
        let filteringList = [...event.dataTransfer.files];
        filteringList = filteringList.filter(f => f.type == "image/jpeg");
        fileList = filteringList;
    }

    let dragging: boolean = false;
</script>

{#if $loginCredentialStore.length > 0}
    {#if fileList}
        <div class="uploadZone">
            <FileUploadList fileList={fileList} on:finished={() => fileList = null} />
        </div>
    {:else}
        <div class="uploadZone"
            on:dragover|preventDefault
            on:dragenter|preventDefault={dragEnter}
            on:dragleave|preventDefault={dragLeave}
            on:drop|preventDefault={handleDrop}
        >
            {#if dragging }
                <div class="dragIndicator"
                    in:fade={{duration: 100}}
                    out:fade={{duration: 200}}
                >
                    <div class="dragIndicatorOutline"></div>
                </div>
            {/if}
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
    }
    .dragIndicator {
        background-color: rgb(121, 121, 121);
        opacity: 0.5;
        width: 100%;
        height: 100%;
        display: flex;
        pointer-events: none;
    }
    .dragIndicatorOutline {
        width: 95%;
        height: 95%;
        margin: auto;
        border: 10px dashed rgb(255, 255, 255);
        border-radius: 30px;
    }
</style>
