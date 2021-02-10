<script lang="ts">
    import { tick, onMount } from "svelte";

    import Overlay from "./Overlay.svelte";

    import { RunApi } from '../api';
    import { loginCredentialStore, isLoggedInStore } from "../stores";

    let updateTimeout: number = null;
    onMount(() => {
        if ($loginCredentialStore.length == 0) {
            return;
        }
        checkLogin();
    });

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
        <div class="login-prompt">
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
            <div class="login-message">{loginMessage}&nbsp;</div>
        </div>
    </Overlay>
{/if}
{#if !$isLoggedInStore}
    <div class="link" on:click={showLogin} title="Log In">
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
                d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z">
            </path>
        </svg>
    </div>
{:else}
    <div class="link" on:click={logout} title="Log Out">
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
                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1">
            </path>
        </svg>
    </div>
{/if}


<style>
    .link {
        cursor: pointer;
    }
    .link svg {
        width: 30px;
    }
    .link:hover {
        text-decoration: underline;
    }

    .login-prompt {
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

    .login-prompt label {
        width: 100%;
        margin-top: 5px;
    }

    .login-prompt button {
        margin-top: 4px;
        font-size: x-large;
    }

    .login-prompt input {
        width: 100%;
        margin-top: 4px;
        margin-bottom: 10px;
        font-size: x-large;
    }

    .login-prompt .login-message {
        width: 100%;
        font-size: xx-large;
        text-align: center;
        color: rgb(179, 0, 0);
    }
</style>
