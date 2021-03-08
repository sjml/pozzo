<script lang="ts">
</script>

<svelte:head>
    <meta name="robots" content="noindex">
</svelte:head>

<div class="notFound">
    <!--
        can't send a real 404 response because
        by the time the browser realizes this
        it's already received the app as a 200.
        but the inference is gathered.
    -->
    <div class="error">
        404
    </div>
    <div class="explain">
        There’s nothing here…
    </div>
</div>

<style>
    .notFound {
        text-align: center;
        opacity: 0.5;

        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
    }

    .error {
        font-size: 300px;
    }

    .explain {
        font-size: 50px;
    }
</style>
