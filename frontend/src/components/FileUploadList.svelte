<script lang="ts">
    import { createEventDispatcher, onMount, onDestroy, tick } from "svelte";


    import { userStoppedUploadScroll } from "../stores";
    import type { FileUploadStatus } from "../pozzo.type";
    import FileUploader from "./FileUploader.svelte";

    export let fileList: File[];

    let uploadStatuses: FileUploadStatus[];

    const MAX_UPLOADS = 4;
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

    let awaitingConfirmation = true;
    onMount(async () => {
        $userStoppedUploadScroll = false;
        uploadStatuses = fileList.map((f) => {
            return {file: f, status: 0};
        });
    });

    onDestroy(() => {
        window.removeEventListener("beforeunload", befUn);
    });

    function befUn(event: BeforeUnloadEvent) {
        event.preventDefault();
        event.returnValue = "";
        return "...";
    }

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
        $userStoppedUploadScroll = false;
        queueUploads();
    }

    const dispatch = createEventDispatcher();

</script>


<div class="fileUploadList" on:click={() => $userStoppedUploadScroll = true}>
    {#if awaitingConfirmation}
        <div class="confirm">
            {#if failedUploadCount > 0}
                {(failedUploadCount == 1) ? "This image " : "These images "}did not upload
                successfully. Do you want to try again?
            {:else}
                Upload {fileList.length} image{(fileList.length == 1) ? "" : "s"}?
            {/if}
            <div class="yesno">
                <div class="yes"
                    on:click={start}
                >
                    <svg
                        class="w-6 h-6"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M9 11l3-3m0 0l3 3m-3-3v8m0-13a9 9 0 110 18 9 9 0 010-18z"
                        />
                    </svg>
                    Upload
                </div>
                <div class="no"
                    on:click={() => dispatch("finished")}
                >
                    <svg
                        class="w-6 h-6"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"
                        />
                    </svg>

                    Cancel
                </div>
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
        border: 1px solid rgb(119, 119, 119);
        border-radius: 6px;

        width: 450px;
        min-width: 215px;
        max-height: 85vh;
        overflow-y: scroll;
        margin: auto;
    }

    .confirm {
        font-size: x-large;
        padding: 10px;
        text-align: center;
        background-color: rgb(66, 66, 66);
    }

    .yesno {
        font-size: large;
        display: flex;
        justify-content: center;
        margin: 10px 0px;
    }
    .yes, .no {
        margin-left: 30px;
        margin-right: 30px;
        cursor: pointer;
        display: flex;
        flex-direction: column;
        align-items: center;
    }
    .yesno svg {
        width: 40px;
    }
</style>
