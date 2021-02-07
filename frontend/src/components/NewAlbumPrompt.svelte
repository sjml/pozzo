<script lang="ts">
    import { createEventDispatcher, onMount, tick } from "svelte";

    import Overlay from "./Overlay.svelte";
    import { RunApi } from "../api";

    const dispatch = createEventDispatcher();

    async function attemptNewAlbum() {
        attemptingNewAlbum = true;
        const res = await RunApi("/album/new", {
            params: {
                title: newAlbumNameField
            },
            method: "POST",
            authorize: true
        });
        if (res.success) {
            dispatch("done", {newAlbumID: res.data.albumID});
        }
        else {
            newAlbumMessage = "Duplicate name!";
        }
        attemptingNewAlbum = false;
    }

    onMount(async () => {
        newAlbumNameField = "";
        newAlbumMessage = "";
        await tick();
        newAlbumNameInput.focus();
    });

    let newAlbumNameInput: HTMLInputElement = null;
    let newAlbumNameField: string = "";

    let attemptingNewAlbum: boolean = false;
    let newAlbumMessage: string = "";
</script>

<Overlay on:clickedOutside={() => dispatch("dismissed")}>
    <div class="new-album-prompt">
        <form on:submit|preventDefault={attemptNewAlbum}>
            <label>
                Album Name:
                <input type="text"
                    disabled={attemptingNewAlbum}
                    bind:this={newAlbumNameInput}
                    bind:value={newAlbumNameField}
                />
            </label>
            <button type="submit" disabled={attemptingNewAlbum || (newAlbumNameField.length <= 0)}>Create Album</button>
        </form>
        <div class="new-album-message">{newAlbumMessage}&nbsp;</div>
    </div>
</Overlay>


<style>
        .new-album-prompt {
        background-image: linear-gradient(
            rgb(99, 99, 99),
            rgb(56, 56, 56)
        );
        border: 1px solid rgb(119, 119, 119);
        border-radius: 6px;

        margin: 10px;
        padding: 50px;
        max-width: 400px;;

        display: flex;
        flex-wrap: wrap;
    }

    .new-album-prompt label {
        width: 100%;
        margin-top: 5px;
    }

    .new-album-prompt button {
        margin-top: 4px;
        font-size: x-large;
    }

    .new-album-prompt input {
        width: 100%;
        margin-top: 4px;
        margin-bottom: 10px;
        font-size: x-large;
    }

    .new-album-prompt .new-album-message {
        width: 100%;
        font-size: xx-large;
        text-align: center;
        color: rgb(179, 0, 0);
    }
</style>
