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
    // let fileList: File[] = [
    //     // {name: "longer_than_it_should_be_what_about_now_filename_with_things_if_it_keeps_going_to_the_ends_of_the_earth.jpg", size: 2343452, lastModified: 1612536624, type: "image/jpeg", arrayBuffer:null, slice: null, stream: null, text: null},
    //     {name: "file2.jpg", size: 7987235,  lastModified: 1612536620, type: "image/jpeg", arrayBuffer:null, slice: null, stream: null, text: null},
    //     {name: "file3.jpg", size: 312, lastModified: 1612536624, type: "image/jpeg", arrayBuffer:null, slice: null, stream: null, text: null},
    //     {name: "file4.jpg", size: 1234567, lastModified: 1612536624, type: "image/jpeg", arrayBuffer:null, slice: null, stream: null, text: null},
    //     // {name: "longer_than_it_should_be_what_about_now_filename_with_things_if_it_keeps_going_to_the_ends_of_the_earth.jpg", size: 2343452, lastModified: 1612536624, type: "image/jpeg", arrayBuffer:null, slice: null, stream: null, text: null},
    //     {name: "file2.jpg", size: 7987235,  lastModified: 1612536620, type: "image/jpeg", arrayBuffer:null, slice: null, stream: null, text: null},
    //     {name: "file3.jpg", size: 312, lastModified: 1612536624, type: "image/jpeg", arrayBuffer:null, slice: null, stream: null, text: null},
    //     {name: "file4.jpg", size: 1234567, lastModified: 1612536624, type: "image/jpeg", arrayBuffer:null, slice: null, stream: null, text: null},
    //     // {name: "longer_than_it_should_be_what_about_now_filename_with_things_if_it_keeps_going_to_the_ends_of_the_earth.jpg", size: 2343452, lastModified: 1612536624, type: "image/jpeg", arrayBuffer:null, slice: null, stream: null, text: null},
    //     {name: "file2.jpg", size: 7987235,  lastModified: 1612536620, type: "image/jpeg", arrayBuffer:null, slice: null, stream: null, text: null},
    //     {name: "file3.jpg", size: 312, lastModified: 1612536624, type: "image/jpeg", arrayBuffer:null, slice: null, stream: null, text: null},
    //     {name: "file4.jpg", size: 1234567, lastModified: 1612536624, type: "image/jpeg", arrayBuffer:null, slice: null, stream: null, text: null},
    //     // {name: "longer_than_it_should_be_what_about_now_filename_with_things_if_it_keeps_going_to_the_ends_of_the_earth.jpg", size: 2343452, lastModified: 1612536624, type: "image/jpeg", arrayBuffer:null, slice: null, stream: null, text: null},
    //     {name: "file2.jpg", size: 7987235,  lastModified: 1612536620, type: "image/jpeg", arrayBuffer:null, slice: null, stream: null, text: null},
    //     {name: "file3.jpg", size: 312, lastModified: 1612536624, type: "image/jpeg", arrayBuffer:null, slice: null, stream: null, text: null},
    //     {name: "file4.jpg", size: 1234567, lastModified: 1612536624, type: "image/jpeg", arrayBuffer:null, slice: null, stream: null, text: null},
    // ];

    async function handleDrop(event: DragEvent) {
        dragging = false;
        fileList = [...event.dataTransfer.files];

        // let f = event.dataTransfer.files[0];
        // let fd = new FormData();
        // fd.append('photoUp', f);

        // const res = await fetch(`${$siteData.apiUri}/upload`, {
        //     method: "POST",
        //     body: fd,
        //     headers: {
        //         'Authorization': `Bearer ${$loginCredentialStore}`,
        //     }
        // });
        // if (res.ok) {
        //     const feedback = await res.json();
        //     console.log(feedback);
        // }
        // else {
        //     const err = await res.json();
        //     console.error(err);
        // }
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
