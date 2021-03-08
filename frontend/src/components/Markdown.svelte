<script lang="ts" context="module">
    import MarkdownIt from "markdown-it";

    let md = null;

    function initMarkdown() {
        md = MarkdownIt({
            typographer: true,
            xhtmlOut: true,
        });
    }
</script>

<script lang="ts">
    export let markdown: string = "";

    let rendered: string = "";
    function render(s: string) {
        if (md == null) {
            initMarkdown();
        }
        return md.render(s);
    }
    $: rendered = render(markdown)
</script>

{@html rendered}
