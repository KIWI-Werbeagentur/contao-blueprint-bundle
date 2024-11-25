const iframes= `
    <div class="blueprint_article_preview">
        <div class="blueprint_article_preview__wrapper">
            <div class="blueprint_article_preview__viewport blueprint_article_preview__viewport--smartphone" data-type="smartphone">
                {{ iframes }}
            </div>
            <div class="blueprint_article_preview__viewport blueprint_article_preview__viewport--tablet" data-type="tablet">
                {{ iframes }}
            </div>
            <div class="blueprint_article_preview__viewport blueprint_article_preview__viewport--desktop" data-type="desktop">
                {{ iframes }}
            </div>
        </div>
    </div>
`

const iframe = `
    <iframe class="blueprint_article_preview__item" data-layout="{{ layout }}" src="{{ url }}"></iframe>
`

window.addEventListener('DOMContentLoaded',()=>{
    //Insert Previews
    const arrPreviews = [];
    arrBlueprintPreviewSrcSet.forEach(src=>{
        let preview = iframe
        preview = preview.replace("{{ url }}", src.url)
        preview = preview.replace("{{ layout }}", src.layout)
        arrPreviews.push(preview)
    })
    const tmp = document.createElement("div")
    tmp.innerHTML = iframes.replaceAll("{{ iframes }}", arrPreviews.join(""))

    document.querySelector('main').parentNode.append(tmp.firstElementChild)


    //Set preview visibility
    document.querySelectorAll("[data-blueprint-alias]").forEach(previewTrigger=>{
        const intLayout = previewTrigger.closest("[data-layout]").dataset.layout

        previewTrigger.addEventListener('mouseenter',()=>{
            const strBlueprint = previewTrigger.dataset.blueprintAlias

            document.querySelector(`iframe[data-layout="${intLayout}"]`).classList.add("--active")
            window.dispatchEvent(new CustomEvent("blueprint_preview", { detail: strBlueprint }))
        })

        previewTrigger.addEventListener('mouseleave',()=>{
            document.querySelector(`iframe[data-layout="${intLayout}"]`).classList.remove("--active")
        })
    })
});

window.addEventListener("load",()=>{
    window.dispatchEvent(new Event("blueprint_insert"))
})

window.addEventListener("blueprint_preview_resize",(e)=>{
    document.querySelectorAll(".blueprint_article_preview__item").forEach(preview=>{
        preview.style.setProperty('height', `${e.detail.height}px`)
    })
})