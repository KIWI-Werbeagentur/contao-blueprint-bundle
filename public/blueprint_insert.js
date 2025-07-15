const iframes = `
    <div class="blueprint_article_preview" data-previews>
        <div class="blueprint_article_preview__wrapper">
            <div class="blueprint_article_preview__viewport viewport viewport--desktop" data-type="desktop">
                <div class="viewport__wrapper">
                    <iframe></iframe>
                </div>
            </div>
            <div class="blueprint_article_preview__viewport viewport viewport--smartphone" data-type="smartphone">
                <div class="viewport__wrapper">
                    <iframe></iframe>
                </div>
            </div>
            <div class="blueprint_article_preview__viewport viewport viewport--tablet" data-type="tablet">
                <div class="viewport__wrapper">
                    <iframe></iframe>
                </div>
            </div>
        </div>
    </div>
`

const iframe = `<iframe class="viewport__content" data-layout="{{ layout }}" data-page="{{ page }}" src="{{ url }}"></iframe>`

const toggleBueprint = (previewTrigger, intPage)=>{
    const strBlueprint = previewTrigger.dataset.blueprintAlias

    document.querySelectorAll(`iframe.--active`).forEach(iframe => {
        iframe.classList.remove("--active")
    })

    document.querySelectorAll(`iframe[data-page="${intPage}"]`).forEach(iframe => {
        iframe.classList.add("--active")
    })
    window.dispatchEvent(new CustomEvent("blueprint_preview", {detail: strBlueprint}))
}

window.addEventListener('DOMContentLoaded', () => {
    //Insert Previews
    const tmp = document.createElement("div")
    tmp.innerHTML = iframes
    document.querySelector('main').parentNode.append(tmp.firstElementChild)
    let page = 0

    document.querySelectorAll('[data-blueprint-alias]').forEach(previewTrigger => {
        previewTrigger.addEventListener('mouseenter', () => {
            intPage = previewTrigger.closest('[data-page]').dataset.page
            let preview = document.querySelector(`iframe[data-page='${intPage}']`)

            if (!preview) {
                preview = iframe
                preview = preview.replace("{{ url }}", `${strBlueprintPreview}&page=${intPage}`)
                preview = preview.replace("{{ page }}", intPage)

                document.querySelectorAll('[data-previews] iframe').forEach(iframe => {
                    const container = iframe.parentNode
                    container.innerHTML = preview

                    container.querySelector('iframe').addEventListener('load', ()=>{
                        window.dispatchEvent(new Event("blueprint_insert", {detail: intPage}))
                        toggleBueprint(previewTrigger,intPage)
                    }, true)
                })
            }
        })
    })

    //Set preview visibility
    document.querySelectorAll("[data-blueprint-alias]").forEach(previewTrigger => {
        const intPage = previewTrigger.closest("[data-page]").dataset.page

        previewTrigger.addEventListener('mouseenter', () => {
            toggleBueprint(previewTrigger,intPage)
        })
    })
});

window.addEventListener("load", () => {
    window.dispatchEvent(new Event("blueprint_insert"))
})

window.addEventListener("blueprint_preview_resize", (e) => {
    document.querySelectorAll(".blueprint_article_preview__item").forEach(preview => {
        preview.style.setProperty('height', `${e.detail.height}px`)
    })
})