<script lang="ts">
    import { siteData, loginCredentialStore } from "./stores";
</script>

{#if $loginCredentialStore.length > 0}
<div class="uploadZone">

</div>
{/if}

<style>
    .uploadZone {
        position: fixed;
        top: 0;
        right: 0;
        bottom: 0;
        left: 0;
    }
</style>
