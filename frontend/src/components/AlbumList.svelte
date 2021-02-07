<script lang="ts">
    import { onMount, tick } from "svelte";

    import { Link } from "svelte-routing";

    import { RunApi } from "../api";
    import type { Album } from "../pozzo.type";
    import { loginCredentialStore } from "../stores";
    import Overlay from "./Overlay.svelte";

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
            addingNew = false;
            getAlbumList(null);
        }
        else {
            newAlbumMessage = "Duplicate name!";
        }
        attemptingNewAlbum = false;
    }

    async function showNewAlbum() {
        newAlbumNameField = "";
        newAlbumMessage = "";
        addingNew = true;
        await tick();
        newAlbumNameInput.focus();
    }

    async function getAlbumList(_) {
        const res = await RunApi("/album/list", {authorize: true});
        if (res.success) {
            albumList = res.data;
        }
        else {
            console.error(res);
        }
    }
    let albumList: Album[];
    let addingNew = false;
    let newAlbumNameInput: HTMLInputElement = null;
    let newAlbumNameField: string = "";
    let attemptingNewAlbum: boolean = false;
    let newAlbumMessage: string = "";

    onMount(() => {
        getAlbumList(null);
    });

    $: getAlbumList($loginCredentialStore)
</script>

<div class="albumList">
    {#if addingNew}
        <Overlay on:clickedOutside={() => addingNew = false}>
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
    {/if}

    <div class="header">
        <h2>Albums</h2>
        {#if $loginCredentialStore.length > 0}
            <div class="addAlbum" title="Add Album" on:click={showNewAlbum}>
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
        {/if}
    </div>
    {#if albumList}
        {#each albumList as album}
            <div class="navAlbum">
                <Link to={`album/${album.title}`}>
                    {album.title}
                </Link>
            </div>
        {/each}
    {/if}
</div>

<style>
    .albumList {
        padding-left: 30px;
    }
    .navAlbum {
        margin: 10px;
        cursor: pointer;
    }

    .header {
        display: flex;
        align-items: center;
    }
    .addAlbum {
        width: 30px;
        margin-left: 10px;
        cursor: pointer;
        display: flex;
    }

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
