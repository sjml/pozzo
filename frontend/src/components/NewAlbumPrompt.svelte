<script lang="ts">
    import { createEventDispatcher, onMount, tick } from "svelte";

    import { RunApi } from "../api";
    import Overlay from "./Overlay.svelte";

    const dispatch = createEventDispatcher();

    let newAlbumNameInput: HTMLInputElement = null;
    let newAlbumNameField: string = "";

    let attemptingNewAlbum: boolean = false;
    let newAlbumMessage: string = "";
    let isPrivate: boolean = false;

    onMount(async () => {
        newAlbumNameField = "";
        newAlbumMessage = "";
        await tick();
        newAlbumNameInput.focus();
    });

    async function attemptNewAlbum() {
        attemptingNewAlbum = true;
        const res = await RunApi("/album/new", {
            params: {
                title: newAlbumNameField,
                isPrivate: isPrivate
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
</script>


<Overlay on:clickedOutside={() => dispatch("dismissed")}>
    <div class="newAlbumPrompt">
        <form on:submit|preventDefault={attemptNewAlbum}>
            <label>
                Album Name:
                <input type="text"
                    disabled={attemptingNewAlbum}
                    bind:this={newAlbumNameInput}
                    bind:value={newAlbumNameField}
                />
            </label>
            <div class="visibilityToggle">
                <label>
                    <input type="checkbox" bind:checked={isPrivate}>
                    Private
                </label>
            </div>
            <button type="submit" disabled={attemptingNewAlbum || (newAlbumNameField.length <= 0)}>Create Album</button>
        </form>
        <div class="new-album-message">{newAlbumMessage}&nbsp;</div>
    </div>
</Overlay>


<style>
    .newAlbumPrompt {
        margin: 10px;

        background-image: linear-gradient(
            rgb(99, 99, 99),
            rgb(56, 56, 56)
        );
        border: 1px solid rgb(119, 119, 119);
        border-radius: 6px;
        padding: 50px;
        max-width: 400px;;

        display: flex;
        flex-wrap: wrap;
    }

    .newAlbumPrompt label {
        width: 100%;
        margin-top: 5px;
    }

    .newAlbumPrompt button {
        margin-top: 15px;

        font-size: x-large;
    }

    .newAlbumPrompt input[type=text] {
        width: 100%;
        margin-top: 4px;
        margin-bottom: 10px;

        font-size: x-large;
    }

    .newAlbumPrompt .new-album-message {
        width: 100%;

        font-size: xx-large;
        text-align: center;
        color: rgb(179, 0, 0);
    }
</style>
