<script lang="ts">
    import { createEventDispatcher, onMount } from "svelte";

    import type { FileUploadStatus } from "../pozzo.type";
    import { frontendStateStore } from "../stores";
    import { UploadFile } from "../api";
    import { HumanBytes } from "../util";


    const dispatch = createEventDispatcher();

    export let uploadStatus: FileUploadStatus;


    let previewSrc = null;
    let previewAlt = "";
    let progressBar: HTMLProgressElement;
    onMount(async () => {
        uploadStatus.startUploadCallback = () => startUpload();
        progressBar.value = 0;

        const fr = new FileReader();
        fr.readAsDataURL(uploadStatus.file);
        fr.onloadend = () => {
            previewSrc = fr.result;
            previewAlt = uploadStatus.file.name;
        };
    });


    let visibleDiv: HTMLDivElement;
    let statusString: string = "Pending…";
    export async function startUpload() {
        if (uploadStatus.status > 0) {
            return;
        }
        uploadStatus.status = 1;
        statusString = "Uploading…";
        if (!$frontendStateStore.userStoppedUploadScroll) {
            visibleDiv.scrollIntoView({
                behavior: "smooth",
                block: "center"
            });
        }

        const uploadRes = await UploadFile(uploadStatus,
            (progress: number) => progressBar.value = progress,
            (_uploadStatus: boolean) => {
                statusString = "Processing…";
                progressBar.removeAttribute("value");
            }
        );
        progressBar.value = 1.0;

        if (uploadRes.success) {
            statusString = "Done!";
            uploadStatus.status = 2;
            dispatch("fileUploadDone");
        }
        else {
            statusString = "Failed :(";
            uploadStatus.status = 3;
            dispatch("fileUploadFailed");
        }
    }

    $: {
        if (progressBar && uploadStatus.status == 0) {
            progressBar.value = 0
        };
    }
</script>


<div class="fileUploader"
    bind:this={visibleDiv}
    >
    <div class="info">
        <div class="meta">
            <div class="name"><span class="label">Name:</span> {uploadStatus.file.name}</div>
            <div class="size"><span class="label">Size:</span> {HumanBytes(uploadStatus.file.size)}</div>
        </div>
        <div class="preview">
            <img src={previewSrc} alt={previewAlt}>
        </div>
    </div>
    <div class="status">
        <progress bind:this={progressBar}></progress>
        <div class="status">{statusString}</div>
    </div>
</div>

<style>
    .fileUploader {
        padding: 10px 20px;
        background-image: linear-gradient(
            rgb(99, 99, 99),
            rgb(56, 56, 56)
        );
        border-top: 1px solid black;

        display: flex;
        flex-direction: column;
    }

    .info {
        display: flex;
        justify-content: space-between;
    }

    .meta {
        margin-right: 10px;

        overflow: hidden;
        white-space: nowrap;
    }

    .preview {
        width: 75px;
        height: 75px;

        display: flex;
    }

    .preview img {
        max-width: 75px;
        max-height: 75px;
        margin: auto;
    }

    .label {
        font-weight: bolder;
    }

    .status {
        flex-grow: 2;
    }

    progress {
        width: 100%;
    }

    .status {
        text-align: center;
    }
</style>
