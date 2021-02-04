<script lang="ts">
    import { fade } from "svelte/transition";

    import { siteData, loginCredentialStore } from "./stores";

    function dragEnter(event: DragEvent) {
        dragging = true;
    }

    function dragLeave(event: DragEvent) {
        dragging = false;
    }

    async function handleDrop(event: DragEvent) {
        dragging = false;
        console.log(event.dataTransfer.files.length);
        for (const file of event.dataTransfer.files) {
            ([...event.dataTransfer.files]).forEach((file) =>{
                console.log(file);
            });
        }
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

<style>
    .uploadZone {
        position: fixed;
        top: 0;
        right: 0;
        bottom: 0;
        left: 0;
        z-index: 200;
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
