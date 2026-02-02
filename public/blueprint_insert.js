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

function initBlueprintPreviews() {
    // Check if previews already exist (avoid duplicates)
    if (document.querySelector('[data-previews]')) {
        return;
    }

    // Check if we have blueprint elements on the page
    if (!document.querySelector('[data-blueprint-alias]')) {
        return;
    }

    //Insert Previews
    const tmp = document.createElement("div")
    tmp.innerHTML = iframes
    const main = document.querySelector('main')
    if (main && main.parentNode) {
        main.parentNode.append(tmp.firstElementChild)
    }
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

                    const iframeElement = container.querySelector('iframe')
                    iframeElement.addEventListener('load', ()=>{
                        // Delay to ensure iframe scripts execute before dispatching events
                        setTimeout(() => {
                            window.dispatchEvent(new Event("blueprint_insert", {detail: intPage}))
                            setTimeout(() => {
                                toggleBueprint(previewTrigger,intPage)
                            }, 10)
                        }, 100)
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
}

// Initialize on page load
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initBlueprintPreviews);
} else {
    initBlueprintPreviews();
}

// Reinitialize on Turbo navigation
document.addEventListener('turbo:load', initBlueprintPreviews);
document.addEventListener('turbo:render', initBlueprintPreviews);

window.addEventListener("load", () => {
    window.dispatchEvent(new Event("blueprint_insert"))
})

window.addEventListener("blueprint_preview_resize", (e) => {
    document.querySelectorAll(".blueprint_article_preview__item").forEach(preview => {
        preview.style.setProperty('height', `${e.detail.height}px`)
    })
})