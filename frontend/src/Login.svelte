<script lang="ts">
    import { tick, onMount } from 'svelte';

    import { siteData, loginCredentialStore } from "./stores";

    let updateTimeout: number = null;
    onMount(() => {
        if ($loginCredentialStore.length == 0) {
            return;
        }
        checkLogin();
    });
    async function checkLogin() {
        try {
            const res = await fetch(
                `${$siteData.apiUri}/login/check`,
                {
                    headers: {
                        'Authorization': `Bearer ${$loginCredentialStore}`,
                    }
                }
            );
            if (res.ok) {
                const checkResult = await res.json();
                updateTimeout = setTimeout(() => {
                    updateTimeout = null;
                    $loginCredentialStore = checkResult.newToken;
                }, (checkResult.validIn + 0.5) * 1000);
            }
            else {
                const err = await res.json();
                console.error(err);
                // throw new Error(err);
            }
        } catch (error) {
            // probably just 403-ed from expired token
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
    async function showLogin()
    {
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
        const res = await fetch(
            `${$siteData.apiUri}/login/`,
            {
                body: JSON.stringify({
                    userName: usernameField,
                    password: passwordField,
                }),
                method: "POST",
            }
        );
        if (res.ok) {
            const loginStatus = await res.json();
            $loginCredentialStore = loginStatus.token;
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

<div class="login">
    {#if $loginCredentialStore.length == 0}
        {#if loginDisplayed}
            <div class="overlay"
                on:click|self={() => loginDisplayed = false}
            >
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
            </div>
        {/if}
        <div class="link" on:click={showLogin}>
            Login
        </div>
    {:else}
        <div class="link" on:click={logout}>
            Logout
        </div>
    {/if}
</div>


<style>
    .login {
        margin: 10px;
        position: fixed;
        top: 0;
        right: 0;
        z-index: 500;
    }

    .link {
        cursor: pointer;
    }
    .link:hover {
        text-decoration: underline;
    }

    .overlay {
        background-color: rgba(0, 0, 0, 0.5);
        z-index: 1000;

        position: fixed;
        top: 0;
        right: 0;
        bottom: 0;
        left: 0;

        display: flex;
        justify-content: center;
        align-items: center;
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
