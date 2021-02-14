<script lang="ts">
    import { onMount } from "svelte";

    import marked from "marked";
    import DOMPurify from "dompurify";

    export let markdown: string = "";

    let md = null;
    let rendered: string = "";

    onMount(() => {
        marked.setOptions({
            smartypants: true,
        });
    });

    function render(s: string) {
        return DOMPurify.sanitize(marked(s));
    }

    $: rendered = render(markdown)
</script>

{@html rendered}
