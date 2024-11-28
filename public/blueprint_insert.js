const iframes = `
    <div class="blueprint_article_preview">
        <div class="blueprint_article_preview__wrapper">
            <div class="blueprint_article_preview__viewport viewport viewport--desktop" data-type="desktop">
                <div class="viewport__wrapper">
                    {{ iframes }}
                </div>
            </div>
            <div class="blueprint_article_preview__viewport viewport viewport--smartphone" data-type="smartphone">
                <div class="viewport__wrapper">
                    {{ iframes }}
                </div>
            </div>
            <div class="blueprint_article_preview__viewport viewport viewport--tablet" data-type="tablet">
                <div class="viewport__wrapper">
                    {{ iframes }}
                </div>
            </div>
        </div>
    </div>
`

const iframe = `
    <iframe class="viewport__content" data-layout="{{ layout }}" src="{{ url }}"></iframe>
`

window.addEventListener('DOMContentLoaded', () => {
    //Insert Previews
    const arrPreviews = [];
    arrBlueprintPreviewSrcSet.forEach(src => {
        let preview = iframe
        preview = preview.replace("{{ url }}", src.url)
        preview = preview.replace("{{ layout }}", src.layout)
        arrPreviews.push(preview)
    })
    const tmp = document.createElement("div")
    tmp.innerHTML = iframes.replaceAll("{{ iframes }}", arrPreviews.join(""))

    document.querySelector('main').parentNode.append(tmp.firstElementChild)


    //Set preview visibility
    document.querySelectorAll("[data-blueprint-alias]").forEach(previewTrigger => {
        const intLayout = previewTrigger.closest("[data-layout]").dataset.layout

        previewTrigger.addEventListener('mouseenter', () => {
            const strBlueprint = previewTrigger.dataset.blueprintAlias

            document.querySelectorAll(`iframe.--active`).forEach(iframe => {
                iframe.classList.remove("--active")
            })

            document.querySelectorAll(`iframe[data-layout="${intLayout}"]`).forEach(iframe => {
                iframe.classList.add("--active")
            })
            window.dispatchEvent(new CustomEvent("blueprint_preview", {detail: strBlueprint}))
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