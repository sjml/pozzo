<script lang="ts">
    import { onMount } from "svelte";
    import { navigate } from "svelte-routing";

    import { siteData, loginCredentialStore } from "../stores";
    import { RunApi } from "../api";
    import Overlay from "./Overlay.svelte";


    let sitenameInput: HTMLInputElement;

    onMount(() => {
        sitenameInput.focus();
    })
    $: if ($siteData.siteTitle !== false) navigate("/", {replace: true})

    let attemptingConfig: boolean = false;
    let sitenameField: string = "";
    let usernameField: string = "";
    let passwordField: string = "";
    let password2Field: string = "";
    let configMessage: string = "";
    async function attemptConfig() {
        attemptingConfig = true;
        const res = await RunApi("/setup", {
            params: {
                siteTitle: sitenameField,
                userName: usernameField,
                password: passwordField,
            },
            method: "POST",
        });
        if (res.success) {
            $siteData.siteTitle = sitenameField;
            $loginCredentialStore = res.data.key;
            navigate("/", {replace: true});
        }
        else {
            configMessage = "Something went wrong. :(";
        }
        attemptingConfig = false;
    }


    let submitEnabled: boolean = false;
    $: {
        if (sitenameField.length > 0
            && usernameField.length > 0
            && passwordField.length > 0
            && passwordField == password2Field)
        {
            submitEnabled = true;
        }
        else {
            submitEnabled = false;
        }
    }
</script>

<Overlay>
    <div class="configPrompt">
        <form on:submit|preventDefault={attemptConfig}>
            <label>
                Site Name:
                <input type="text"
                    disabled={attemptingConfig}
                    bind:this={sitenameInput}
                    bind:value={sitenameField}
                />
            </label>
            <label>
                User:
                <input type="text"
                    disabled={attemptingConfig}
                    bind:value={usernameField}
                />
            </label>
            <label>
                Password:
                <input type="password"
                    disabled={attemptingConfig}
                    bind:value={passwordField}
                />
            </label>
            <label>
                Verify Password:
                <input type="password"
                    disabled={attemptingConfig}
                    bind:value={password2Field}
                />
            </label>
            <button type="submit" disabled={attemptingConfig || !submitEnabled}>Configure Site</button>
        </form>
        <div class="configMessage">{configMessage}&nbsp;</div>
    </div>
</Overlay>

<style>
    .configPrompt {
        margin: 10px;
        max-width: 400px;

        padding: 50px;
        background-image: linear-gradient(
            rgb(99, 99, 99),
            rgb(56, 56, 56)
        );
        border: 1px solid rgb(119, 119, 119);
        border-radius: 6px;

        display: flex;
        flex-wrap: wrap;
    }

    .configPrompt label {
        width: 100%;

        margin-top: 5px;
    }

    .configPrompt button {
        margin-top: 4px;

        font-size: x-large;
    }

    .configPrompt input {
        width: 100%;
        margin-top: 4px;
        margin-bottom: 10px;

        font-size: x-large;
    }

    .configPrompt .configMessage {
        width: 100%;

        font-size: xx-large;
        text-align: center;
        color: rgb(179, 0, 0);
    }
</style>
