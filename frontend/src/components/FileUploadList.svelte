<script lang="ts">
    import { createEventDispatcher, onMount, onDestroy, tick } from "svelte";

    import type { FileUploadStatus } from "../pozzo.type";
    import { frontendStateStore } from "../stores";
    import FileUploader from "./FileUploader.svelte";
    import Button from "./Button.svelte";

    const dispatch = createEventDispatcher();
    const MAX_UPLOADS = 4;

    export let fileList: File[];

    onMount(() => {
        $frontendStateStore.userStoppedUploadScroll = false;

        let targetAlbumID = null;
        let offset = 1;
        if ($frontendStateStore.currentAlbum != null) {
            targetAlbumID = $frontendStateStore.currentAlbum.id;
            const existingIndices = $frontendStateStore.currentAlbum.photos.map((p) => p.ordering);
            if (existingIndices.length == 0) {
                offset = 1;
            }
            else {
                offset = Math.max(...existingIndices) + 1;
            }
        }

        uploadStatuses = fileList.map((f, i) => {
            return {
                file: f,
                status: 0,
                index: i+offset,
                targetAlbum: targetAlbumID
            };
        });
    });

    onDestroy(() => {
        window.removeEventListener("beforeunload", befUn);
    });


    let uploadStatuses: FileUploadStatus[];

    let uploadStackPointer = 0;
    let currentUploadCount = 0;
    function queueUploads() {
        while (currentUploadCount < MAX_UPLOADS) {
            if (uploadStackPointer >= uploadStatuses.length) {
                break;
            }
            uploadStatuses[uploadStackPointer].startUploadCallback();
            uploadStackPointer += 1;
            currentUploadCount += 1;
        }
    }

    function fileUploadDone() {
        currentUploadCount -= 1;
        if (currentUploadCount == 0 && uploadStackPointer >= uploadStatuses.length) {
            queueIsEmpty();
        }
        else {
            queueUploads();
        }
    }

    let failedUploadCount = 0;
    function fileUploadFailed() {
        failedUploadCount += 1;
        fileUploadDone();
    }

    function queueIsEmpty() {
        if (failedUploadCount == 0) {
            dispatch("finished");
            return;
        }
        uploadStatuses = uploadStatuses.filter(us => us.status == 3);
        awaitingConfirmation = true;
    }


    function befUn(event: BeforeUnloadEvent) {
        event.preventDefault();
        event.returnValue = "";
        return "...";
    }

    let awaitingConfirmation = true;
    async function start() {
        window.addEventListener("beforeunload", befUn);
        failedUploadCount = 0;
        uploadStackPointer = 0;
        currentUploadCount = 0;
        awaitingConfirmation = false;
        for (let i=0; i < uploadStatuses.length; i++) {
            uploadStatuses[i].status = 0;
        }

        await tick();
        await new Promise(p => setTimeout(p, 400));
        $frontendStateStore.userStoppedUploadScroll = false;
        queueUploads();
    }
</script>


<div class="fileUploadList" on:click={() => $frontendStateStore.userStoppedUploadScroll = true}>
    {#if awaitingConfirmation}
        <div class="confirm">
            {#if failedUploadCount > 0}
                {(failedUploadCount == 1) ? "This image " : "These images "}did not upload
                successfully. Do you want to try again?
            {:else}
                Upload {fileList.length} image{(fileList.length == 1) ? "" : "s"}?
            {/if}
            <div class="yesno">
                <Button
                    title="Upload"
                    isBig={true}
                    margin="0 30px"
                    on:click={start}
                >
                    <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 256 256"><rect width="256" height="256" fill="none"></rect><circle cx="128" cy="128" r="96" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></circle><polyline points="94.059 121.941 128 88 161.941 121.941" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></polyline><line x1="128" y1="168" x2="128" y2="88" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></line></svg>
                    Upload
                </Button>
                <Button
                    title="Cancel"
                    isBig={true}
                    margin="0 30px"
                    on:click={() => dispatch("dismissed")}
                >
                    <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 256 256"><rect width="256" height="256" fill="none"></rect><circle cx="128" cy="128" r="96" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></circle><line x1="160" y1="96" x2="96" y2="160" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></line><line x1="160" y1="160" x2="96" y2="96" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></line></svg>
                    Cancel
                </Button>
            </div>
        </div>
    {/if}

    {#if uploadStatuses}
        {#each uploadStatuses as uploadStatus}
            <FileUploader
                uploadStatus={uploadStatus}
                on:fileUploadDone={fileUploadDone}
                on:fileUploadFailed={fileUploadFailed}
            />
        {/each}
    {/if}
</div>


<style>
    .fileUploadList {
        width: 450px;
        min-width: 215px;
        max-height: 85vh;
        margin: auto;

        border: 1px solid rgb(119, 119, 119);
        border-radius: 6px;
        overflow-y: scroll;
        pointer-events: all;
    }

    .confirm {
        padding: 10px;
        font-size: x-large;
        text-align: center;
        background-color: rgb(66, 66, 66);
    }

    .yesno {
        margin: 10px 0px;

        font-size: large;

        display: flex;
        justify-content: center;
    }
</style>
