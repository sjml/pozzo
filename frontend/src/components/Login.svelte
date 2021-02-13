<script lang="ts">
    import { tick, onMount } from "svelte";

    import { RunApi } from '../api';
    import { loginCredentialStore, isLoggedInStore } from "../stores";
    import Overlay from "./Overlay.svelte";
    import Button from "./Button.svelte";


    onMount(() => {
        if ($loginCredentialStore.length == 0) {
            return;
        }
        checkLogin();
    });


    let updateTimeout: number = null;
    async function checkLogin() {
        const res = await RunApi("/login/check", {
            authorize: true,
        });
        if (res.success) {
            updateTimeout = setTimeout(() => {
                updateTimeout = null;
                $loginCredentialStore = res.data.newToken;
            }, (res.data.validIn + 0.5) * 1000);
        }
        else {
            if (res.data.code == -4) {
                // just not valid yet; chill for a bit
            }
            else {
                // probably an expired token
                logout();
            }
        }
    }

    function logout() {
        if (updateTimeout != null) {
            clearTimeout(updateTimeout);
            updateTimeout = null;
        }
        $loginCredentialStore = "";
    }


    let loginDisplayed: boolean = false;
    let usernameInput: HTMLInputElement;
    async function showLogin() {
        usernameField = "";
        passwordField = "";
        loginMessage = "";
        loginDisplayed = true;
        await tick();
        usernameInput.focus();
    }


    let attemptingLogin: boolean = false;
    let usernameField: string = "";
    let passwordField: string = "";
    let loginMessage: string = "";
    async function attemptLogin() {
        attemptingLogin = true;
        const res = await RunApi("/login/", {
            params: {
                userName: usernameField,
                password: passwordField,
            },
            method: "POST",
        });
        if (res.success) {
            $loginCredentialStore = res.data.token;
            loginDisplayed = false;
        }
        else {
            loginMessage = "Incorrect!";
        }
        attemptingLogin = false;
    }


    let submitEnabled: boolean = false;
    $: {
        if (usernameField.length > 0 && passwordField.length > 0) {
            submitEnabled = true;
        }
        else {
            submitEnabled = false;
        }
    }
</script>

{#if loginDisplayed}
    <Overlay on:clickedOutside={() => loginDisplayed = false}>
        <div class="loginPrompt">
            <form on:submit|preventDefault={attemptLogin}>
                <label>
                    User:
                    <input type="text"
                        disabled={attemptingLogin}
                        bind:this={usernameInput}
                        bind:value={usernameField}
                    />
                </label>
                <label>
                    Password:
                    <input type="password"
                        disabled={attemptingLogin}
                        bind:value={passwordField}
                    />
                </label>
                <button type="submit" disabled={attemptingLogin || !submitEnabled}>Log In</button>
            </form>
            <div class="loginMessage">{loginMessage}&nbsp;</div>
        </div>
    </Overlay>
{/if}
{#if !$isLoggedInStore}
    <Button on:click={showLogin} title="Log In">
        <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 256 256"><rect width="256" height="256" fill="none"></rect><circle cx="128" cy="128" r="96" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></circle><circle cx="128" cy="120" r="40" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></circle><path d="M63.79905,199.37405a72.02812,72.02812,0,0,1,128.40177-.00026" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></path></svg>
    </Button>
{:else}
    <Button on:click={logout} title="Log Out">
        <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 256 256"><rect width="256" height="256" fill="none"></rect><polyline points="174.029 86 216.029 128 174.029 170" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></polyline><line x1="104" y1="128" x2="216" y2="128" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></line><path d="M120,216H48a8,8,0,0,1-8-8V48a8,8,0,0,1,8-8h72" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></path></svg>
    </Button>
{/if}


<style>
    .loginPrompt {
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

    .loginPrompt label {
        width: 100%;

        margin-top: 5px;
    }

    .loginPrompt button {
        margin-top: 4px;

        font-size: x-large;
    }

    .loginPrompt input {
        width: 100%;
        margin-top: 4px;
        margin-bottom: 10px;

        font-size: x-large;
    }

    .loginPrompt .loginMessage {
        width: 100%;

        font-size: xx-large;
        text-align: center;
        color: rgb(179, 0, 0);
    }
</style>
