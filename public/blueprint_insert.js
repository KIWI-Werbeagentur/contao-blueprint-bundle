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

let ac = null;
let hoverGeneration = 0;

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
    // Abort any previous listeners (prevents duplicates after Turbo navigation)
    if (ac) ac.abort();
    ac = new AbortController();
    const signal = ac.signal;

    if (!document.querySelector('[data-blueprint-alias]')) {
        return;
    }

    const existing = document.querySelector('[data-previews]');
    if (existing) {
        existing.remove();
    }

    const tmp = document.createElement("div")
    tmp.innerHTML = iframes
    const main = document.querySelector('main')
    if (main && main.parentNode) {
        main.parentNode.append(tmp.firstElementChild)
    }

    document.querySelectorAll('[data-blueprint-alias]').forEach(previewTrigger => {
        previewTrigger.addEventListener('mouseenter', () => {
            const generation = ++hoverGeneration;
            const intPage = previewTrigger.closest('[data-page]').dataset.page
            const strAlias = previewTrigger.dataset.blueprintAlias
            const strSrc = `${strBlueprintPreview}&page=${intPage}&alias=${strAlias}`

            const previews = document.querySelectorAll(`iframe[data-page='${intPage}']`)

            if (!previews.length) {
                const tmp = document.createElement("div")
                tmp.innerHTML = iframe
                    .replace("{{ url }}", strSrc)
                    .replace("{{ page }}", intPage)
                const template = tmp.firstElementChild

                const containers = document.querySelectorAll('[data-previews] .viewport__wrapper')
                containers.forEach(container => {
                    container.innerHTML = ''
                    const clone = template.cloneNode(true)
                    container.appendChild(clone)
                    clone.addEventListener('load', () => {
                        if (signal.aborted || generation !== hoverGeneration) return
                        setTimeout(() => {
                            if (signal.aborted || generation !== hoverGeneration) return
                            window.dispatchEvent(new Event("blueprint_insert", {detail: intPage}))
                            setTimeout(() => {
                                if (!signal.aborted && generation === hoverGeneration) {
                                    toggleBueprint(previewTrigger, intPage)
                                }
                            }, 10)
                        }, 100)
                    }, {capture: true, signal})
                })
            } else {
                let loaded = 0
                previews.forEach(preview => {
                    preview.src = strSrc
                    preview.addEventListener('load', () => {
                        if (signal.aborted || generation !== hoverGeneration) return
                        loaded++
                        if (loaded < previews.length) return
                        setTimeout(() => {
                            if (signal.aborted || generation !== hoverGeneration) return
                            window.dispatchEvent(new Event("blueprint_insert", {detail: intPage}))
                            setTimeout(() => {
                                if (!signal.aborted && generation === hoverGeneration) {
                                    toggleBueprint(previewTrigger, intPage)
                                }
                            }, 10)
                        }, 100)
                    }, {once: true, signal})
                })
            }
        }, {signal})
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